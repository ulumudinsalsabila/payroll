<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
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

        $publishedPeriods = PayrollPeriod::query()
            ->where('status', 'published')
            ->orderByDesc('published_at')
            ->limit(6)
            ->withSum('payslips', 'net_salary')
            ->get()
            ->reverse()
            ->values();

        $labels = $publishedPeriods->map(function ($p) {
            try {
                return Carbon::createFromDate((int) $p->year, (int) $p->month, 1)->translatedFormat('M Y');
            } catch (\Throwable $e) {
                return trim((string) ($p->month ?? '')) . '/' . trim((string) ($p->year ?? ''));
            }
        })->values();

        $series = $publishedPeriods->map(function ($p) {
            return (int) ($p->payslips_sum_net_salary ?? 0);
        })->values();

        $chartData = [
            'labels' => $labels,
            'series' => $series,
        ];

        return view('dashboard.index', compact(
            'totalEmployees',
            'latestPeriod',
            'totalExpense',
            'recentLogs',
            'todayLogCount',
            'chartData'
        ));
    }
}
