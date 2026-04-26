<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use App\Imports\AttendancesImport;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employees = Employee::where('is_active', true)->get();
        return view('admin.attendances.index', compact('employees'));
    }

    /**
     * Import attendance from Excel file.
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv|max:10240'
        ]);

        try {
            Excel::import(new AttendancesImport, $request->file('excel_file'));
            
            return redirect()->route('attendances.index')
                ->with('success', 'Data absensi berhasil diimport!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'period_month' => 'required|string|size:7', // YYYY-MM
            'present_days' => 'required|integer|min:0',
            'absent_days' => 'required|integer|min:0',
            'late_minutes' => 'required|integer|min:0',
            'overtime_hours' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        $employee = Employee::find($request->employee_id);
        $validated['employee_name'] = $employee->name;
        $validated['fingerprint_id'] = $employee->fingerprint_id;
        
        Attendance::create($validated);

        return redirect()->route('attendances.index')->with('success', 'Data rekap absensi berhasil ditambahkan.');
    }

    /**
     * Get data for DataTables.
     */
    public function data(Request $request)
    {
        $query = Attendance::with('employee');

        if ($request->filled('period_month')) {
            $query->where('period_month', $request->period_month);
        }

        $query->latest();

        return DataTables::eloquent($query)
            ->editColumn('period_month', function ($item) {
                return Carbon::parse($item->period_month)->translatedFormat('F Y');
            })
            ->addColumn('action', function ($item) {
                return '
                    <button type="button" class="btn btn-sm btn-icon btn-light-primary btnEdit" 
                        data-id="'.$item->id.'" 
                        data-employee_id="'.$item->employee_id.'"
                        data-period_month="'.$item->period_month.'"
                        data-present_days="'.$item->present_days.'"
                        data-absent_days="'.$item->absent_days.'"
                        data-late_minutes="'.$item->late_minutes.'"
                        data-overtime_hours="'.$item->overtime_hours.'"
                        data-notes="'.$item->notes.'">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-icon btn-light-danger btnDelete" 
                        data-id="'.$item->id.'" 
                        data-name="'.$item->employee_name . ' (' . $item->period_month . ')' .'">
                        <i class="bi bi-trash"></i>
                    </button>
                ';
            })
            ->make(true);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'period_month' => 'required|string|size:7',
            'present_days' => 'required|integer|min:0',
            'absent_days' => 'required|integer|min:0',
            'late_minutes' => 'required|integer|min:0',
            'overtime_hours' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        $attendance = Attendance::findOrFail($id);
        $employee = Employee::find($request->employee_id);
        $validated['employee_name'] = $employee->name;
        $validated['fingerprint_id'] = $employee->fingerprint_id;

        $attendance->update($validated);

        return redirect()->route('attendances.index')->with('success', 'Data rekap absensi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->delete();

        return redirect()->route('attendances.index')->with('success', 'Data rekap absensi berhasil dihapus.');
    }

    /**
     * Remove all attendances by period_month.
     */
    public function destroyPeriod(Request $request)
    {
        $request->validate([
            'period_month' => 'required|string|size:7'
        ]);

        Attendance::where('period_month', $request->period_month)->delete();

        return redirect()->route('attendances.index')->with('success', "Seluruh data absensi untuk periode {$request->period_month} berhasil dihapus.");
    }
}
