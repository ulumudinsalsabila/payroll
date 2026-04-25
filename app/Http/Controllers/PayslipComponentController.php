<?php

namespace App\Http\Controllers;

use App\Models\PayslipComponent;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class PayslipComponentController extends Controller
{
    public function index()
    {
        return view('payslip-components.index');
    }

    public function data(Request $request)
    {
        $query = PayslipComponent::query()->select([
            'id',
            'name',
            'type',
            'is_active',
        ]);

        return DataTables::eloquent($query)
            ->editColumn('is_active', function ($row) {
                return $row->is_active ? 1 : 0;
            })
            ->filterColumn('type', function ($query, $keyword) {
                if ($keyword === null || $keyword === '') {
                    return;
                }
                $query->where('type', $keyword);
            })
            ->filterColumn('is_active', function ($query, $keyword) {
                if ($keyword === null || $keyword === '') {
                    return;
                }

                if ($keyword === '1' || $keyword === 1 || $keyword === true) {
                    $query->where('is_active', true);
                    return;
                }
                if ($keyword === '0' || $keyword === 0 || $keyword === false) {
                    $query->where('is_active', false);
                    return;
                }
            })
            ->toJson();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(['earning','deduction','tax'])],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $component = PayslipComponent::create($data);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'CREATE',
            'module' => 'PAYSLIP_COMPONENT',
            'target_id' => $component->id,
            'description' => 'Menambahkan komponen gaji',
            'old_values' => null,
            'new_values' => $component->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('payslip-components.index')->with('success', 'Komponen gaji berhasil ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $component = PayslipComponent::findOrFail($id);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(['earning','deduction','tax'])],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $before = $component->toArray();
        $component->update($data);
        $component->refresh();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'UPDATE',
            'module' => 'PAYSLIP_COMPONENT',
            'target_id' => $component->id,
            'description' => 'Memperbarui komponen gaji',
            'old_values' => $before,
            'new_values' => $component->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('payslip-components.index')->with('success', 'Komponen gaji berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $component = PayslipComponent::findOrFail($id);
        $before = $component->toArray();
        $component->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'DELETE',
            'module' => 'PAYSLIP_COMPONENT',
            'target_id' => $id,
            'description' => 'Menghapus komponen gaji',
            'old_values' => $before,
            'new_values' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('payslip-components.index')->with('success', 'Komponen gaji berhasil dihapus.');
    }

    public function show(string $id)
    {
        return redirect()->route('payslip-components.index');
    }
}
