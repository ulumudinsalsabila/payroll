<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::orderBy('employee_code')->get();
        return view('employees.index', compact('employees'));
    }
    
    /**
     * Generate unique employee code with format ALT-XXXXXX
     */
    private function generateEmployeeCode(): string
    {
        do {
            $num = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $code = 'ALT-' . $num;
        } while (Employee::where('employee_code', $code)->exists());
        return $code;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['nullable','email','max:255', Rule::unique('employees', 'email')],
            'position' => ['required','string','max:255'],
            'department' => ['required','string','max:255'],
            'address' => ['nullable','string'],
            'bank_name' => ['nullable','string','max:255'],
            'bank_account_name' => ['nullable','string','max:255'],
            'bank_account_number' => ['nullable','string','max:255'],
            'join_date' => ['required','date'],
            'leave_balance' => ['nullable','integer','min:0'],
            'npwp_number' => ['nullable','string','max:100'],
            'ptkp_status' => ['required','string', Rule::in(['TK/0','TK/1','TK/2','TK/3','K/0','K/1','K/2','K/3'])],
            'ter_category' => ['required','string', Rule::in(['A','B','C'])],
        ]);

        $data['leave_balance'] = $data['leave_balance'] ?? 0;
        $data['employee_code'] = $this->generateEmployeeCode();

        $employee = Employee::create($data);

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
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['nullable','email','max:255', Rule::unique('employees', 'email')->ignore($employee->id, 'id')],
            'position' => ['required','string','max:255'],
            'department' => ['required','string','max:255'],
            'address' => ['nullable','string'],
            'bank_name' => ['nullable','string','max:255'],
            'bank_account_name' => ['nullable','string','max:255'],
            'bank_account_number' => ['nullable','string','max:255'],
            'join_date' => ['required','date'],
            'leave_balance' => ['nullable','integer','min:0'],
            'npwp_number' => ['nullable','string','max:100'],
            'ptkp_status' => ['required','string', Rule::in(['TK/0','TK/1','TK/2','TK/3','K/0','K/1','K/2','K/3'])],
            'ter_category' => ['required','string', Rule::in(['A','B','C'])],
        ]);

        $data['leave_balance'] = $data['leave_balance'] ?? 0;

        $employee->update($data);
        $employee->refresh();

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
