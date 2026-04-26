<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Validation\Rule;
use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;

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
                return '
                    <div class="d-flex justify-content-center border-0">
                        <button type="button" class="btn btn-sm btn-icon btn-light-warning me-2 btnEditProduct" data-id="'.$product->id.'" data-code="'.$product->code.'" data-name="'.$product->name.'" data-price="'.$product->price.'" data-description="'.$product->description.'" data-is_active="'.$product->is_active.'" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-icon btn-light-danger btnDeleteProduct" data-id="'.$product->id.'" data-name="'.$product->name.'" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->editColumn('price', function($product) {
                return 'Rp ' . number_format($product->price, 0, ',', '.');
            })
            ->editColumn('is_active', function($product) {
                return $product->is_active 
                    ? '<span class="badge bg-light-success text-success">Aktif</span>'
                    : '<span class="badge bg-light-danger text-danger">Non-Aktif</span>';
            })
            ->addColumn('is_active_raw', function($product) {
                return $product->is_active ? 1 : 0;
            })
            ->rawColumns(['actions', 'is_active'])
            ->toJson();
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

    /**
     * Import products from Excel.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            Excel::import(new ProductsImport, $request->file('file'));
            return redirect()->route('products.index')->with('success', 'Master data barang berhasil diimport.');
        } catch (\Exception $e) {
            return redirect()->route('products.index')->with('error', 'Gagal mengimport data: ' . $e->getMessage());
        }
    }

    /**
     * Download import template.
     */
    public function downloadTemplate()
    {
        $header = ['kode', 'nama', 'harga', 'deskripsi'];
        $data = [
            ['BRG001', 'Barang Contoh 1', 50000, 'Deskripsi barang 1'],
            ['BRG002', 'Barang Contoh 2', 75000, 'Deskripsi barang 2'],
        ];

        return Excel::download(new class($header, $data) implements \Maatwebsite\Excel\Concerns\FromArray {
            private $header;
            private $data;
            public function __construct($header, $data) { $this->header = $header; $this->data = $data; }
            public function array(): array { return array_merge([$this->header], $this->data); }
        }, 'template_import_barang.xlsx');
    }
}
