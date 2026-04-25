@extends('layouts.master')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold mb-0">Dashboard PT. Hasna Utama</h3>
            <p class="text-muted small mb-0">Selamat datang kembali, {{ auth()->user()->name }}</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <form action="{{ route('dashboard') }}" method="GET" id="filterForm" class="d-flex align-items-center gap-2">
                <label class="form-label fw-bold text-gray-700 mb-0">Filter Periode:</label>
                <input type="month" name="period" value="{{ $filterPeriod }}" class="form-control form-control-sm w-150px" onchange="document.getElementById('filterForm').submit()">
            </form>
            <span class="badge badge-light-primary fw-bold px-4 py-3">{{ now()->translatedFormat('d F Y') }}</span>
        </div>
    </div>

    <!-- Stats Row 1 -->
    <div class="row g-4 mb-6">
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm bg-primary bg-opacity-10">
                <div class="card-body p-5">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-primary fw-bold fs-7 text-uppercase mb-1">Total Karyawan</div>
                            <div class="fs-1 fw-bold text-gray-800">{{ number_format((int) $totalEmployees, 0, ',', '.') }}</div>
                        </div>
                        <div class="symbol symbol-50px">
                            <div class="symbol-label bg-primary text-white shadow">
                                <i class="bi bi-people fs-2"></i>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 fs-7 text-muted">
                        <span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Aktif</span> seluruh departemen
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm bg-info bg-opacity-10">
                <div class="card-body p-5">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-info fw-bold fs-7 text-uppercase mb-1">Rerata Kehadiran</div>
                            <div class="fs-1 fw-bold text-gray-800">{{ number_format((float) $avgPresence, 1, ',', '.') }}</div>
                            <div class="text-muted small">Hari / {{ $filterName }}</div>
                        </div>
                        <div class="symbol symbol-50px">
                            <div class="symbol-label bg-info text-white shadow">
                                <i class="bi bi-calendar-check fs-2"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm bg-success bg-opacity-10">
                <div class="card-body p-5">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-success fw-bold fs-7 text-uppercase mb-1">Total Pemasukan</div>
                            <div class="fs-3 fw-bold text-gray-800">Rp {{ number_format((int) $incomeTotal, 0, ',', '.') }}</div>
                            <div class="text-muted small">{{ $filterName }} (Invoicing)</div>
                        </div>
                        <div class="symbol symbol-50px">
                            <div class="symbol-label bg-success text-white shadow">
                                <i class="bi bi-arrow-up-circle fs-2"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm bg-danger bg-opacity-10">
                <div class="card-body p-5">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-danger fw-bold fs-7 text-uppercase mb-1">Total Pengeluaran</div>
                            <div class="fs-3 fw-bold text-gray-800">Rp {{ number_format((int) $expenseTotal, 0, ',', '.') }}</div>
                            <div class="text-muted small">{{ $filterName }} (Invoicing)</div>
                        </div>
                        <div class="symbol symbol-50px">
                            <div class="symbol-label bg-danger text-white shadow">
                                <i class="bi bi-arrow-down-circle fs-2"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Charts & Highlights -->
    <div class="row g-4 mb-6">
        <!-- Trend Chart -->
        <div class="col-12 col-lg-8">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-dark">Tren Pengeluaran Gaji</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Statistik tahun {{ now()->year }} (Januari - Desember)</span>
                    </h3>
                </div>
                <div class="card-body">
                    <div id="expense_chart" style="height: 300px;"></div>
                </div>
            </div>
        </div>

        <!-- Last Payroll Stats (Dark Card) -->
        <div class="col-12 col-lg-4">
            <div class="card h-100 border-0 shadow-sm bg-dark">
                <div class="card-body p-9 d-flex flex-column justify-content-between">
                    <div>
                        <div class="fs-2hx fw-bold text-white mb-2">Rp {{ number_format((int) $totalExpense, 0, ',', '.') }}</div>
                        <div class="fs-4 fw-semibold text-gray-400 mb-7">Total Gaji {{ $filterName }}</div>
                        
                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex align-items-center">
                                <span class="bullet bullet-dot bg-success me-2"></span>
                                <span class="text-gray-400 fw-semibold fs-6">Periode: {{ $latestPeriod ? $latestPeriod->month . '/' . $latestPeriod->year : '-' }}</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="bullet bullet-dot bg-warning me-2"></span>
                                <span class="text-gray-400 fw-semibold fs-6">Status: {{ strtoupper($latestPeriod->status ?? 'N/A') }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="separator separator-dashed border-gray-600 my-8"></div>
                        <a href="{{ route('payroll-periods.index') }}" class="btn btn-primary w-100">Kelola Payroll</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Triple Cards Row -->
    <div class="row g-4">
        <!-- Dept Distribution -->
        <div class="col-12 col-lg-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-dark">Distribusi Departemen</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Persentase jumlah karyawan</span>
                    </h3>
                </div>
                <div class="card-body d-flex flex-column justify-content-center">
                    <div id="dept_donut_chart" style="height: 300px;"></div>
                </div>
            </div>  
        </div>

        <!-- Recent Invoices -->
        <div class="col-12 col-lg-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-dark fs-5">Transaksi Terakhir</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">5 data invoice terbaru</span>
                    </h3>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-7 gy-3">
                            <thead>
                                <tr class="text-start text-muted fw-bold text-uppercase gs-0">
                                    <th>Invoice</th>
                                    <th>Tipe</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600">
                                @forelse($recentInvoices as $inv)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $inv->invoice_number }}</div>
                                            <div class="text-muted small">{{ \Carbon\Carbon::parse($inv->issue_date)->format('d/m/Y') }}</div>
                                        </td>
                                        <td>
                                            <span class="badge badge-light-{{ $inv->type === 'pemasukan' ? 'success' : 'danger' }} fw-bold fs-9">
                                                {{ strtoupper($inv->type) }}
                                            </span>
                                        </td>
                                        <td class="fw-bold">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-5">Belum ada transaksi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Presence -->
        <div class="col-12 col-lg-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-dark fs-5">Kehadiran Tertinggi</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Top 5 karyawan {{ $filterName }}</span>
                    </h3>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-7 gy-3">
                            <thead>
                                <tr class="text-start text-muted fw-bold text-uppercase gs-0">
                                    <th>Nama</th>
                                    <th>Hadir</th>
                                    <th class="text-end">%</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600">
                                @forelse($topPresence as $att)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ optional($att->employee)->name ?? '-' }}</div>
                                        </td>
                                        <td>{{ $att->present_days }} Hari</td>
                                        <td class="text-end fw-bold text-primary">{{ number_format(($att->present_days / 22) * 100, 1) }}%</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-5">Data belum tersedia.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.41.0"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Main Expense Chart
            (function() {
                const el = document.querySelector('#expense_chart');
                if (!el) return;

                const chartData = @json($chartData ?? ['labels' => [], 'series' => []]);
                const labels = Array.isArray(chartData.labels) ? chartData.labels : [];
                const series = Array.isArray(chartData.series) ? chartData.series : [];

                if (series.length === 0) {
                    el.innerHTML = '<div class="d-flex flex-column flex-center h-350px"><span class="text-muted">Belum ada data periode yang dipublish.</span></div>';
                    return;
                }

                const options = {
                    chart: {
                        type: 'area',
                        height: 350,
                        toolbar: { show: false },
                        fontFamily: 'Inter, Helvetica, "sans-serif"',
                        animations: { enabled: true }
                    },
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 3 },
                    series: [{ name: 'Total Netto', data: series }],
                    xaxis: {
                        categories: labels,
                        labels: { style: { colors: '#A1A5B7', fontSize: '12px' } },
                        axisBorder: { show: false },
                        axisTicks: { show: false }
                    },
                    yaxis: {
                        labels: {
                            style: { colors: '#A1A5B7', fontSize: '12px' },
                            formatter: (val) => 'Rp ' + (Number(val) || 0).toLocaleString('id-ID')
                        }
                    },
                    tooltip: {
                        y: { formatter: (val) => 'Rp ' + (Number(val) || 0).toLocaleString('id-ID') }
                    },
                    fill: {
                        type: 'gradient',
                        gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0, stops: [20, 100] }
                    },
                    colors: ['#3E97FF'],
                    grid: { borderColor: '#EFF2F5', strokeDashArray: 4 }
                };

                new ApexCharts(el, options).render();
            })();

            // Dept Donut Chart
            (function() {
                const el = document.querySelector('#dept_donut_chart');
                if (!el) return;

                const deptData = @json($deptDistribution ?? []);
                
                if (!deptData || deptData.length === 0) {
                    el.innerHTML = '<div class="d-flex flex-column flex-center h-300px"><span class="text-muted">Data karyawan belum tersedia.</span></div>';
                    return;
                }

                const labels = deptData.map(d => d.department || 'Lainnya');
                const series = deptData.map(d => Number(d.count));

                const options = {
                    chart: { 
                        type: 'donut', 
                        height: 300, 
                        fontFamily: 'Inter, Helvetica, "sans-serif"',
                        animations: { enabled: true }
                    },
                    series: series,
                    labels: labels,
                    legend: { position: 'bottom' },
                    dataLabels: { enabled: true, formatter: (val) => val.toFixed(1) + "%" },
                    colors: ['#3E97FF', '#F1416C', '#50CD89', '#7239EA', '#FFC700'],
                    stroke: { width: 0 },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '70%',
                                labels: {
                                    show: true,
                                    name: { show: true },
                                    value: { show: true, formatter: (val) => val + ' Orang' },
                                    total: { show: true, label: 'Total', formatter: (w) => w.globals.seriesTotals.reduce((a, b) => a + b, 0) + ' Orang' }
                                }
                            }
                        }
                    }
                };

                new ApexCharts(el, options).render();
            })();
        });
    </script>
@endpush
