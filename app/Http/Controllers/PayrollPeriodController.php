<?php

namespace App\Http\Controllers;

use App\Models\PayrollPeriod;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            'month' => [
                'required','string','size:2',
                Rule::unique('payroll_periods', 'month')->where(function ($q) use ($request) {
                    return $q->where('year', $request->year);
                }),
            ],
            'year' => ['required','string','size:4'],
            'description' => ['nullable','string'],
        ], [
            'month.unique' => 'Periode dengan bulan dan tahun tersebut sudah ada.',
        ]);

        $data['status'] = 'draft';
        $data['is_leave_distributed'] = false;

        $period = PayrollPeriod::create($data);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'CREATE',
            'module' => 'PAYROLL_PERIOD',
            'target_id' => $period->id,
            'description' => 'Menambahkan periode gaji',
            'old_values' => null,
            'new_values' => $period->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

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
            'month' => [
                'required','string','size:2',
                Rule::unique('payroll_periods', 'month')
                    ->ignore($id, 'id')
                    ->where(function ($q) use ($request) {
                        return $q->where('year', $request->year);
                    }),
            ],
            'year' => ['required','string','size:4'],
            'description' => ['nullable','string'],
        ], [
            'month.unique' => 'Periode dengan bulan dan tahun tersebut sudah ada.',
        ]);

        $period = PayrollPeriod::findOrFail($id);
        $before = $period->toArray();
        $period->update($data);
        $period->refresh();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'UPDATE',
            'module' => 'PAYROLL_PERIOD',
            'target_id' => $period->id,
            'description' => 'Memperbarui periode gaji',
            'old_values' => $before,
            'new_values' => $period->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('payroll-periods.index')->with('success', 'Periode gaji berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $period = PayrollPeriod::findOrFail($id);
        $before = $period->toArray();
        $period->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'DELETE',
            'module' => 'PAYROLL_PERIOD',
            'target_id' => $id,
            'description' => 'Menghapus periode gaji',
            'old_values' => $before,
            'new_values' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('payroll-periods.index')->with('success', 'Periode gaji berhasil dihapus.');
    }
}
