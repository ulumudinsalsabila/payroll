<?php

namespace App\Http\Controllers;

use App\Exports\PayslipTemplateExport;
use App\Imports\PayslipTemplateImport;
use App\Models\PayrollPeriod;
use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\LeaveTransaction;
use App\Models\Payslip;
use App\Models\PayslipComponent;
use App\Models\PayslipDetail;
use App\Mail\PayslipPdfMail;
use App\Services\PayrollService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class PayrollPeriodController extends Controller
{
    public function __construct(private readonly PayrollService $payrollService)
    {
    }

    private function recalculateLeaveBalance(string $employeeId): int
    {
        $txs = LeaveTransaction::query()
            ->where('employee_id', $employeeId)
            ->orderBy('transaction_date')
            ->orderBy('created_at')
            ->get(['type', 'days']);

        $balance = 0;
        foreach ($txs as $tx) {
            $type = strtolower(trim((string) $tx->type));
            $days = max((int) $tx->days, 0);

            if ($type === 'reset') {
                $balance = $days;
                continue;
            }

            if ($type === 'accrual') {
                $balance += $days;
            } elseif ($type === 'joint' || $type === 'personal') {
                $balance -= $days;
            }

            if ($balance < 0) {
                $balance = 0;
            }
        }

        return max($balance, 0);
    }

    public function index()
    {
        return view('payroll-periods.index');
    }

    public function data(Request $request)
    {
        $query = PayrollPeriod::query()->select([
            'id',
            'month',
            'year',
            'description',
            'status',
            'default_work_days',
            'created_at',
        ]);

        return DataTables::eloquent($query)->toJson();
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
            'default_work_days' => ['required','integer','min:1','max:31'],
            'description' => ['nullable','string'],
        ], [
            'month.unique' => 'Periode dengan bulan dan tahun tersebut sudah ada.',
            'default_work_days.required' => 'Hari kerja default wajib diisi.',
            'default_work_days.min' => 'Hari kerja minimal 1 hari.',
            'default_work_days.max' => 'Hari kerja maksimal 31 hari.',
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
        $employees = Employee::where('is_active', true)->orderBy('name')->get();
        $earnings = PayslipComponent::where('is_active', true)->where('type', 'earning')->orderBy('name')->get();
        $deductions = PayslipComponent::where('is_active', true)->where('type', 'deduction')->orderBy('name')->get();
        $taxes = PayslipComponent::where('is_active', true)->where('type', 'tax')->orderBy('name')->get();

        $basicSalaryComponentId = PayslipComponent::where('is_active', true)
            ->where('type', 'earning')
            ->where('name', 'Gaji Pokok')
            ->value('id');

        $pph21ComponentId = PayslipComponent::where('is_active', true)
            ->where('type', 'tax')
            ->where('name', 'PPh Pasal 21')
            ->value('id');

        $draftWorkDays = [];
        $draftNetto = [];
        $draftAmounts = [];

        foreach ($payrollPeriod->payslips as $payslip) {
            $draftWorkDays[$payslip->employee_id] = $payslip->work_days;
            $draftNetto[$payslip->employee_id] = $payslip->net_salary;

            foreach ($payslip->details as $detail) {
                $draftAmounts[$payslip->employee_id][$detail->payslip_component_id] = $detail->amount;
            }

            if ($pph21ComponentId && !isset($draftAmounts[$payslip->employee_id][$pph21ComponentId]) && $payslip->tax_amount !== null) {
                $draftAmounts[$payslip->employee_id][$pph21ComponentId] = (int) $payslip->tax_amount;
            }
        }

        // Auto-fill basic salary for employees without existing payslips
        foreach ($employees as $employee) {
            if (!isset($draftAmounts[$employee->id][$basicSalaryComponentId]) && $basicSalaryComponentId && $employee->basic_salary > 0) {
                $draftAmounts[$employee->id][$basicSalaryComponentId] = $employee->basic_salary;
            }
        }

        return view('payroll-periods.show', compact('payrollPeriod', 'employees', 'earnings', 'deductions', 'taxes', 'draftWorkDays', 'draftNetto', 'draftAmounts', 'basicSalaryComponentId'));
    }

    public function downloadTemplate(Request $request, string $id)
    {
        $payrollPeriod = PayrollPeriod::findOrFail($id);
        $mode = (string) $request->query('mode', 'empty');

        $employees = Employee::where('is_active', true)->orderBy('name')->get();
        $components = PayslipComponent::query()
            ->where('is_active', true)
            ->whereIn('type', ['earning', 'deduction', 'tax'])
            ->orderByRaw("case when type = 'earning' then 1 when type = 'deduction' then 2 when type = 'tax' then 3 else 4 end")
            ->orderBy('name')
            ->get();

        $previousPeriod = null;
        if ($mode === 'last_period') {
            $previousPeriod = PayrollPeriod::where('created_at', '<', $payrollPeriod->created_at)->latest()->first();
            if (!$previousPeriod) {
                return redirect()->back()->with('error', 'Belum ada data periode gaji sebelumnya untuk disalin.');
            }
        }

        return Excel::download(
            new PayslipTemplateExport($employees, $components, $previousPeriod),
            'Template_Gaji_' . $payrollPeriod->month . '_' . $payrollPeriod->year . '.xlsx'
        );
    }

    private function parseExcelInt($value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        return (int) round((float) $value);
    }

    public function importTemplate(Request $request, string $payrollPeriod)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls'],
        ]);

        $period = PayrollPeriod::findOrFail($payrollPeriod);

        if ($period->status !== 'draft') {
            return redirect()->route('payroll-periods.show', $period->id)->with('error', 'Periode sudah dipublish. Silakan kembalikan ke draft terlebih dahulu jika ingin mengubah data.');
        }

        $sheets = Excel::toArray(new PayslipTemplateImport(), $request->file('file'));
        $rows = $sheets[0] ?? [];
        if (!is_array($rows) || count($rows) < 2) {
            return redirect()->back()->with('error', 'File Excel kosong atau format tidak valid.');
        }

        $headers = $rows[0] ?? [];
        if (!is_array($headers)) {
            return redirect()->back()->with('error', 'Header file Excel tidak valid.');
        }

        $headers = array_map(function ($h) {
            return is_string($h) ? trim($h) : (string) $h;
        }, $headers);

        $lastHeaderIndex = -1;
        foreach ($headers as $i => $h) {
            if ($h !== '') {
                $lastHeaderIndex = (int) $i;
            }
        }

        if ($lastHeaderIndex < 3 || ($headers[$lastHeaderIndex] ?? '') !== 'Netto') {
            return redirect()->back()->with('error', 'Format kolom tidak sesuai template (kolom terakhir harus Netto).');
        }

        if (($headers[0] ?? '') !== 'Kode Karyawan' || ($headers[1] ?? '') !== 'Nama Karyawan' || ($headers[2] ?? '') !== 'Hari Kerja') {
            return redirect()->back()->with('error', 'Format kolom awal tidak sesuai template.');
        }

        $componentStartIndex = 3;
        $hasLeaveColumns = ($headers[3] ?? '') === 'Cuti Bersama' && ($headers[4] ?? '') === 'Cuti Pribadi';
        if ($hasLeaveColumns) {
            $componentStartIndex = 5;
        }

        if ($lastHeaderIndex < $componentStartIndex) {
            return redirect()->back()->with('error', 'Format kolom tidak sesuai template.');
        }

        $componentHeaders = array_slice($headers, $componentStartIndex, $lastHeaderIndex - $componentStartIndex);
        $components = PayslipComponent::query()
            ->where('is_active', true)
            ->whereIn('type', ['earning', 'deduction', 'tax'])
            ->get(['id', 'name', 'type']);

        $componentIdByName = $components->pluck('id', 'name')->toArray();
        $componentTypeMap = $components->pluck('type', 'id')->toArray();

        $columnToComponentId = [];
        foreach ($componentHeaders as $idx => $name) {
            $colIndex = $componentStartIndex + $idx;
            $name = trim((string) $name);
            if ($name === '') {
                return redirect()->back()->with('error', 'Ada kolom komponen kosong di header template.');
            }
            $componentId = $componentIdByName[$name] ?? null;
            if (!$componentId) {
                return redirect()->back()->with('error', 'Komponen tidak ditemukan/aktif: ' . $name);
            }
            $columnToComponentId[$colIndex] = $componentId;
        }

        $basicSalaryComponentId = PayslipComponent::where('name', 'Gaji Pokok')->value('id');
        $paymentDate = Carbon::createFromDate((int) $period->year, (int) $period->month, 1)->endOfMonth()->toDateString();

        $skippedCodes = [];
        $importedRows = 0;

        try {
            DB::transaction(function () use (
                $rows,
                $lastHeaderIndex,
                $columnToComponentId,
                $componentTypeMap,
                $basicSalaryComponentId,
                $period,
                $paymentDate,
                &$skippedCodes,
                &$importedRows
            ) {
                for ($r = 1; $r < count($rows); $r++) {
                    $row = $rows[$r] ?? [];
                    if (!is_array($row) || count($row) === 0) {
                        continue;
                    }

                    $employeeCode = isset($row[0]) ? trim((string) $row[0]) : '';
                    if ($employeeCode === '') {
                        continue;
                    }

                    $employee = Employee::where('employee_code', $employeeCode)->where('is_active', true)->first();
                    if (!$employee) {
                        $skippedCodes[] = $employeeCode;
                        continue;
                    }

                    $workDays = $this->parseExcelInt($row[2] ?? 0);
                    $netto = $this->parseExcelInt($row[$lastHeaderIndex] ?? 0);

                    $componentValues = [];
                    foreach ($columnToComponentId as $colIndex => $componentId) {
                        $componentValues[$componentId] = $this->parseExcelInt($row[$colIndex] ?? 0);
                    }

                    $totalEarnings = 0;
                    $totalDeductions = 0;
                    $totalTax = 0;
                    $basicSalary = 0;

                    // Auto-fill basic salary from employee data if not provided in Excel
                    $employeeBasicSalary = (int) ($employee->basic_salary ?? 0);
                    
                    foreach ($componentValues as $componentId => $amount) {
                        $type = $componentTypeMap[$componentId] ?? null;
                        if ($type === 'earning') {
                            $totalEarnings += $amount;
                        } elseif ($type === 'deduction') {
                            $totalDeductions += $amount;
                        } elseif ($type === 'tax') {
                            $totalTax += $amount;
                        }
                        if ($basicSalaryComponentId && $componentId === $basicSalaryComponentId) {
                            // Use Excel value if provided, otherwise use employee's basic salary
                            $basicSalary = $amount > 0 ? $amount : $employeeBasicSalary;
                            // Update component values with employee's basic salary if Excel value is empty
                            if ($amount === 0 && $employeeBasicSalary > 0) {
                                $componentValues[$componentId] = $employeeBasicSalary;
                                $totalEarnings += $employeeBasicSalary;
                            }
                        }
                    }

                    // Calculate net salary: total earnings - total deductions - tax amount
                    $calculatedNetto = $totalEarnings - $totalDeductions - $totalTax;
                    
                    $payslip = Payslip::updateOrCreate(
                        ['payroll_period_id' => $period->id, 'employee_id' => $employee->id],
                        [
                            'payment_date' => $paymentDate,
                            'work_days' => $workDays,
                            'basic_salary' => $basicSalary,
                            'total_earnings' => $totalEarnings,
                            'total_deductions' => $totalDeductions,
                            'tax_amount' => $totalTax,
                            'net_salary' => $calculatedNetto,
                            'status' => 'draft',
                        ]
                    );

                    foreach ($componentValues as $componentId => $amount) {
                        PayslipDetail::updateOrCreate(
                            ['payslip_id' => $payslip->id, 'payslip_component_id' => $componentId],
                            ['amount' => $amount]
                        );
                    }

                    $importedRows++;
                }

                });
        } catch (ValidationException $e) {
            return redirect()
                ->route('payroll-periods.show', $period->id)
                ->withErrors($e->errors())
                ->with('error', 'Import gagal. Silakan perbaiki file Excel dan upload ulang.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'IMPORT_EXCEL',
            'module' => 'PAYROLL_PERIOD',
            'target_id' => $period->id,
            'description' => 'Import template Excel gaji massal',
            'old_values' => null,
            'new_values' => ['imported_rows' => $importedRows, 'skipped_codes' => $skippedCodes],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $msg = 'Import berhasil. Baris terproses: ' . $importedRows . '.';
        if (count($skippedCodes) > 0) {
            $msg .= ' Kode karyawan tidak ditemukan (di-skip): ' . implode(', ', array_values(array_unique($skippedCodes))) . '.';
        }

        return redirect()->route('payroll-periods.show', $period->id)->with('success', $msg);
    }

    public function pullAttendance(string $payrollPeriod)
    {
        $period = PayrollPeriod::findOrFail($payrollPeriod);

        if ($period->status !== 'draft') {
            return redirect()->route('payroll-periods.show', $period->id)->with('error', 'Periode ini sudah tidak dalam status draft.');
        }

        $periodMonth = $period->year . '-' . $period->month;
        $attendances = Attendance::where('period_month', $periodMonth)->get();

        if ($attendances->isEmpty()) {
            return redirect()->route('payroll-periods.show', $period->id)->with('error', 'Tidak ada data rekap absensi untuk periode ' . $periodMonth);
        }

        $imported = 0;
        $paymentDate = Carbon::createFromDate((int) $period->year, (int) $period->month, 1)->endOfMonth()->toDateString();
        $basicSalaryComponentId = PayslipComponent::where('name', 'Gaji Pokok')->value('id');

        foreach ($attendances as $attendance) {
            $employee = $attendance->employee;
            
            if (!$employee) {
                $employee = Employee::where('fingerprint_id', $attendance->fingerprint_id)
                                    ->orWhere('name', 'like', "%{$attendance->employee_name}%")
                                    ->first();
            }
            
            if (!$employee) {
                \Log::warning("Payroll: Could not find employee for attendance record: " . $attendance->employee_name . " (ID: " . $attendance->fingerprint_id . ")");
                continue;
            }

            // Create or get existing payslip
            $payslip = Payslip::firstOrCreate([
                'payroll_period_id' => $period->id,
                'employee_id' => $employee->id,
            ], [
                'payment_date' => $paymentDate,
                'status' => 'draft',
                'basic_salary' => $employee->basic_salary ?? 0,
                'total_earnings' => 0,
                'total_deductions' => 0,
                'tax_amount' => 0,
                'net_salary' => 0,
            ]);

            // Update work days from attendance
            $payslip->work_days = $attendance->present_days;
            $payslip->save();

            // Auto-fill Gaji Pokok if not exists
            if ($basicSalaryComponentId && $employee->basic_salary > 0) {
                PayslipDetail::updateOrCreate([
                    'payslip_id' => $payslip->id,
                    'payslip_component_id' => $basicSalaryComponentId,
                ], [
                    'amount' => $employee->basic_salary
                ]);
            }

            $imported++;
        }

        return redirect()->route('payroll-periods.show', $period->id)
            ->with('success', "Berhasil menarik data absensi untuk $imported karyawan.");
    }

    public function previewPdf(Request $request, string $payrollPeriod): Response
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'string'],
        ]);

        $period = PayrollPeriod::findOrFail($payrollPeriod);

        $payslip = Payslip::query()
            ->where('payroll_period_id', $period->id)
            ->where('employee_id', $validated['employee_id'])
            ->with(['employee', 'payrollPeriod', 'details.component'])
            ->first();

        if (!$payslip) {
            abort(404, 'Payslip untuk karyawan tersebut belum ada. Silakan simpan draft terlebih dahulu.');
        }

        $fileName = 'Payslip_' . ($period->month ?? '') . '_' . ($period->year ?? '') . '_' . ($payslip->employee->name ?? 'Karyawan') . '.pdf';
        $fileName = str_replace(['/', '\\'], '-', $fileName);

        if (app()->bound('dompdf.wrapper')) {
            $pdf = app('dompdf.wrapper');
            $pdf->loadView('pdf.payslip', ['payslip' => $payslip]);
            $pdf->setPaper('a4', 'portrait');
            return $pdf->stream($fileName);
        }

        return response()->view('pdf.payslip', ['payslip' => $payslip]);
    }

    public function publish(Request $request, string $payrollPeriod)
    {
        $period = PayrollPeriod::findOrFail($payrollPeriod);

        if ($period->status !== 'draft') {
            return redirect()->route('payroll-periods.show', $period->id)->with('error', 'Periode ini sudah tidak dalam status draft.');
        }

        $payslips = Payslip::query()
            ->where('payroll_period_id', $period->id)
            ->with(['employee', 'payrollPeriod', 'details.component'])
            ->get();

        if ($payslips->isEmpty()) {
            return redirect()->route('payroll-periods.show', $period->id)->with('error', 'Tidak ada payslip pada periode ini. Silakan simpan draft terlebih dahulu.');
        }

        try {
            DB::transaction(function () use ($period, $payslips) {
                LeaveTransaction::query()
                    ->where('payroll_period_id', $period->id)
                    ->delete();

                if ($period->month === '01') {
                    $resetDate = Carbon::createFromDate((int) $period->year, 1, 1)->toDateString();
                    $allEmployees = Employee::query()->get(['id']);
                    foreach ($allEmployees as $emp) {
                        LeaveTransaction::create([
                            'employee_id' => $emp->id,
                            'payroll_period_id' => $period->id,
                            'payslip_id' => null,
                            'transaction_date' => $resetDate,
                            'type' => 'reset',
                            'days' => 12,
                            'description' => 'Reset saldo cuti tahunan (periode Januari ' . $period->year . ')',
                        ]);

                        Employee::where('id', $emp->id)->update(['leave_balance' => 12]);
                    }
                }

                $fieldErrors = [];
                $paymentDate = Carbon::createFromDate((int) $period->year, (int) $period->month, 1)->endOfMonth()->toDateString();

                foreach ($payslips as $payslip) {
                    $employee = $payslip->employee;
                    if (!$employee) {
                        continue;
                    }

                    $joint = max((int) ($payslip->leave_joint_days ?? 0), 0);
                    $personal = max((int) ($payslip->leave_personal_days ?? 0), 0);
                    $used = $joint + $personal;

                    $entitlement = max((int) ($employee->leave_balance ?? 0), 0);
                    if ($used > $entitlement) {
                        $message = 'Total cuti (' . $used . ') melebihi saldo cuti (' . $entitlement . ').';
                        $fieldErrors['leave_joint_days.' . $employee->id] = $message;
                        $fieldErrors['leave_personal_days.' . $employee->id] = $message;
                        continue;
                    }

                    $remaining = max($entitlement - $used, 0);

                    $payslip->leave_entitlement = $entitlement;
                    $payslip->leave_remaining = $remaining;
                    $payslip->save();

                    if ($joint > 0) {
                        LeaveTransaction::create([
                            'employee_id' => $employee->id,
                            'payroll_period_id' => $period->id,
                            'payslip_id' => $payslip->id,
                            'transaction_date' => $paymentDate,
                            'type' => 'joint',
                            'days' => $joint,
                            'description' => 'Cuti bersama (periode ' . $period->month . '/' . $period->year . ')',
                        ]);
                    }

                    if ($personal > 0) {
                        LeaveTransaction::create([
                            'employee_id' => $employee->id,
                            'payroll_period_id' => $period->id,
                            'payslip_id' => $payslip->id,
                            'transaction_date' => $paymentDate,
                            'type' => 'personal',
                            'days' => $personal,
                            'description' => 'Cuti personal (periode ' . $period->month . '/' . $period->year . ')',
                        ]);
                    }

                    Employee::where('id', $employee->id)->update(['leave_balance' => $remaining]);
                }

                if (count($fieldErrors) > 0) {
                    throw ValidationException::withMessages($fieldErrors);
                }

                $period->is_leave_distributed = true;
                $period->status = 'published';
                $period->published_at = Carbon::now();
                $period->save();

                Payslip::where('payroll_period_id', $period->id)->update(['status' => 'published']);
            });

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'PUBLISH_PERIOD',
                'module' => 'PAYROLL_PERIOD',
                'target_id' => $period->id,
                'description' => 'Publish periode gaji (finalisasi)',
                'old_values' => ['status' => 'draft'],
                'new_values' => ['status' => 'published'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('payroll-periods.show', $period->id)->with('success', 'Periode berhasil dipublish (finalisasi).');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('payroll-periods.show', $period->id)->with('error', $e->getMessage());
        }
    }

    public function sendEmails(Request $request, string $payrollPeriod)
    {
        $period = PayrollPeriod::findOrFail($payrollPeriod);

        if ($period->status !== 'published') {
            return redirect()->route('payroll-periods.show', $period->id)->with('error', 'Email hanya bisa dikirim jika periode sudah dipublish.');
        }

        if (!app()->bound('dompdf.wrapper')) {
            return redirect()->route('payroll-periods.show', $period->id)->with('error', 'DomPDF belum terpasang/terdaftar.');
        }

        $payslips = Payslip::query()
            ->where('payroll_period_id', $period->id)
            ->with(['employee', 'payrollPeriod', 'details.component'])
            ->get();

        $sent = 0;
        $skipped = 0;
        $failed = 0;
        $failedLabels = [];

        foreach ($payslips as $payslip) {
            $email = trim((string) optional($payslip->employee)->email);
            if ($email === '') {
                $skipped++;
                continue;
            }

            $fileName = 'Payslip_' . ($period->month ?? '') . '_' . ($period->year ?? '') . '_' . (optional($payslip->employee)->name ?? 'Karyawan') . '.pdf';
            $fileName = str_replace(['/', '\\'], '-', $fileName);

            try {
                $pdf = app('dompdf.wrapper');
                $pdf->loadView('pdf.payslip', ['payslip' => $payslip]);
                $pdf->setPaper('a4', 'portrait');
                $pdfBytes = $pdf->output();

                Mail::to($email)->send(new PayslipPdfMail($payslip, $pdfBytes, $fileName));
                $sent++;
            } catch (\Throwable $e) {
                $failed++;
                $label = (optional($payslip->employee)->employee_code ?? '-') . ' - ' . (optional($payslip->employee)->name ?? '');
                $failedLabels[] = $label;
            }
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'SEND_PAYSLIP_EMAILS',
            'module' => 'PAYROLL_PERIOD',
            'target_id' => $period->id,
            'description' => 'Mengirim PDF slip gaji via email',
            'old_values' => null,
            'new_values' => [
                'sent' => $sent,
                'skipped' => $skipped,
                'failed' => $failed,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $msg = 'Email terkirim: ' . $sent . '. Di-skip (tanpa email): ' . $skipped . '. Gagal: ' . $failed . '.';
        if ($failed > 0 && count($failedLabels) > 0) {
            $some = array_slice($failedLabels, 0, 10);
            $msg .= ' Detail gagal: ' . implode(', ', $some) . (count($failedLabels) > 10 ? ', dll.' : '') . '.';
        }

        return redirect()->route('payroll-periods.show', $period->id)->with('success', $msg);
    }

    public function reopenDraft(Request $request, string $payrollPeriod)
    {
        $period = PayrollPeriod::findOrFail($payrollPeriod);

        if ($period->status !== 'published') {
            return redirect()->route('payroll-periods.show', $period->id)->with('error', 'Periode ini tidak dalam status published.');
        }

        $before = $period->toArray();

        DB::transaction(function () use ($period) {
            $employeeIds = LeaveTransaction::query()
                ->where('payroll_period_id', $period->id)
                ->pluck('employee_id')
                ->unique()
                ->values();

            LeaveTransaction::query()
                ->where('payroll_period_id', $period->id)
                ->delete();

            foreach ($employeeIds as $employeeId) {
                $newBalance = $this->recalculateLeaveBalance((string) $employeeId);
                Employee::where('id', $employeeId)->update(['leave_balance' => $newBalance]);
            }

            Payslip::where('payroll_period_id', $period->id)->update([
                'status' => 'draft',
                'leave_entitlement' => null,
                'leave_remaining' => null,
            ]);

            $period->status = 'draft';
            $period->published_at = null;
            $period->is_leave_distributed = false;
            $period->save();
        });

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'REOPEN_DRAFT',
            'module' => 'PAYROLL_PERIOD',
            'target_id' => $period->id,
            'description' => 'Mengembalikan periode gaji ke status draft',
            'old_values' => $before,
            'new_values' => $period->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('payroll-periods.show', $period->id)->with('success', 'Periode berhasil dikembalikan ke draft.');
    }

    public function calculateRow(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'string'],
            'earnings' => ['nullable', 'array'],
            'earnings.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $result = $this->payrollService->calculateForEmployee(
                $validated['employee_id'],
                $validated['earnings'] ?? []
            );

            return response()->json($result);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Karyawan tidak ditemukan.',
            ], 404);
        }
    }

    public function saveDraft(Request $request, string $payrollPeriod)
    {
        $request->validate([
            'payslips' => ['nullable', 'array'],
            'payslips.*' => ['nullable', 'array'],
            'payslips.*.*' => ['nullable', 'numeric', 'min:0'],
            'work_days' => ['nullable', 'array'],
            'work_days.*' => ['nullable', 'numeric', 'min:0'],
            'netto' => ['nullable', 'array'],
            'netto.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $period = PayrollPeriod::findOrFail($payrollPeriod);

        if ($period->status !== 'draft') {
            return redirect()->route('payroll-periods.show', $period->id)->with('error', 'Periode sudah dipublish. Silakan kembalikan ke draft terlebih dahulu jika ingin mengubah data.');
        }
        $payslips = $request->input('payslips', []);
        $workDaysInput = $request->input('work_days', []);
        $nettoInput = $request->input('netto', []);

        $componentTypeMap = PayslipComponent::pluck('type', 'id');
        $basicSalaryComponentId = PayslipComponent::where('name', 'Gaji Pokok')->value('id');

        $employeeIds = array_unique(array_merge(
            array_keys($payslips),
            array_keys($workDaysInput),
            array_keys($nettoInput)
        ));

        $errors = [];
        $entitlementMap = [];
        if ($period->month !== '01' && count($employeeIds) > 0) {
            $entitlementMap = Employee::query()
                ->whereIn('id', $employeeIds)
                ->pluck('leave_balance', 'id')
                ->map(fn ($v) => max((int) ($v ?? 0), 0))
                ->toArray();
        }

        foreach ($employeeIds as $employeeId) {
            $joint = isset($leaveJointInput[$employeeId]) && $leaveJointInput[$employeeId] !== '' ? (int) round((float) $leaveJointInput[$employeeId]) : 0;
            $personal = isset($leavePersonalInput[$employeeId]) && $leavePersonalInput[$employeeId] !== '' ? (int) round((float) $leavePersonalInput[$employeeId]) : 0;
            $joint = max($joint, 0);
            $personal = max($personal, 0);
            $used = $joint + $personal;
            if ($used <= 0) {
                continue;
            }

            $entitlement = $period->month === '01' ? 12 : ($entitlementMap[$employeeId] ?? 0);
            if ($used > $entitlement) {
                $message = 'Total cuti (' . $used . ') melebihi saldo cuti (' . $entitlement . ').';
                $errors['leave_joint_days.' . $employeeId] = $message;
                $errors['leave_personal_days.' . $employeeId] = $message;
            }
        }

        if (count($errors) > 0) {
            throw ValidationException::withMessages($errors);
        }

        $paymentDate = Carbon::createFromDate((int) $period->year, (int) $period->month, 1)->endOfMonth()->toDateString();

        DB::transaction(function () use (
            $employeeIds,
            $payslips,
            $workDaysInput,
            $nettoInput,
            $period,
            $paymentDate,
            $componentTypeMap,
            $basicSalaryComponentId
        ) {
            foreach ($employeeIds as $employeeId) {
                $componentValues = $payslips[$employeeId] ?? [];
                $workDays = isset($workDaysInput[$employeeId]) && $workDaysInput[$employeeId] !== '' ? (int) $workDaysInput[$employeeId] : 0;
                $netto = isset($nettoInput[$employeeId]) && $nettoInput[$employeeId] !== '' ? (int) round((float) $nettoInput[$employeeId]) : 0;

                $hasAnyValue = $workDays > 0 || $netto > 0;
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
                $totalTax = 0;
                $basicSalary = 0;

                foreach ($componentValues as $componentId => $val) {
                    $amount = $val !== null && $val !== '' ? (int) round((float) $val) : 0;
                    $type = $componentTypeMap[$componentId] ?? null;
                    if ($type === 'earning') {
                        $totalEarnings += $amount;
                    } elseif ($type === 'deduction') {
                        $totalDeductions += $amount;
                    } elseif ($type === 'tax') {
                        $totalTax += $amount;
                    }
                    if ($basicSalaryComponentId && $componentId === $basicSalaryComponentId) {
                        $basicSalary = $amount;
                    }
                }

                // Calculate net salary: total earnings - total deductions - tax amount
                $calculatedNetto = $totalEarnings - $totalDeductions - $totalTax;
                
                $payslip = Payslip::updateOrCreate(
                    ['payroll_period_id' => $period->id, 'employee_id' => $employeeId],
                    [
                        'payment_date' => $paymentDate,
                        'work_days' => $workDays,
                        'basic_salary' => $basicSalary,
                        'total_earnings' => $totalEarnings,
                        'total_deductions' => $totalDeductions,
                        'tax_amount' => $totalTax,
                        'net_salary' => $calculatedNetto,
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
            'default_work_days' => ['required','integer','min:1','max:31'],
            'description' => ['nullable','string'],
        ], [
            'month.unique' => 'Periode dengan bulan dan tahun tersebut sudah ada.',
            'default_work_days.required' => 'Hari kerja default wajib diisi.',
            'default_work_days.min' => 'Hari kerja minimal 1 hari.',
            'default_work_days.max' => 'Hari kerja maksimal 31 hari.',
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
