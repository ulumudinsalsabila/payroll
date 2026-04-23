<?php

namespace App\Http\Controllers;

use App\Models\TerRate;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class TerRateController extends Controller
{
    public function index()
    {
        return view('ter-rates.index');
    }

    public function data(Request $request)
    {
        $query = TerRate::query()
            ->select(['id', 'category', 'min_bruto', 'max_bruto', 'percentage'])
            ->orderBy('category')
            ->orderBy('min_bruto');

        return DataTables::eloquent($query)
            ->filterColumn('category', function ($query, $keyword) {
                if ($keyword === null || $keyword === '') {
                    return;
                }
                $query->where('category', $keyword);
            })
            ->toJson();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category' => ['required', 'string', Rule::in(['A','B','C'])],
            'min_bruto' => ['required', 'numeric', 'min:0'],
            'max_bruto' => ['nullable', 'numeric'],
            'percentage' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        if (isset($data['max_bruto']) && $data['max_bruto'] !== null) {
            if ($data['max_bruto'] <= $data['min_bruto']) {
                return back()->withInput()->withErrors(['max_bruto' => 'Batas atas harus lebih besar dari batas bawah.']);
            }
        }

        $rate = TerRate::create($data);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'CREATE',
            'module' => 'TER_RATE',
            'target_id' => $rate->id,
            'description' => 'Menambahkan tarif TER',
            'old_values' => null,
            'new_values' => $rate->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('ter-rates.index')->with('success', 'Tarif TER berhasil ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $rate = TerRate::findOrFail($id);
        $data = $request->validate([
            'category' => ['required', 'string', Rule::in(['A','B','C'])],
            'min_bruto' => ['required', 'numeric', 'min:0'],
            'max_bruto' => ['nullable', 'numeric'],
            'percentage' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        if (isset($data['max_bruto']) && $data['max_bruto'] !== null) {
            if ($data['max_bruto'] <= $data['min_bruto']) {
                return back()->withInput()->withErrors(['max_bruto' => 'Batas atas harus lebih besar dari batas bawah.']);
            }
        }

        $before = $rate->toArray();
        $rate->update($data);
        $rate->refresh();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'UPDATE',
            'module' => 'TER_RATE',
            'target_id' => $rate->id,
            'description' => 'Memperbarui tarif TER',
            'old_values' => $before,
            'new_values' => $rate->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('ter-rates.index')->with('success', 'Tarif TER berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $rate = TerRate::findOrFail($id);
        $before = $rate->toArray();
        $rate->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'DELETE',
            'module' => 'TER_RATE',
            'target_id' => $id,
            'description' => 'Menghapus tarif TER',
            'old_values' => $before,
            'new_values' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('ter-rates.index')->with('success', 'Tarif TER berhasil dihapus.');
    }

    public function show(string $id)
    {
        return redirect()->route('ter-rates.index');
    }
}
