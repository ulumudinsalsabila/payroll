<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\ActivityLog;
use App\Models\LeaveTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
{
    public function index()
    {
        $departments = Employee::query()
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        $positions = Employee::query()
            ->whereNotNull('position')
            ->where('position', '!=', '')
            ->distinct()
            ->orderBy('position')
            ->pluck('position');

        return view('employees.index', compact('departments', 'positions'));
    }

    public function data(Request $request)
    {
        $query = Employee::query()->select([
            'id',
            'employee_code',
            'name',
            'email',
            'is_active',
            'position',
            'department',
            'address',
            'bank_name',
            'bank_account_name',
            'bank_account_number',
            'join_date',
            'leave_balance',
            'npwp_number',
            'fingerprint_id',
            'basic_salary',
        ]);

        return DataTables::eloquent($query)
            ->editColumn('join_date', function ($row) {
                return $row->join_date?->format('Y-m-d') ?? '';
            })
            ->editColumn('is_active', function ($row) {
                return $row->is_active ? 1 : 0;
            })
            ->filterColumn('department', function ($query, $keyword) {
                if ($keyword === null || $keyword === '') {
                    return;
                }
                $query->where('department', $keyword);
            })
            ->filterColumn('position', function ($query, $keyword) {
                if ($keyword === null || $keyword === '') {
                    return;
                }
                $query->where('position', $keyword);
            })
            ->toJson();
    }
    
    /**
     * Generate unique employee code with format HASNA-XXXXXX
     */
    private function generateEmployeeCode(): string
    {
        do {
            $num = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $code = 'HASNA-' . $num;
        } while (Employee::where('employee_code', $code)->exists());
        return $code;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['nullable','email','max:255', Rule::unique('employees', 'email')],
            'is_active' => ['nullable', 'boolean'],
            'position' => ['required','string','max:255'],
            'department' => ['required','string','max:255'],
            'address' => ['nullable','string'],
            'bank_name' => ['nullable','string','max:255'],
            'bank_account_name' => ['nullable','string','max:255'],
            'bank_account_number' => ['nullable','string','max:255'],
            'join_date' => ['required','date'],
            'leave_balance' => ['nullable','integer','min:0'],
            'npwp_number' => ['nullable','string','max:100'],
            'ptkp_status' => ['nullable','string'],
            'ter_category' => ['nullable','string'],
            'fingerprint_id' => ['nullable','string','max:255'],
            'basic_salary' => ['nullable','integer','min:0'],
        ]);

        if (!array_key_exists('leave_balance', $data) || $data['leave_balance'] === null) {
            $joinDate = Carbon::parse($data['join_date']);
            $joinYear = (int) $joinDate->format('Y');
            $nowYear = (int) Carbon::now()->format('Y');

            if ($joinYear === $nowYear) {
                $joinMonth = (int) $joinDate->format('n');
                $data['leave_balance'] = max(12 - $joinMonth + 1, 0);
            } else {
                $data['leave_balance'] = 12;
            }
        }

        $data['is_active'] = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true;
        $data['employee_code'] = $this->generateEmployeeCode();

        $employee = Employee::create($data);

        LeaveTransaction::create([
            'employee_id' => $employee->id,
            'payroll_period_id' => null,
            'payslip_id' => null,
            'transaction_date' => Carbon::parse($employee->join_date)->toDateString(),
            'type' => 'reset',
            'days' => (int) ($employee->leave_balance ?? 0),
            'description' => 'Saldo cuti awal (prorate)',
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'CREATE',
            'module' => 'EMPLOYEE',
            'target_id' => $employee->id,
            'description' => 'Menambahkan karyawan',
            'old_values' => null,
            'new_values' => $employee->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('employees.index')->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $employee = Employee::findOrFail($id);
        $before = $employee->toArray();
        $beforeLeaveBalance = (int) ($employee->leave_balance ?? 0);
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['nullable','email','max:255', Rule::unique('employees', 'email')->ignore($employee->id, 'id')],
            'is_active' => ['required', 'boolean'],
            'position' => ['required','string','max:255'],
            'department' => ['required','string','max:255'],
            'address' => ['nullable','string'],
            'bank_name' => ['nullable','string','max:255'],
            'bank_account_name' => ['nullable','string','max:255'],
            'bank_account_number' => ['nullable','string','max:255'],
            'join_date' => ['required','date'],
            'leave_balance' => ['nullable','integer','min:0'],
            'npwp_number' => ['nullable','string','max:100'],
            'ptkp_status' => ['nullable','string'],
            'ter_category' => ['nullable','string'],
            'fingerprint_id' => ['nullable','string','max:255'],
            'basic_salary' => ['nullable','integer','min:0'],
        ]);

        if (!array_key_exists('leave_balance', $data) || $data['leave_balance'] === null) {
            $data['leave_balance'] = $beforeLeaveBalance;
        }

        $employee->update($data);
        $employee->refresh();

        $afterLeaveBalance = (int) ($employee->leave_balance ?? 0);
        if ($afterLeaveBalance !== $beforeLeaveBalance) {
            LeaveTransaction::create([
                'employee_id' => $employee->id,
                'payroll_period_id' => null,
                'payslip_id' => null,
                'transaction_date' => Carbon::now()->toDateString(),
                'type' => 'reset',
                'days' => $afterLeaveBalance,
                'description' => 'Penyesuaian saldo cuti',
            ]);
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'UPDATE',
            'module' => 'EMPLOYEE',
            'target_id' => $employee->id,
            'description' => 'Memperbarui karyawan',
            'old_values' => $before,
            'new_values' => $employee->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('employees.index')->with('success', 'Karyawan berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $employee = Employee::findOrFail($id);
        $before = $employee->toArray();
        $employee->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'DELETE',
            'module' => 'EMPLOYEE',
            'target_id' => $id,
            'description' => 'Menghapus karyawan',
            'old_values' => $before,
            'new_values' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
        return redirect()->route('employees.index')->with('success', 'Karyawan berhasil dihapus.');
    }

    public function show(string $id)
    {
        return redirect()->route('employees.index');
    }
}
