<div>
    <div>Yth. Bapak/Ibu {{ $payslip->employee?->name ?? 'Karyawan' }},</div>
    <div style="margin-top: 10px;">Berikut kami lampirkan slip gaji untuk periode {{ $payslip->payrollPeriod?->month ?? '-' }}/{{ $payslip->payrollPeriod?->year ?? '-' }}.</div>
    <div style="margin-top: 10px;">Terima kasih.</div>
</div>
