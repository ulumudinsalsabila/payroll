<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ActivityLogController extends Controller
{
    public function index()
    {
        return view('activity-logs.index');
    }

    public function data(Request $request)
    {
        $baseQuery = ActivityLog::query()
            ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
            ->select('activity_logs.*', 'users.name as user_name');

        return DataTables::eloquent($baseQuery)
            ->addColumn('user', function ($row) {
                return (string) ($row->user_name ?? '-');
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at?->format('d M Y H:i') ?? '-';
            })
            ->addColumn('created_at_ts', function ($row) {
                return $row->created_at?->timestamp ?? 0;
            })
            ->addColumn('description', function ($row) {
                return Str::limit((string) $row->description, 80);
            })
            ->addColumn('description_full', function ($row) {
                return (string) $row->description;
            })
            ->addColumn('ip_address', function ($row) {
                return (string) ($row->ip_address ?? '-');
            })
            ->addColumn('user_agent', function ($row) {
                return (string) ($row->user_agent ?? '');
            })
            ->addColumn('old_values', function ($row) {
                return $row->old_values;
            })
            ->addColumn('new_values', function ($row) {
                return $row->new_values;
            })
            ->orderColumn('user', function ($query, $order) {
                $query->orderBy('users.name', $order);
            })
            ->filterColumn('user', function ($query, $keyword) {
                $query->where('users.name', 'like', '%' . $keyword . '%');
            })
            ->toJson();
    }
}
