<?php

namespace App\Http\Controllers;

use App\Models\PayrollPeriod;
use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\Payslip;
use App\Models\PayslipComponent;
use App\Models\PayslipDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

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
        $payrollPeriod = PayrollPeriod::with(['payslips.employee', 'payslips.details'])->findOrFail($id);
        $employees = Employee::orderBy('name')->get();
        $earnings = PayslipComponent::where('is_active', true)->where('type', 'earning')->orderBy('name')->get();
        $deductions = PayslipComponent::where('is_active', true)->where('type', 'deduction')->orderBy('name')->get();
        $taxes = PayslipComponent::where('is_active', true)->where('type', 'tax')->orderBy('name')->get();

        $draftWorkDays = [];
        $draftTax = [];
        $draftNetto = [];
        $draftAmounts = [];

        foreach ($payrollPeriod->payslips as $payslip) {
            $draftWorkDays[$payslip->employee_id] = $payslip->work_days;
            $draftTax[$payslip->employee_id] = $payslip->tax_amount;
            $draftNetto[$payslip->employee_id] = $payslip->net_salary;

            foreach ($payslip->details as $detail) {
                $draftAmounts[$payslip->employee_id][$detail->payslip_component_id] = $detail->amount;
            }
        }

        return view('payroll-periods.show', compact('payrollPeriod', 'employees', 'earnings', 'deductions', 'taxes', 'draftWorkDays', 'draftTax', 'draftNetto', 'draftAmounts'));
    }

    public function calculateRow(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'string', 'exists:employees,id'],
            'earnings' => ['nullable', 'array'],
            'earnings.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $earnings = $validated['earnings'] ?? [];
        $totalEarnings = 0;
        foreach ($earnings as $val) {
            $totalEarnings += (int) round((float) $val);
        }

        $deductionComponents = PayslipComponent::query()
            ->where('is_active', true)
            ->where('type', 'deduction')
            ->orderBy('name')
            ->get(['id', 'percentage', 'max_cap']);

        $deductions = [];
        foreach ($deductionComponents as $comp) {
            $percentage = $comp->percentage === null ? 0.0 : (float) $comp->percentage;
            $base = $totalEarnings;
            if (!empty($comp->max_cap) && (int) $comp->max_cap > 0) {
                $base = min($base, (int) $comp->max_cap);
            }
            $amount = (int) round($base * ($percentage / 100));
            $deductions[$comp->id] = max($amount, 0);
        }

        $tax = 88608;
        $totalDeductions = array_sum($deductions);
        $netto = max($totalEarnings - $totalDeductions - $tax, 0);

        return response()->json([
            'deductions' => $deductions,
            'tax' => $tax,
            'netto' => $netto,
        ]);
    }

    public function saveDraft(Request $request, string $payrollPeriod)
    {
        $request->validate([
            'payslips' => ['nullable', 'array'],
            'payslips.*' => ['nullable', 'array'],
            'payslips.*.*' => ['nullable', 'numeric', 'min:0'],
            'work_days' => ['nullable', 'array'],
            'work_days.*' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'array'],
            'tax.*' => ['nullable', 'numeric', 'min:0'],
            'netto' => ['nullable', 'array'],
            'netto.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $period = PayrollPeriod::findOrFail($payrollPeriod);
        $payslips = $request->input('payslips', []);
        $workDaysInput = $request->input('work_days', []);
        $taxInput = $request->input('tax', []);
        $nettoInput = $request->input('netto', []);

        $componentTypeMap = PayslipComponent::pluck('type', 'id');
        $basicSalaryComponentId = PayslipComponent::where('name', 'Gaji Pokok')->value('id');

        $employeeIds = array_unique(array_merge(
            array_keys($payslips),
            array_keys($workDaysInput),
            array_keys($taxInput),
            array_keys($nettoInput)
        ));

        $paymentDate = Carbon::createFromDate((int) $period->year, (int) $period->month, 1)->endOfMonth()->toDateString();

        DB::transaction(function () use (
            $employeeIds,
            $payslips,
            $workDaysInput,
            $taxInput,
            $nettoInput,
            $period,
            $paymentDate,
            $componentTypeMap,
            $basicSalaryComponentId
        ) {
            foreach ($employeeIds as $employeeId) {
                $componentValues = $payslips[$employeeId] ?? [];
                $workDays = isset($workDaysInput[$employeeId]) && $workDaysInput[$employeeId] !== '' ? (int) $workDaysInput[$employeeId] : 0;
                $tax = isset($taxInput[$employeeId]) && $taxInput[$employeeId] !== '' ? (int) round((float) $taxInput[$employeeId]) : 0;
                $netto = isset($nettoInput[$employeeId]) && $nettoInput[$employeeId] !== '' ? (int) round((float) $nettoInput[$employeeId]) : 0;

                $hasAnyValue = $workDays > 0 || $tax > 0 || $netto > 0;
                foreach ($componentValues as $val) {
                    if ($val !== null && $val !== '') {
                        $hasAnyValue = true;
                        break;
                    }
                }

                $existingPayslip = Payslip::where('payroll_period_id', $period->id)
                    ->where('employee_id', $employeeId)
                    ->first();

                if (!$hasAnyValue) {
                    if ($existingPayslip) {
                        $existingPayslip->details()->delete();
                        $existingPayslip->delete();
                    }
                    continue;
                }

                $totalEarnings = 0;
                $totalDeductions = 0;
                $basicSalary = 0;

                foreach ($componentValues as $componentId => $val) {
                    $amount = $val !== null && $val !== '' ? (int) round((float) $val) : 0;
                    $type = $componentTypeMap[$componentId] ?? null;
                    if ($type === 'earning') {
                        $totalEarnings += $amount;
                    } elseif ($type === 'deduction') {
                        $totalDeductions += $amount;
                    }
                    if ($basicSalaryComponentId && $componentId === $basicSalaryComponentId) {
                        $basicSalary = $amount;
                    }
                }

                $payslip = Payslip::updateOrCreate(
                    ['payroll_period_id' => $period->id, 'employee_id' => $employeeId],
                    [
                        'payment_date' => $paymentDate,
                        'work_days' => $workDays,
                        'basic_salary' => $basicSalary,
                        'total_earnings' => $totalEarnings,
                        'total_deductions' => $totalDeductions,
                        'tax_amount' => $tax,
                        'net_salary' => $netto,
                        'status' => 'draft',
                    ]
                );

                foreach ($componentValues as $componentId => $val) {
                    $amount = $val !== null && $val !== '' ? (int) round((float) $val) : 0;
                    PayslipDetail::updateOrCreate(
                        ['payslip_id' => $payslip->id, 'payslip_component_id' => $componentId],
                        ['amount' => $amount]
                    );
                }
            }
        });

        $filledCells = 0;
        foreach ($payslips as $employeeId => $componentValues) {
            if (!is_array($componentValues)) {
                continue;
            }
            foreach ($componentValues as $componentId => $val) {
                if ($val !== null && $val !== '') {
                    $filledCells++;
                }
            }
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'SAVE_DRAFT',
            'module' => 'PAYROLL_PERIOD',
            'target_id' => $period->id,
            'description' => 'Menyimpan draft input gaji massal',
            'old_values' => null,
            'new_values' => ['filled_cells' => $filledCells],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('payroll-periods.show', $period->id)->with('success', 'Draft berhasil disimpan.');
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
