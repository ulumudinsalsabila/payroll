@extends('layouts.master')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
    <h3 class="fw-bold mb-0">Dashboard</h3>
</div>

<div class="row g-4 mb-6">
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fw-semibold">Total Karyawan</div>
                        <div class="fs-2hx fw-bold">{{ number_format((int) $totalEmployees, 0, ',', '.') }}</div>
                    </div>
                    <div class="symbol symbol-50px">
                        <div class="symbol-label bg-light-primary text-primary">
                            <i class="bi bi-people fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fw-semibold">Periode Gaji Terakhir</div>
                        <div class="fs-5 fw-bold">
                            @if ($latestPeriod)
                                @php
                                    try {
                                        $periodLabel = \Carbon\Carbon::createFromDate((int) $latestPeriod->year, (int) $latestPeriod->month, 1)->translatedFormat('F Y');
                                    } catch (\Throwable $e) {
                                        $periodLabel = str_pad((string) $latestPeriod->month, 2, '0', STR_PAD_LEFT) . '/' . (string) $latestPeriod->year;
                                    }
                                @endphp
                                {{ $periodLabel }}
                            @else
                                -
                            @endif
                        </div>
                        <div class="mt-2">
                            @if ($latestPeriod)
                                @php($status = (string) $latestPeriod->status)
                                <span class="badge {{ $status === 'published' ? 'badge-light-success' : 'badge-light-warning' }}">
                                    {{ strtoupper($status) }}
                                </span>
                            @else
                                <span class="badge badge-light-secondary">N/A</span>
                            @endif
                        </div>
                    </div>
                    <div class="symbol symbol-50px">
                        <div class="symbol-label bg-light-warning text-warning">
                            <i class="bi bi-calendar2-week fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fw-semibold">Total Pengeluaran Netto Terakhir</div>
                        <div class="fs-4 fw-bold">Rp {{ number_format((int) $totalExpense, 0, ',', '.') }}</div>
                    </div>
                    <div class="symbol symbol-50px">
                        <div class="symbol-label bg-light-success text-success">
                            <i class="bi bi-wallet2 fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fw-semibold">Aktivitas Log</div>
                        <div class="fs-2hx fw-bold">{{ number_format((int) $todayLogCount, 0, ',', '.') }}</div>
                        <div class="text-muted">Hari ini</div>
                    </div>
                    <div class="symbol symbol-50px">
                        <div class="symbol-label bg-light-info text-info">
                            <i class="bi bi-activity fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card h-100">
            <div class="card-header align-items-center">
                <h3 class="card-title mb-0">Tren Pengeluaran Gaji (6 Bulan)</h3>
            </div>
            <div class="card-body">
                <div id="expense_chart" style="height: 320px;"></div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-header align-items-center">
                <h3 class="card-title mb-0">5 Aktivitas Terakhir</h3>
            </div>
            <div class="card-body">
                @if ($recentLogs->count() === 0)
                    <div class="text-muted">Belum ada aktivitas.</div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-3">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>User</th>
                                    <th>Aksi</th>
                                    <th class="text-end">Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentLogs as $log)
                                    <tr>
                                        <td class="fw-semibold">{{ $log->user?->name ?? '-' }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $log->action }}</div>
                                            <div class="text-muted fs-7">{{ $log->module }}</div>
                                        </td>
                                        <td class="text-end text-muted">{{ $log->created_at?->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
(function () {
    const el = document.querySelector('#expense_chart');
    if (!el) return;

    const chartData = @json($chartData);
    const labels = Array.isArray(chartData.labels) ? chartData.labels : [];
    const series = Array.isArray(chartData.series) ? chartData.series : [];

    const options = {
        chart: {
            type: 'area',
            height: 320,
            toolbar: { show: false },
            fontFamily: 'inherit'
        },
        dataLabels: { enabled: false },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        series: [{
            name: 'Total Netto',
            data: series
        }],
        xaxis: {
            categories: labels,
            labels: {
                style: {
                    colors: '#A1A5B7',
                    fontSize: '12px'
                }
            },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            labels: {
                style: {
                    colors: '#A1A5B7',
                    fontSize: '12px'
                },
                formatter: function (val) {
                    return 'Rp ' + (Number(val) || 0).toLocaleString('id-ID');
                }
            }
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return 'Rp ' + (Number(val) || 0).toLocaleString('id-ID');
                }
            }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.35,
                opacityTo: 0.05,
                stops: [20, 100]
            }
        },
        colors: ['#3E97FF'],
        grid: {
            borderColor: '#EFF2F5',
            strokeDashArray: 4,
            yaxis: { lines: { show: true } }
        } 
    };

    const chart = new ApexCharts(el, options);
    chart.render();
})();
</script>
@endpush
