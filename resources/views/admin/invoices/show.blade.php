@extends('layouts.master')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
    <h3 class="fw-bold mb-0">Detail Invoice {{ $invoice->invoice_number }}</h3>
    <div>
        <a href="{{ route('invoices.print', $invoice->id) }}" target="_blank" class="btn btn-success me-2">
            <i class="bi bi-printer me-2"></i>Cetak Preview
        </a>
        <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn btn-warning me-2">
            <i class="bi bi-pencil me-2"></i>Edit
        </a>
        <a href="{{ route('invoices.index') }}" class="btn btn-light">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="card card-flush">
    <div class="card-body p-9">
        <div class="row mb-10">
            <div class="col-sm-6">
                <div class="fw-bold fs-4 text-gray-800 mb-2">Informasi Invoice</div>
                <div class="fw-semibold text-gray-600 fs-6">
                    <table class="table table-borderless align-middle gs-0 gy-1">
                        <tr>
                            <td class="text-muted w-150px">No. Invoice</td>
                            <td class="text-gray-800">{{ $invoice->invoice_number }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                @php
                                    $badges = [
                                        'draft' => 'badge-light-secondary',
                                        'sent' => 'badge-light-primary',
                                        'paid' => 'badge-light-success',
                                        'cancelled' => 'badge-light-danger',
                                    ];
                                    $class = $badges[$invoice->status] ?? 'badge-light';
                                @endphp
                                <span class="badge {{ $class }}">{{ ucfirst($invoice->status) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tgl Terbit</td>
                            <td class="text-gray-800">{{ $invoice->issue_date->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Jatuh Tempo</td>
                            <td class="text-gray-800">{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="col-sm-6 text-sm-end">
                <div class="fw-bold fs-4 text-gray-800 mb-2">Ditagihkan Kepada:</div>
                <div class="fw-semibold text-gray-600 fs-6">
                    {{ $invoice->customer_name }}
                </div>
            </div>
        </div>

        <div class="table-responsive mb-10">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th>Deskripsi</th>
                        <th class="text-center w-100px">Kuantitas</th>
                        <th class="text-end min-w-100px">Harga Satuan</th>
                        <th class="text-end min-w-100px">Total</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach($invoice->items as $item)
                    <tr>
                        <td>
                            <div class="text-gray-800">{{ $item->description }}</div>
                            @if($item->product)
                            <div class="text-muted fs-7">Produk: {{ $item->product->name }} ({{ $item->product->code }})</div>
                            @endif
                        </td>
                        <td class="text-center">{{ (float)$item->quantity }}</td>
                        <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="text-end text-gray-800 fw-bold">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end mb-10">
            <div class="mw-300px w-100">
                <div class="d-flex justify-content-between mb-3">
                    <div class="fw-semibold text-gray-600">Subtotal:</div>
                    <div class="fw-bold text-gray-800">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</div>
                </div>
                
                @if($invoice->tax_amount > 0)
                <div class="d-flex justify-content-between mb-3">
                    <div class="fw-semibold text-gray-600">Pajak:</div>
                    <div class="fw-bold text-gray-800">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</div>
                </div>
                @endif

                @if($invoice->discount_amount > 0)
                <div class="d-flex justify-content-between mb-3">
                    <div class="fw-semibold text-gray-600">Diskon:</div>
                    <div class="fw-bold text-danger">- Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}</div>
                </div>
                @endif

                <div class="separator separator-dashed my-4"></div>

                <div class="d-flex justify-content-between align-items-center">
                    <div class="fw-bold fs-4 text-gray-800">Total Akhir:</div>
                    <div class="fw-bold fs-3 text-primary">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        @if($invoice->notes)
        <div class="mb-0">
            <div class="fw-bold text-gray-800 mb-2">Catatan:</div>
            <div class="fw-semibold text-gray-600">{{ $invoice->notes }}</div>
        </div>
        @endif
    </div>
</div>
@endsection
