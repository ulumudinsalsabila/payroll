<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\Invoice;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $totalEmployees = Employee::count();

        $latestPeriod = PayrollPeriod::latest()->first();

        $totalExpense = 0;
        if ($latestPeriod) {
            $totalExpense = (int) $latestPeriod->payslips()->sum('net_salary');
        }

        $recentLogs = ActivityLog::with('user')->latest()->limit(5)->get();

        $todayLogCount = ActivityLog::query()
            ->whereDate('created_at', Carbon::today())
            ->count();

        // Handle Filter
        $filterPeriod = $request->query('period', Carbon::now()->format('Y-m'));
        try {
            $carbonFilter = Carbon::createFromFormat('Y-m', $filterPeriod);
            $filterMonth = $carbonFilter->month;
            $filterYear = $carbonFilter->year;
        } catch (\Throwable $e) {
            $carbonFilter = Carbon::now();
            $filterMonth = $carbonFilter->month;
            $filterYear = $carbonFilter->year;
            $filterPeriod = $carbonFilter->format('Y-m');
        }

        $currentYear = Carbon::now()->year;
        $publishedPeriods = PayrollPeriod::query()
            ->where('status', 'published')
            ->where('year', $currentYear)
            ->withSum('payslips', 'net_salary')
            ->get()
            ->keyBy('month');

        $labels = [];
        $series = [];

        for ($m = 1; $m <= 12; $m++) {
            $monthNum = str_pad($m, 2, '0', STR_PAD_LEFT);
            $monthName = Carbon::createFromDate($currentYear, $m, 1)->translatedFormat('M');
            $labels[] = $monthName;

            $period = $publishedPeriods->get($monthNum);
            $series[] = $period ? (int) ($period->payslips_sum_net_salary ?? 0) : 0;
        }

        $chartData = [
            'labels' => $labels,
            'series' => $series,
        ];

        // Filtered Invoice Stats
        $incomeTotal = Invoice::where('type', 'pemasukan')
            ->whereMonth('issue_date', $filterMonth)
            ->whereYear('issue_date', $filterYear)
            ->sum('total_amount');

        $expenseTotal = Invoice::where('type', 'pengeluaran')
            ->whereMonth('issue_date', $filterMonth)
            ->whereYear('issue_date', $filterYear)
            ->sum('total_amount');

        // Filtered Attendance Stats (Avg presence)
        $avgPresence = Attendance::where('period_month', $filterPeriod)
            ->avg('present_days') ?? 0;

        // Recent Invoices
        $recentInvoices = Invoice::latest()->limit(5)->get();

        // Top Employees by Presence (Current Month)
        $topPresence = Attendance::where('period_month', $filterPeriod)
            ->orderByDesc('present_days')
            ->limit(5)
            ->get();

        // Department Distribution
        $deptDistribution = Employee::query()
            ->select('department', DB::raw('count(*) as count'))
            ->groupBy('department')
            ->get();

        return view('dashboard.index', compact(
            'totalEmployees',
            'latestPeriod',
            'totalExpense',
            'recentLogs',
            'todayLogCount',
            'chartData',
            'incomeTotal',
            'expenseTotal',
            'avgPresence',
            'deptDistribution',
            'filterPeriod',
            'recentInvoices',
            'topPresence'
        ));
    }
}
