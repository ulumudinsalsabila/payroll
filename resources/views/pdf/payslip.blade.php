<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Payslip</title>
    <style>
        @page { margin: 24px 24px 24px 24px; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #000000; }
        table { border-collapse: collapse; }

        .w-100 { width: 100%; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .align-top { vertical-align: top; }
        .fw-bold { font-weight: bold; }

        .company-name { font-size: 13px; font-weight: bold; }
        .employee-name { font-size: 12px; font-weight: bold; }
        .employee-address { font-size: 10px; color: #111111; }

        .info-table td { padding: 1px 0; }
        .info-table .label { width: 140px; font-weight: bold; }
        .info-table .colon { width: 10px; }

        .hr-thick { border-top: 2px solid #000000; height: 1px; }
        .hr-thin { border-top: 1px solid #000000; height: 1px; }

        .section-center { font-size: 12px; font-weight: bold; text-align: center; }

        .detail-table td { padding: 2px 0; }
        .detail-table .col-label { width: 170px; font-weight: bold; vertical-align: top; }
        .detail-table .col-item { width: auto; padding-left: 12px; }
        .detail-table .col-unit { width: 90px; text-align: center; }
        .detail-table .col-amt { width: 140px; text-align: right; }

        .amt-line { border-top: 1px solid #777777; padding-top: 3px; }
        .amt-line-strong { border-top: 2px solid #777777; padding-top: 4px; }

        .notes { font-size: 10px; }
    </style>
</head>
<body>
@php
    $payslip = $payslip ?? null;

    $monthsId = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    $periodMonth = (int) (optional(optional($payslip)->payrollPeriod)->month ?? date('m'));
    $periodYear = (int) (optional(optional($payslip)->payrollPeriod)->year ?? date('Y'));

    $startDate = \Carbon\Carbon::create($periodYear, $periodMonth, 1);
    $endDate = (clone $startDate)->endOfMonth();

    $paymentDate = optional($payslip)->payment_date ? \Carbon\Carbon::parse($payslip->payment_date) : $endDate;

    $fmtRupiah = function ($v) {
        $v = $v === null || $v === '' ? 0 : (int) round((float) $v);
        return 'Rp' . number_format($v, 0, ',', '.');
    };

    $fmtNumber = function ($v) {
        $v = $v === null || $v === '' ? 0 : (int) round((float) $v);
        return number_format($v, 0, ',', '.');
    };

    $fmtPercent = function ($v) {
        $v = $v === null || $v === '' ? 0 : (float) $v;
        return number_format($v, 2, ',', '.') . '%';
    };

    $employee = optional($payslip)->employee;

    $hakCuti = (int) (optional($employee)->leave_balance ?? 0);
    $cutiBersama = 0;
    $cutiPersonal = 0;
    $sisaCuti = $hakCuti;

    $details = $payslip ? ($payslip->relationLoaded('details') ? $payslip->details : $payslip->details()->get()) : collect();
    if (method_exists($details, 'load')) {
        $details->load('component');
    }

    $earningDetails = $details->filter(fn($d) => optional($d->component)->type === 'earning');
    $deductionDetails = $details->filter(fn($d) => optional($d->component)->type === 'deduction');
    $taxDetails = $details->filter(fn($d) => optional($d->component)->type === 'tax');

    $basicDetail = $earningDetails->first(fn($d) => (string) optional($d->component)->name === 'Gaji Pokok');
    $basicAmount = $basicDetail ? (int) $basicDetail->amount : (int) (optional($payslip)->basic_salary ?? 0);

    $otherEarnings = $earningDetails->filter(fn($d) => (string) optional($d->component)->name !== 'Gaji Pokok');
    $lemburBonusTotal = (int) $otherEarnings->sum('amount');

    $totalBruto = (int) (optional($payslip)->total_earnings ?? $earningDetails->sum('amount'));
    $totalPotongan = (int) (optional($payslip)->total_deductions ?? $deductionDetails->sum('amount'));
    $totalPajak = (int) (optional($payslip)->tax_amount ?? $taxDetails->sum('amount'));
    $kenaPajak = max($totalBruto - $totalPotongan, 0);
    $netto = (int) (optional($payslip)->net_salary ?? max($kenaPajak - $totalPajak, 0));

    $terPercent = null;
    $category = strtoupper((string) (optional($employee)->ter_category ?? ''));
    if (in_array($category, ['A', 'B', 'C'], true)) {
        $rate = \App\Models\TerRate::query()
            ->where('category', $category)
            ->where('min_bruto', '<=', $totalBruto)
            ->where(function ($q) use ($totalBruto) {
                $q->whereNull('max_bruto')->orWhere('max_bruto', '>=', $totalBruto);
            })
            ->orderByDesc('min_bruto')
            ->first();

        if ($rate) {
            $terPercent = (float) $rate->percentage;
        }
    }

    $periodText = $startDate->format('d/m/Y') . ' s.d. ' . $endDate->format('d/m/Y');
    $paymentText = $paymentDate->format('d') . ' ' . ($monthsId[(int) $paymentDate->format('n')] ?? $paymentDate->format('F')) . ' ' . $paymentDate->format('Y');

    $logoPath = public_path('assets/logos/1 black.svg');
    $logoData = null;
    if (is_file($logoPath)) {
        $logoData = 'data:image/svg+xml;base64,' . base64_encode(file_get_contents($logoPath));
    }

    $monthKey = str_pad((string) $periodMonth, 2, '0', STR_PAD_LEFT);
    $ytdTotalBruto = 0;
    $ytdTotalPotongan = 0;
    $ytdTotalPajak = 0;
    $ytdNetto = 0;
    if ($payslip) {
        $ytdPayslips = \App\Models\Payslip::query()
            ->where('employee_id', $payslip->employee_id)
            ->whereHas('payrollPeriod', function ($q) use ($periodYear, $monthKey) {
                $q->where('year', (string) $periodYear)->where('month', '<=', $monthKey);
            })
            ->get(['total_earnings', 'total_deductions', 'tax_amount', 'net_salary']);

        $ytdTotalBruto = (int) $ytdPayslips->sum('total_earnings');
        $ytdTotalPotongan = (int) $ytdPayslips->sum('total_deductions');
        $ytdTotalPajak = (int) $ytdPayslips->sum('tax_amount');
        $ytdNetto = (int) $ytdPayslips->sum('net_salary');
    }

    $ytdKenaPajak = max($ytdTotalBruto - $ytdTotalPotongan, 0);
    $ringkasanRange = ($monthsId[$periodMonth] ?? $startDate->format('F')) . ' ' . $periodYear . ' s.d. ' . ($monthsId[$periodMonth] ?? $startDate->format('F')) . ' Tahun ' . $periodYear;
@endphp

<table class="w-100" width="100%">
    <tr>
        <td class="text-center fw-bold">RAHASIA</td>
    </tr>
</table>

<table class="w-100" width="100%" style="margin-top: 10px;">
    <tr>
        <td class="align-top text-center" style="width: 140px;">
            @if($logoData)
                <img src="{{ $logoData }}" alt="Logo" style="width: 110px;" />
            @endif
        </td>
        <td class="align-top">
            <div class="company-name">PT Altimeda Cipta Visitama</div>
            <table class="w-100 info-table" width="100%" style="margin-top: 4px;">
                <tr>
                    <td class="label">Nomor PT</td>
                    <td class="colon">:</td>
                    <td>35 159 057 646</td>
                </tr>
                <tr>
                    <td class="label">Periode</td>
                    <td class="colon">:</td>
                    <td>{{ $periodText }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Pembayaran</td>
                    <td class="colon">:</td>
                    <td>{{ $paymentText }}</td>
                </tr>
                <tr>
                    <td class="label">Posisi</td>
                    <td class="colon">:</td>
                    <td>{{ (string) (optional($employee)->position ?? '-') }}</td>
                </tr>
                <tr>
                    <td class="label">Unit Kerja</td>
                    <td class="colon">:</td>
                    <td>{{ (string) (optional($employee)->department ?? '-') }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="w-100" width="100%" style="margin-top: 14px;">
    <tr>
        <td class="align-top">
            <div class="employee-name">{{ (string) (optional($employee)->name ?? '-') }}</div>
            <div class="employee-address" style="margin-top: 2px;">
                {!! nl2br(e((string) (optional($employee)->address ?? '-'))) !!}
            </div>
        </td>
    </tr>
</table>

<table class="w-100" width="100%" style="margin-top: 10px;"><tr><td class="hr-thick"></td></tr></table>

<table class="w-100" width="100%" style="margin-top: 12px;">
    <tr>
        <td class="section-center">Detil Pembayaran</td>
    </tr>
</table>

<table class="w-100" width="100%" style="margin-top: 8px;">
    <tr>
        <td class="align-top">
            <table class="w-100 detail-table" width="100%">
                <tr>
                    <td class="col-label"></td>
                    <td class="col-item"></td>
                    <td class="col-unit fw-bold">Hari/Unit</td>
                    <td class="col-amt fw-bold">Jumlah</td>
                </tr>
                <tr>
                    <td class="col-label">Pendapatan</td>
                    <td class="col-item">Gaji Pokok</td>
                    <td class="col-unit">{{ (int) (optional($payslip)->work_days ?? 0) }}</td>
                    <td class="col-amt">{{ $fmtNumber($basicAmount) }}</td>
                </tr>
                <tr>
                    <td class="col-label"></td>
                    <td class="col-item">Lembur dan Bonus</td>
                    <td class="col-unit">0</td>
                    <td class="col-amt">{{ $fmtRupiah($lemburBonusTotal) }}</td>
                </tr>
                <tr>
                    <td class="col-label"></td>
                    <td class="col-item fw-bold">Total Penghasilan Bruto</td>
                    <td class="col-unit"></td>
                    <td class="col-amt fw-bold amt-line">{{ $fmtRupiah($totalBruto) }}</td>
                </tr>

                <tr><td colspan="4" style="height: 10px;"></td></tr>

                @php($potonganStart = true)
                @foreach($deductionDetails as $detail)
                    @php($pct = optional($detail->component)->percentage)
                    <tr>
                        <td class="col-label">{{ $potonganStart ? 'Potongan' : '' }}</td>
                        <td class="col-item">{{ (string) optional($detail->component)->name }}</td>
                        <td class="col-unit">{{ $pct !== null ? $fmtPercent($pct) : '' }}</td>
                        <td class="col-amt">{{ $fmtRupiah((int) $detail->amount) }}</td>
                    </tr>
                    @php($potonganStart = false)
                @endforeach

                @if($deductionDetails->count() === 0)
                    <tr>
                        <td class="col-label">Potongan</td>
                        <td class="col-item">&nbsp;</td>
                        <td class="col-unit">&nbsp;</td>
                        <td class="col-amt">&nbsp;</td>
                    </tr>
                @endif

                <tr>
                    <td class="col-label"></td>
                    <td class="col-item fw-bold">Total Potongan (Sebelum Pajak)</td>
                    <td class="col-unit"></td>
                    <td class="col-amt fw-bold amt-line">{{ $fmtRupiah($totalPotongan) }}</td>
                </tr>

                <tr><td colspan="4" style="height: 10px;"></td></tr>

                <tr>
                    <td class="col-label fw-bold">Total Penghasilan Kena Pajak</td>
                    <td class="col-item"></td>
                    <td class="col-unit"></td>
                    <td class="col-amt fw-bold">{{ $fmtRupiah($kenaPajak) }}</td>
                </tr>
                <tr>
                    <td class="col-label"></td>
                    <td class="col-item fw-bold">Total Pajak PPh Pasal 21</td>
                    <td class="col-unit">{{ $terPercent !== null ? number_format($terPercent, 2, ',', '.') . '%' : '' }}</td>
                    <td class="col-amt">{{ $fmtRupiah($totalPajak) }}</td>
                </tr>
                <tr>
                    <td class="col-label fw-bold">Total Penghasilan Bersih Setelah Pajak</td>
                    <td class="col-item"></td>
                    <td class="col-unit"></td>
                    <td class="col-amt fw-bold amt-line-strong">{{ $fmtRupiah($netto) }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="w-100" width="100%" style="margin-top: 12px;"><tr><td class="hr-thick"></td></tr></table>

<table class="w-100" width="100%" style="margin-top: 12px;">
    <tr>
        <td class="section-center">Informasi Rekening</td>
    </tr>
</table>

<table class="w-100 info-table" width="100%" style="margin-top: 10px;">
    <tr>
        <td class="label">Bank</td>
        <td class="colon">:</td>
        <td>{{ (string) (optional($employee)->bank_name ?? '-') }}</td>
    </tr>
    <tr>
        <td class="label">Nama Rekening</td>
        <td class="colon">:</td>
        <td>{{ (string) (optional($employee)->bank_account_name ?? '-') }}</td>
    </tr>
    <tr>
        <td class="label">Nomor Rekening</td>
        <td class="colon">:</td>
        <td>{{ (string) (optional($employee)->bank_account_number ?? '-') }}</td>
    </tr>
    <tr>
        <td class="label">Jumlah</td>
        <td class="colon"></td>
        <td class="fw-bold">{{ $fmtRupiah($netto) }}</td>
    </tr>
</table>

<table class="w-100" width="100%" style="margin-top: 12px;"><tr><td class="hr-thick"></td></tr></table>

<table class="w-100" width="100%" style="margin-top: 12px;">
    <tr>
        <td class="align-top" style="width: 72%; padding-right: 10px;">
            <table class="w-100" width="100%">
                <tr>
                    <td class="fw-bold" style="font-size: 12px;">Ringkasan Pembayaran Gaji</td>
                    <td class="text-right fw-bold" style="font-size: 10px;">{{ $ringkasanRange }}</td>
                </tr>
            </table>

            <table class="w-100" width="100%" style="margin-top: 10px;">
                <tr>
                    <td style="width: 260px;">Total Pedapatan Kotor</td>
                    <td class="text-right" style="width: 140px;">{{ $fmtRupiah($totalBruto) }}</td>
                    <td class="text-right" style="width: 140px;">{{ $fmtRupiah($ytdTotalBruto) }}</td>
                </tr>
                <tr>
                    <td>Total Potongan (Sebelum Pajak)</td>
                    <td class="text-right">{{ $fmtRupiah($totalPotongan) }}</td>
                    <td class="text-right">{{ $fmtRupiah($ytdTotalPotongan) }}</td>
                </tr>
                <tr>
                    <td>Total Penghasilan Kena Pajak</td>
                    <td class="text-right">{{ $fmtRupiah($kenaPajak) }}</td>
                    <td class="text-right">{{ $fmtRupiah($ytdKenaPajak) }}</td>
                </tr>
                <tr>
                    <td>Total Pajak</td>
                    <td class="text-right">{{ $fmtRupiah($totalPajak) }}</td>
                    <td class="text-right">{{ $fmtRupiah($ytdTotalPajak) }}</td>
                </tr>
                <tr>
                    <td class="fw-bold">Pembayaran Bersih</td>
                    <td class="text-right fw-bold">{{ $fmtRupiah($netto) }}</td>
                    <td class="text-right fw-bold">{{ $fmtRupiah($ytdNetto) }}</td>
                </tr>
            </table>
        </td>

        <td class="align-top" style="width: 28%; border-left: 2px solid #000000; padding-left: 10px;">
            <table class="w-100" width="100%">
                <tr>
                    <td class="text-center fw-bold" style="width: 60px;">Cuti</td>
                    <td class="text-center fw-bold">Saldo Cuti</td>
                </tr>
            </table>

            <table class="w-100" width="100%" style="margin-top: 10px;">
                <tr>
                    <td>Hak Cuti</td>
                    <td class="text-right">{{ $hakCuti }}</td>
                </tr>
                <tr>
                    <td>Cuti Bersama</td>
                    <td class="text-right">{{ $cutiBersama }}</td>
                </tr>
                <tr>
                    <td>Cuti Personal</td>
                    <td class="text-right">{{ $cutiPersonal }}</td>
                </tr>
                <tr>
                    <td class="fw-bold">Sisa Cuti</td>
                    <td class="text-right fw-bold amt-line">{{ $sisaCuti }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="w-100" width="100%" style="margin-top: 12px;"><tr><td class="hr-thick"></td></tr></table>

<table class="w-100" width="100%" style="margin-top: 10px;">
    <tr>
        <td class="notes">
            <div class="fw-bold" style="margin-bottom: 4px;">Catatan</div>
            <div>- Setiap 1 bulan bekerja mendapatkan 1 hari hak cuti</div>
            <div>- Setiap tahun cuti akan direset</div>
        </td>
    </tr>
</table>

</body>
</html>
