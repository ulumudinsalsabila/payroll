<?php

namespace App\Http\Controllers;

use App\Models\PayrollPeriod;
use Illuminate\Http\Request;

class PayrollPeriodController extends Controller
{
    public function index()
    {
        $periods = PayrollPeriod::orderByDesc('created_at')->get();
        return view('payroll-periods.index', compact('periods'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'month' => ['required','string','size:2'],
            'year' => ['required','string','size:4'],
            'description' => ['nullable','string'],
        ]);

        $data['status'] = 'draft';
        $data['is_leave_distributed'] = false;

        PayrollPeriod::create($data);

        return redirect()->route('payroll-periods.index')->with('success', 'Periode gaji berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $payrollPeriod = PayrollPeriod::findOrFail($id);
        return view('payroll-periods.show', compact('payrollPeriod'));
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'month' => ['required','string','size:2'],
            'year' => ['required','string','size:4'],
            'description' => ['nullable','string'],
        ]);

        $period = PayrollPeriod::findOrFail($id);
        $period->update($data);

        return redirect()->route('payroll-periods.index')->with('success', 'Periode gaji berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $period = PayrollPeriod::findOrFail($id);
        $period->delete();

        return redirect()->route('payroll-periods.index')->with('success', 'Periode gaji berhasil dihapus.');
    }
}
