@php
    $employeeName = (string) ($payslip->employee?->name ?? 'Karyawan');
    $periodMonth = (string) ($payslip->payrollPeriod?->month ?? '-');
    $periodYear = (string) ($payslip->payrollPeriod?->year ?? '-');

    $monthsId = [
        '01' => 'Januari',
        '02' => 'Februari',
        '03' => 'Maret',
        '04' => 'April',
        '05' => 'Mei',
        '06' => 'Juni',
        '07' => 'Juli',
        '08' => 'Agustus',
        '09' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember',
    ];

    $periodLabel = ($monthsId[$periodMonth] ?? $periodMonth) . ' ' . $periodYear;

    $logoPath = public_path('assets/logos/1 black.svg');
    $logoData = null;
    if (is_file($logoPath)) {
        $logoData = 'data:image/svg+xml;base64,' . base64_encode(file_get_contents($logoPath));
    }
@endphp

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width: 100%; margin: 0; padding: 0; background-color: #f5f8fa;">
    <tr>
        <td align="center" style="padding: 24px 12px;">
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width: 600px; max-width: 600px; background-color: #ffffff; border: 1px solid #e5eaee; border-radius: 10px; overflow: hidden;">
                <tr>
                    <td style="padding: 18px 22px; background-color: #ffffff; border-bottom: 1px solid #e5eaee;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width: 100%;">
                            <tr>
                                <td style="width: 120px; vertical-align: middle;">
                                    @if ($logoData)
                                        <img src="{{ $logoData }}" alt="Altimeda" style="display: block; width: 110px; height: auto;" />
                                    @else
                                        <div style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; font-weight: 700; color: #111827;">ALTIMEDA</div>
                                    @endif
                                </td>
                                <td style="vertical-align: middle; text-align: right;">
                                    <div style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; font-weight: 700; color: #111827;">PT Altimeda Cipta Visitama</div>
                                    <div style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #6b7280; margin-top: 2px;">Slip Gaji</div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding: 22px;">
                        <div style="font-family: Arial, Helvetica, sans-serif; font-size: 13px; color: #111827; line-height: 1.6;">
                            <div style="font-weight: 700; font-size: 14px; margin-bottom: 10px;">Yth. Bapak/Ibu {{ $employeeName }},</div>
                            <div style="margin-bottom: 10px;">Berikut kami lampirkan slip gaji untuk periode <strong>{{ $periodLabel }}</strong>.</div>
                            <div style="margin-bottom: 14px;">Silakan periksa lampiran PDF pada email ini.</div>
                            <div style="margin-top: 18px;">Hormat kami,</div>
                            <div style="font-weight: 700;">Tim HRD</div>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding: 14px 22px; background-color: #f9fafb; border-top: 1px solid #e5eaee;">
                        <div style="font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #6b7280; line-height: 1.6;">
                            Email ini dikirim secara otomatis. Mohon tidak membalas email ini.
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
