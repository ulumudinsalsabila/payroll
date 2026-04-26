<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Payslip</title>
    <style>
        @page {
            margin: 20px 30px;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #000;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        td {
            padding: 2px;
            vertical-align: top;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        
        .header-table td { padding: 0 5px; }
        .header-title {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            text-decoration: underline;
        }
        
        .details-table td { padding: 2px 5px; }
        .details-table .col-label { width: 120px; }
        .details-table .col-sep { width: 10px; }
        .details-table .col-curr { width: 30px; }
        .details-table .col-val { text-align: right; }
        
        .sub-item td:first-child { padding-left: 15px; }
        
        .line-top td { border-top: 1px solid #000; padding-top: 5px; }
        .line-bottom td { border-bottom: 1px solid #000; padding-bottom: 5px; }
        .line-thick-top td { border-top: 2px solid #000; padding-top: 5px; }
    </style>
</head>
<body>
    @php
        $payslip = $payslip ?? null;
        $employee = optional($payslip)->employee;
        $period = optional($payslip)->payrollPeriod;
        
        $monthsId = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];
        
        $periodMonth = (int) (optional($period)->month ?? date('n'));
        $periodYear = (int) (optional($period)->year ?? date('Y'));
        $periodStr = ($monthsId[$periodMonth] ?? '') . ' ' . $periodYear;
        
        $defaultWorkDays = $period->default_work_days ?? 25;
        $workDays = $payslip->work_days ?? 0;
        
        $attendance = \App\Models\Attendance::where('employee_id', $payslip->employee_id)
            ->where('period_month', $periodYear . '-' . str_pad($periodMonth, 2, '0', STR_PAD_LEFT))
            ->first();
        $jamLembur = $attendance ? $attendance->overtime_hours : '0';
        
        $fmtRupiah = function ($v) {
            return number_format((int)$v, 0, ',', '.');
        };
        
        $details = $payslip ? $payslip->details : collect();
        if (method_exists($details, 'load')) {
            $details->load('component');
        }
        
        $earningDetails = $details->filter(fn($d) => optional($d->component)->type === 'earning');
        $deductionDetails = $details->filter(fn($d) => optional($d->component)->type === 'deduction');
        $taxDetails = $details->filter(fn($d) => optional($d->component)->type === 'tax');
        
        $basicDetail = $earningDetails->first(fn($d) => (string) optional($d->component)->name === 'Gaji Pokok');
        $basicAmount = $basicDetail ? (int) $basicDetail->amount : (int) (optional($payslip)->basic_salary ?? 0);
        
        $otherEarningLineItems = $earningDetails
            ->filter(fn($d) => (string) optional($d->component)->name !== 'Gaji Pokok');
            
        $deductionLineItems = $deductionDetails;
            
        $taxLineItems = $taxDetails;
            
        $potonganAbsen = 0;
        if ($workDays < $defaultWorkDays && $workDays > 0) {
            $potonganPerHari = $basicAmount > 0 ? round($basicAmount / $defaultWorkDays) : 0;
            $potonganAbsen = ($defaultWorkDays - $workDays) * $potonganPerHari;
        }
        
        $totalPendapatan = $basicAmount + $otherEarningLineItems->sum('amount');
        $totalPotongan = $deductionLineItems->sum('amount') + $taxLineItems->sum('amount') + $potonganAbsen;
        $pendapatanBersih = max($totalPendapatan - $totalPotongan, 0);
        
        $logoPath = public_path('assets/logos/logo.png');
        $logoData = null;
        if (is_file($logoPath)) {
            $logoData = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }
    @endphp

    <table style="width: 100%;">
        <tr>
            <td style="width: 33%; vertical-align: top;">
                @if ($logoData)
                    <img src="{{ $logoData }}" alt="Logo" style="width: 200px;" />
                @else
                    <h3 style="margin: 0 0 5px 0; font-style: italic;">CV .HASNA UTAMA</h3>
                @endif
            </td>
            <td style="width: 34%; vertical-align: top; text-align: center;">
                <div class="header-title" style="margin-top: 22px;">SLIP GAJI KARYAWAN</div>
            </td>
            <td style="width: 33%; vertical-align: top;">
            </td>
        </tr>
    </table>
    <table style="width: 100%; margin-top: 10px;">
        <tr>
            <td style="width: 33%; vertical-align: top;">
                <table class="header-table" style="font-size: 11px;">
                    <tr><td style="width: 70px; padding: 2px 0;">Departemet</td><td style="width: 5px; padding: 2px 0;">:</td><td style="padding: 2px 0;">{{ strtoupper($employee->department ?? '-') }}</td></tr>
                    <tr><td style="padding: 2px 0;">NIK / Nama</td><td style="padding: 2px 0;">:</td><td style="padding: 2px 0;">{{ str_pad($employee->employee_code ?? '', 4, '0', STR_PAD_LEFT) }} / {{ strtoupper($employee->name ?? '-') }}</td></tr>
                    <tr><td style="padding: 2px 0;">Bulan</td><td style="padding: 2px 0;">:</td><td style="padding: 2px 0;">{{ $periodStr }}</td></tr>
                </table>
            </td>
            <td style="width: 33%; vertical-align: top;">
                <table class="header-table" style="font-size: 11px; margin-left: auto;">
                    <tr><td style="padding: 2px 0;">Jml Kerja Efektif</td><td style="width: 5px; padding: 2px 0;">:</td><td class="text-right" style="width: 30px; padding: 2px 0;">{{ $defaultWorkDays }}</td><td style="padding: 2px 0;">Hari</td></tr>
                    <tr><td style="padding: 2px 0;">Jml Hari Kerja</td><td style="padding: 2px 0;">:</td><td class="text-right" style="padding: 2px 0;">{{ number_format($workDays, 1) }}</td><td style="padding: 2px 0;">Hari</td></tr>
                    <tr><td style="padding: 2px 0;">Jml Jam Lembur</td><td style="padding: 2px 0;">:</td><td class="text-right" style="padding: 2px 0;">{{ $jamLembur }}</td><td style="padding: 2px 0;">Jam</td></tr>
                </table>
            </td>
        </tr>
    </table>
    
    
    <div style="height: 15px;"></div>
    
    <table>
        <tr>
            <td style="width: 50%; padding-right: 15px;">
                <table class="details-table">
                    <tr>
                        <td class="col-label">Gaji Pokok</td><td class="col-sep">:</td><td class="col-curr">Rp.</td><td class="col-val">{{ $fmtRupiah($basicAmount) }}</td>
                    </tr>
                    <tr>
                        <td class="col-label">Tunjangan</td><td class="col-sep"></td><td class="col-curr"></td><td class="col-val"></td>
                    </tr>
                    @foreach($otherEarningLineItems as $item)
                    @if(strpos(strtolower(optional($item->component)->name), 'lembur') === false)
                    <tr class="sub-item">
                        <td class="col-label">{{ optional($item->component)->name }}</td><td class="col-sep">:</td><td class="col-curr">Rp.</td><td class="col-val">{{ $fmtRupiah($item->amount) }}</td>
                    </tr>
                    @endif
                    @endforeach
                    
                    @php
                        $lemburItem = $otherEarningLineItems->first(fn($d) => strpos(strtolower(optional($d->component)->name), 'lembur') !== false);
                    @endphp
                    @if($lemburItem)
                    <tr>
                        <td class="col-label">{{ optional($lemburItem->component)->name }}</td><td class="col-sep">:</td><td class="col-curr">Rp.</td><td class="col-val">{{ $fmtRupiah($lemburItem->amount) }}</td>
                    </tr>
                    @endif
                    
                    <tr><td colspan="4" style="height: 5px;"></td></tr>
                    <tr class="line-top">
                        <td class="col-label">Pendapatan Bruto</td><td class="col-sep">:</td><td class="col-curr">Rp.</td><td class="col-val">{{ $fmtRupiah($totalPendapatan) }}</td>
                    </tr>
                </table>
            </td>
            
            <td style="width: 50%; padding-left: 15px;">
                <table class="details-table">
                    <tr>
                        <td class="col-label">Potongan</td><td class="col-sep"></td><td class="col-curr"></td><td class="col-val"></td>
                    </tr>
                    @if($potonganAbsen > 0)
                    <tr class="sub-item">
                        <td class="col-label">Tidak Masuk / Absen</td><td class="col-sep">:</td><td class="col-curr">Rp.</td><td class="col-val">{{ $fmtRupiah($potonganAbsen) }}</td>
                    </tr>
                    @endif
                    @foreach($deductionLineItems as $item)
                    <tr class="sub-item">
                        <td class="col-label">{{ optional($item->component)->name }}</td><td class="col-sep">:</td><td class="col-curr">Rp.</td><td class="col-val">{{ $fmtRupiah($item->amount) }}</td>
                    </tr>
                    @endforeach
                    @foreach($taxLineItems as $item)
                    <tr class="sub-item">
                        <td class="col-label">{{ optional($item->component)->name }}</td><td class="col-sep">:</td><td class="col-curr">Rp.</td><td class="col-val">{{ $fmtRupiah($item->amount) }}</td>
                    </tr>
                    @endforeach
                    
                    <tr><td colspan="4" style="height: 5px;"></td></tr>
                    <tr class="line-top line-bottom">
                        <td class="col-label">Total Potongan</td><td class="col-sep">:</td><td class="col-curr">Rp.</td><td class="col-val">{{ $fmtRupiah($totalPotongan) }}</td>
                    </tr>
                    
                    <tr><td colspan="4" style="height: 15px;"></td></tr>
                    
                    <tr>
                        <td class="col-label">Pendapatan</td><td class="col-sep">:</td><td class="col-curr">Rp.</td><td class="col-val fw-bold">{{ $fmtRupiah($pendapatanBersih) }}</td>
                    </tr>
                    <tr>
                        <td class="col-label">Rounded</td><td class="col-sep">:</td><td class="col-curr">Rp.</td><td class="col-val">0</td>
                    </tr>
                    
                    <tr><td colspan="4" style="height: 5px;"></td></tr>
                    <tr class="line-thick-top">
                        <td class="col-label fw-bold">Pendapatan Bersih</td><td class="col-sep">:</td><td class="col-curr fw-bold">Rp.</td><td class="col-val fw-bold">{{ $fmtRupiah($pendapatanBersih) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    
    <div style="height: 20px;"></div>
    
    <table class="details-table" style="background-color: #e4e4e4; padding: 5px;">
        <tr>
            <td style="width: 80px;" class="fw-bold">Keterangan</td>
            <td style="width: 10px;">:</td>
            <td></td>
        </tr>
    </table>
</body>
</html>
