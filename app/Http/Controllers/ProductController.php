<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('products.index');
    }

    /**
     * Get data for DataTables.
     */
    public function data(Request $request)
    {
        $query = Product::select(['id', 'code', 'name', 'price', 'description', 'is_active', 'created_at']);
        
        return datatables()->eloquent($query)
            ->addColumn('actions', function($product) {
                return '';
            })
            ->editColumn('price', function($product) {
                return 'Rp ' . number_format($product->price, 0, ',', '.');
            })
            ->editColumn('is_active', function($product) {
                return $product->is_active 
                    ? '<span class="badge bg-light-success text-success">Aktif</span>'
                    : '<span class="badge bg-light-danger text-danger">Non-Aktif</span>';
            })
            ->rawColumns(['actions', 'is_active'])
            ->toJson();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('products', 'code')],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        Product::create($data);

        return redirect()->route('products.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);
        
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('products', 'code')->ignore($product->id)],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        $product->update($data);

        return redirect()->route('products.index')->with('success', 'Barang berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Barang berhasil dihapus.');
    }
}
