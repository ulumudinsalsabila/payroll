@extends('layouts.master')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
    <h3 class="fw-bold mb-0">Edit Invoice {{ $invoice->invoice_number }}</h3>
    <a href="{{ route('invoices.index') }}" class="btn btn-light">
        <i class="bi bi-arrow-left me-2"></i>Kembali
    </a>
</div>

<form action="{{ route('invoices.update', $invoice->id) }}" method="POST" id="invoiceForm">
    @csrf
    @method('PUT')

    <div class="row g-5">
        <div class="col-lg-8">
            <div class="card card-flush mb-5">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Detail Item Invoice</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="itemsTable">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th class="min-w-200px">Deskripsi / Produk</th>
                                    <th class="w-100px">Kuantitas</th>
                                    <th class="min-w-150px">Harga Satuan</th>
                                    <th class="min-w-150px">Total</th>
                                    <th class="w-50px"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                @foreach($invoice->items as $index => $item)
                                <tr class="item-row">
                                    <td>
                                        <select name="items[{{ $index }}][product_id]" class="form-select form-select-sm product-select" data-placeholder="Pilih Produk (Opsional)">
                                            <option value=""></option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-name="{{ $product->name }}" {{ $item->product_id == $product->id ? 'selected' : '' }}>{{ $product->code }} - {{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="items[{{ $index }}][description]" class="form-control form-control-sm mt-2 description-input" value="{{ $item->description }}" placeholder="Atau ketik deskripsi item" required>
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][quantity]" class="form-control form-control-sm quantity-input" value="{{ (float)$item->quantity }}" min="0.01" step="0.01" required>
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][unit_price]" class="form-control form-control-sm price-input" value="{{ (float)$item->unit_price }}" min="0" step="0.01" required>
                                    </td>
                                    <td class="text-end fw-bold">
                                        Rp <span class="row-total">0</span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-icon btn-light-danger btn-remove-item"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5">
                                        <button type="button" class="btn btn-light-primary btn-sm" id="btnAddItem">
                                            <i class="bi bi-plus"></i> Tambah Item
                                        </button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="card card-flush mb-5">
                <div class="card-body">
                    <div class="mb-5">
                        <label class="form-label">Catatan Invoice</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Tambahkan catatan untuk pelanggan">{{ old('notes', $invoice->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-flush mb-5">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Informasi Invoice</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="mb-5">
                        <label class="required form-label">Nama Pelanggan</label>
                        <input type="text" name="customer_name" class="form-control @error('customer_name') is-invalid @enderror" value="{{ old('customer_name', $invoice->customer_name) }}" required>
                        @error('customer_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-5">
                        <label class="required form-label">Tipe Invoice</label>
                        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                            <option value="pemasukan" {{ old('type', $invoice->type) == 'pemasukan' ? 'selected' : '' }}>Pemasukan</option>
                            <option value="pengeluaran" {{ old('type', $invoice->type) == 'pengeluaran' ? 'selected' : '' }}>Pengeluaran</option>
                        </select>
                        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-5">
                        <label class="required form-label">Tanggal Invoice</label>
                        <input type="date" name="issue_date" class="form-control @error('issue_date') is-invalid @enderror" value="{{ old('issue_date', $invoice->issue_date->format('Y-m-d')) }}" required>
                        @error('issue_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-5">
                        <label class="form-label">Tanggal Jatuh Tempo</label>
                        <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date', $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '') }}">
                    </div>

                    <div class="mb-5">
                        <label class="required form-label">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="draft" {{ old('status', $invoice->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="sent" {{ old('status', $invoice->status) == 'sent' ? 'selected' : '' }}>Terkirim (Sent)</option>
                            <option value="paid" {{ old('status', $invoice->status) == 'paid' ? 'selected' : '' }}>Lunas (Paid)</option>
                            <option value="cancelled" {{ old('status', $invoice->status) == 'cancelled' ? 'selected' : '' }}>Dibatalkan (Cancelled)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card card-flush">
                <div class="card-header">
                    <div class="card-title">
                        <h2>Ringkasan Biaya</h2>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-semibold text-gray-600">Subtotal</span>
                        <span class="fw-bold" id="displaySubtotal">Rp 0</span>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Pajak (Tax Amount)</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="tax_amount" id="tax_amount" class="form-control summary-input" value="{{ old('tax_amount', (float)$invoice->tax_amount) }}" min="0" step="0.01">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Diskon (Discount Amount)</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="discount_amount" id="discount_amount" class="form-control summary-input" value="{{ old('discount_amount', (float)$invoice->discount_amount) }}" min="0" step="0.01">
                        </div>
                    </div>

                    <div class="separator separator-dashed my-4"></div>

                    <div class="d-flex justify-content-between">
                        <span class="fw-bold fs-4">Total Akhir</span>
                        <span class="fw-bold fs-4 text-primary" id="displayTotal">Rp 0</span>
                    </div>

                    <div class="mt-8">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-save me-2"></i>Perbarui Invoice
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let itemIndex = {{ count($invoice->items) }};

        // Initialize Select2
        function initSelect2(element) {
            $(element).select2({
                minimumResultsForSearch: 5
            });
        }
        
        $('.product-select').each(function() {
            initSelect2(this);
        });

        function formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID').format(amount);
        }

        function calculateTotals() {
            let subtotal = 0;

            $('.item-row').each(function() {
                const qty = parseFloat($(this).find('.quantity-input').val()) || 0;
                const price = parseFloat($(this).find('.price-input').val()) || 0;
                const rowTotal = qty * price;
                
                $(this).find('.row-total').text(formatCurrency(rowTotal));
                subtotal += rowTotal;
            });

            const tax = parseFloat($('#tax_amount').val()) || 0;
            const discount = parseFloat($('#discount_amount').val()) || 0;
            const finalTotal = subtotal + tax - discount;

            $('#displaySubtotal').text('Rp ' + formatCurrency(subtotal));
            $('#displayTotal').text('Rp ' + formatCurrency(finalTotal));
        }

        // Add Item Row
        $('#btnAddItem').click(function() {
            const newRow = `
                <tr class="item-row">
                    <td>
                        <select name="items[${itemIndex}][product_id]" class="form-select form-select-sm product-select" data-placeholder="Pilih Produk (Opsional)">
                            <option value=""></option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-name="{{ $product->name }}">{{ $product->code }} - {{ $product->name }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="items[${itemIndex}][description]" class="form-control form-control-sm mt-2 description-input" placeholder="Atau ketik deskripsi item" required>
                    </td>
                    <td>
                        <input type="number" name="items[${itemIndex}][quantity]" class="form-control form-control-sm quantity-input" value="1" min="0.01" step="0.01" required>
                    </td>
                    <td>
                        <input type="number" name="items[${itemIndex}][unit_price]" class="form-control form-control-sm price-input" value="0" min="0" step="0.01" required>
                    </td>
                    <td class="text-end fw-bold">
                        Rp <span class="row-total">0</span>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-icon btn-light-danger btn-remove-item"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            `;
            $('#itemsBody').append(newRow);
            initSelect2($('#itemsBody .item-row:last-child .product-select'));
            itemIndex++;
            calculateTotals();
        });

        // Remove Item Row
        $(document).on('click', '.btn-remove-item', function() {
            if ($('.item-row').length > 1) {
                $(this).closest('tr').remove();
                calculateTotals();
            } else {
                toastr.warning('Minimal harus ada satu item');
            }
        });

        // Auto-fill price & description on product select
        $(document).on('change', '.product-select', function() {
            const selectedOption = $(this).find('option:selected');
            if (selectedOption.val()) {
                const price = selectedOption.data('price');
                const name = selectedOption.data('name');
                const row = $(this).closest('tr');
                
                row.find('.price-input').val(price);
                row.find('.description-input').val(name);
                calculateTotals();
            }
        });

        // Trigger calculation on input change
        $(document).on('input', '.quantity-input, .price-input, .summary-input', function() {
            calculateTotals();
        });

        // Initial calculation
        calculateTotals();
    });
</script>
@endpush
