<?php

namespace App\Http\Controllers;

use App\Models\Employee;
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
            'position' => ['required','string','max:255'],
            'department' => ['required','string','max:255'],
            'address' => ['nullable','string'],
            'join_date' => ['required','date'],
            'leave_balance' => ['nullable','integer','min:0'],
            'npwp_number' => ['nullable','string','max:100'],
            'ptkp_status' => ['required','string', Rule::in(['TK/0','TK/1','TK/2','TK/3','K/0','K/1','K/2','K/3'])],
            'ter_category' => ['required','string', Rule::in(['A','B','C'])],
        ]);

        $data['leave_balance'] = $data['leave_balance'] ?? 0;
        $data['employee_code'] = $this->generateEmployeeCode();

        Employee::create($data);

        return redirect()->route('employees.index')->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $employee = Employee::findOrFail($id);
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'position' => ['required','string','max:255'],
            'department' => ['required','string','max:255'],
            'address' => ['nullable','string'],
            'join_date' => ['required','date'],
            'leave_balance' => ['nullable','integer','min:0'],
            'npwp_number' => ['nullable','string','max:100'],
            'ptkp_status' => ['required','string', Rule::in(['TK/0','TK/1','TK/2','TK/3','K/0','K/1','K/2','K/3'])],
            'ter_category' => ['required','string', Rule::in(['A','B','C'])],
        ]);

        $data['leave_balance'] = $data['leave_balance'] ?? 0;

        $employee->update($data);

        return redirect()->route('employees.index')->with('success', 'Karyawan berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Karyawan berhasil dihapus.');
    }

    public function show(string $id)
    {
        return redirect()->route('employees.index');
    }
}
