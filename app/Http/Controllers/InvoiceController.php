<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.invoices.index');
    }

    public function exportExcel()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\InvoicesExport, 'Laporan_Invoice_' . date('Ymd_His') . '.xlsx');
    }

    public function data()
    {
        $query = Invoice::query()->latest();

        return datatables()->of($query)
            ->editColumn('issue_date', function ($item) {
                return $item->issue_date ? $item->issue_date->format('d/m/Y') : '-';
            })
            ->editColumn('total_amount', function ($item) {
                return 'Rp ' . number_format($item->total_amount, 0, ',', '.');
            })
            ->editColumn('type', function ($invoice) {
                $badgeClass = $invoice->type == 'pemasukan' ? 'badge-light-success' : 'badge-light-danger';
                return '<span class="badge ' . $badgeClass . '">' . ucfirst($invoice->type) . '</span>';
            })
            ->editColumn('status', function ($invoice) {
                $badges = [
                    'draft' => 'badge-light-secondary',
                    'sent' => 'badge-light-primary',
                    'paid' => 'badge-light-success',
                    'overdue' => 'badge-light-warning',
                    'cancelled' => 'badge-light-danger',
                ];
                $class = $badges[$invoice->status] ?? 'badge-light';
                return '<span class="badge ' . $class . '">' . ucfirst($invoice->status) . '</span>';
            })
            ->addColumn('action', function ($item) {
                return '
                    <a href="'.route('invoices.print', $item->id).'" class="btn btn-sm btn-icon btn-light-success me-2" target="_blank" title="Cetak/Print">
                        <i class="bi bi-printer"></i>
                    </a>
                    <a href="'.route('invoices.show', $item->id).'" class="btn btn-sm btn-icon btn-light-info me-2" title="Detail">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="'.route('invoices.edit', $item->id).'" class="btn btn-sm btn-icon btn-light-warning me-2" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-icon btn-light-danger btnDeleteInvoice" data-id="'.$item->id.'" data-name="'.$item->invoice_number.'" title="Hapus">
                        <i class="bi bi-trash"></i>
                    </button>
                ';
            })
            ->rawColumns(['status', 'type', 'action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::where('is_active', true)->get();
        return view('admin.invoices.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'type' => 'required|in:pemasukan,pengeluaran',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'status' => 'required|in:draft,sent,paid,overdue,cancelled',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $subtotal = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $total = $item['quantity'] * $item['unit_price'];
                $subtotal += $total;

                $itemsData[] = [
                    'product_id' => $item['product_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $total,
                ];
            }

            $taxAmount = $request->tax_amount ?? 0;
            $discountAmount = $request->discount_amount ?? 0;
            $totalAmount = $subtotal + $taxAmount - $discountAmount;

            // Generate invoice number
            $latestInvoice = Invoice::orderBy('id', 'desc')->first();
            $nextId = $latestInvoice ? $latestInvoice->id + 1 : 1;
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'type' => $request->type,
                'customer_name' => $request->customer_name,
                'issue_date' => $request->issue_date,
                'due_date' => $request->due_date,
                'status' => $request->status,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'notes' => $request->notes,
            ]);

            foreach ($itemsData as $itemData) {
                $invoice->items()->create($itemData);
            }

            DB::commit();

            return redirect()->route('invoices.index')->with('success', 'Invoice berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan invoice: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $invoice = Invoice::with('items.product')->findOrFail($id);
        return view('admin.invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $invoice = Invoice::with('items')->findOrFail($id);
        $products = Product::where('is_active', true)->get();
        return view('admin.invoices.edit', compact('invoice', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'type' => 'required|in:pemasukan,pengeluaran',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'status' => 'required|in:draft,sent,paid,overdue,cancelled',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();
            
            $invoice = Invoice::findOrFail($id);

            $subtotal = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $total = $item['quantity'] * $item['unit_price'];
                $subtotal += $total;

                $itemsData[] = [
                    'product_id' => $item['product_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $total,
                ];
            }

            $taxAmount = $request->tax_amount ?? 0;
            $discountAmount = $request->discount_amount ?? 0;
            $totalAmount = $subtotal + $taxAmount - $discountAmount;

            $invoice->update([
                'customer_name' => $request->customer_name,
                'type' => $request->type,
                'issue_date' => $request->issue_date,
                'due_date' => $request->due_date,
                'status' => $request->status,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'notes' => $request->notes,
            ]);

            // Delete existing items and recreate
            $invoice->items()->delete();
            
            foreach ($itemsData as $itemData) {
                $invoice->items()->create($itemData);
            }

            DB::commit();

            return redirect()->route('invoices.index')->with('success', 'Invoice berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui invoice: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource for printing.
     */
    public function print(string $id)
    {
        $invoice = Invoice::with('items.product')->findOrFail($id);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.invoices.print', compact('invoice'))
            ->setPaper('A4', 'portrait');
            
        return $pdf->stream('Invoice-' . $invoice->invoice_number . '.pdf');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $invoice = Invoice::findOrFail($id);
            $invoice->delete();
            return redirect()->route('invoices.index')->with('success', 'Invoice berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus invoice: ' . $e->getMessage());
        }
    }
}
