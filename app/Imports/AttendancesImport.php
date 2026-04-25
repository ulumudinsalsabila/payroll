<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\Attendance;
use App\Models\Employee;

class AttendancesImport implements ToCollection
{
    /**
    * @param Collection $rows
    */
    public function collection(Collection $rows)
    {
        $period_month = null;
        \Log::info('AttendancesImport: Starting import. Total rows: ' . $rows->count());

        foreach ($rows as $index => $row) {
            // Merge first 5 columns to be safe for finding the header
            $headerScan = '';
            for ($i = 0; $i < 5; $i++) {
                $headerScan .= (string)($row[$i] ?? '');
            }

            if (strpos($headerScan, 'Stat. Tgl') !== false) {
                // Try to find YYYY-MM or YYYY/MM
                if (preg_match('/(\d{4})[\-\/](\d{2})/', $headerScan, $matches)) {
                    $period_month = $matches[1] . '-' . $matches[2];
                    \Log::info("AttendancesImport: Period detected at row $index: $period_month");
                } else {
                    \Log::warning("AttendancesImport: Found 'Stat. Tgl' at row $index but could not parse date in: $headerScan");
                }
                continue;
            }

            $col0 = trim((string)($row[0] ?? ''));

            if (is_numeric($col0)) {
                if (!$period_month) {
                    \Log::warning("AttendancesImport: Found numeric row $index ($col0) but period_month is not yet set. Skipping.");
                    continue;
                }

                $fingerprint_id = $col0;
                $employee_name = trim((string)($row[1] ?? ''));
                
                $late_minutes = (int)($row[6] ?? 0);
                $overtime_hours = trim((string)($row[9] ?? '0:00'));
                
                $hariAbsenRaw = trim((string)($row[11] ?? '0/0'));
                $present_days = 0;
                if (strpos($hariAbsenRaw, '/') !== false) {
                    $parts = explode('/', $hariAbsenRaw);
                    $present_days = (int)end($parts);
                } else {
                    $present_days = (int)$hariAbsenRaw;
                }

                $absent_days = (int)($row[13] ?? 0);

                // Find employee
                $employee = Employee::where('fingerprint_id', $fingerprint_id)
                                    ->orWhere('name', 'like', "%{$employee_name}%")
                                    ->first();

                Attendance::updateOrCreate([
                    'fingerprint_id' => $fingerprint_id,
                    'period_month' => $period_month,
                ], [
                    'employee_id' => $employee ? $employee->id : null,
                    'employee_name' => $employee ? $employee->name : $employee_name,
                    'present_days' => $present_days,
                    'absent_days' => $absent_days,
                    'late_minutes' => $late_minutes,
                    'overtime_hours' => $overtime_hours,
                    'notes' => 'Imported from Statistik Report'
                ]);
                
                \Log::info("AttendancesImport: Successfully processed employee $employee_name (ID: $fingerprint_id)");
            }
        }
        
        \Log::info('AttendancesImport: Import finished.');
    }
}
