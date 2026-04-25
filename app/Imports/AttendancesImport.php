<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;

class AttendancesImport implements ToCollection, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        \Log::info('Starting import, rows count: ' . $rows->count());
        
        foreach ($rows as $index => $row) 
        {
            \Log::info('Processing row ' . $index . ': ', $row->toArray());
            
            // Skip empty rows - check with multiple possible column names
            $fingerprintId = $row['fingerprint_id'] ?? $row['fingerprintID'] ?? $row['finger_id'] ?? $row['id'] ?? null;
            $date = $row['date'] ?? $row['tanggal'] ?? $row['Date'] ?? null;
            
            if (empty($fingerprintId) || empty($date)) {
                \Log::info('Skipping row ' . $index . ' - missing fingerprint_id or date');
                continue;
            }

            // Find employee by fingerprint_id
            $employee = Employee::where('fingerprint_id', $fingerprintId)->first();
            \Log::info('Employee found: ' . ($employee ? $employee->name : 'null'));
            
            // Parse date from Excel format
            $parsedDate = $this->parseDate($date);
            \Log::info('Parsed date: ' . $parsedDate);
            
            // Parse time from Excel format - support various column names
            $checkInRaw = $row['check_in'] ?? $row['checkin'] ?? $row['jam_masuk'] ?? $row['in'] ?? null;
            $checkOutRaw = $row['check_out'] ?? $row['checkout'] ?? $row['jam_keluar'] ?? $row['out'] ?? null;
            $checkIn = $this->parseTime($checkInRaw);
            $checkOut = $this->parseTime($checkOutRaw);
            \Log::info('Check in: ' . $checkIn . ', Check out: ' . $checkOut);
            
            // Determine status
            $status = 'hadir'; // default
            if (!$checkIn && !$checkOut) {
                $status = 'alpha';
            } elseif (isset($row['status']) && $row['status']) {
                $status = strtolower($row['status']);
            }

            // Create or update attendance record
            $attendance = Attendance::updateOrCreate([
                'fingerprint_id' => $fingerprintId,
                'date' => $parsedDate,
            ], [
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'status' => $status,
                'notes' => $row['notes'] ?? $row['keterangan'] ?? null,
                'employee_id' => $employee ? $employee->id : null,
                'employee_name' => $employee ? $employee->name : ($row['employee_name'] ?? $row['nama'] ?? null),
            ]);
            
            \Log::info('Attendance saved: ID ' . $attendance->id);
        }
    }

    private function parseDate($date)
    {
        if (is_numeric($date)) {
            // Excel serial date format
            return Carbon::createFromDate(1900, 1, 1)->addDays($date - 2)->format('Y-m-d');
        }
        
        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseTime($time)
    {
        if (empty($time)) {
            return null;
        }
        
        if (is_numeric($time)) {
            // Excel time format (decimal)
            $seconds = $time * 86400;
            return Carbon::createFromTime(0, 0, 0)->addSeconds($seconds)->format('H:i');
        }
        
        try {
            return Carbon::parse($time)->format('H:i');
        } catch (\Exception $e) {
            return null;
        }
    }
}
