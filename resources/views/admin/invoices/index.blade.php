@extends('layouts.master')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
    <h3 class="fw-bold mb-0">Data Invoice</h3>
    <div>
        <a href="{{ route('invoices.export') }}" class="btn btn-success me-2">
            <i class="bi bi-file-earmark-excel me-2"></i>Export Excel
        </a>
        <a href="{{ route('invoices.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Buat Invoice
        </a>
    </div>
</div>

<div class="card card-flush">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="invoices_table">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th>No Invoice</th>
                        <th>Pelanggan</th>
                        <th>Tipe</th>
                        <th>Tgl Invoice</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Delete -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Hapus Invoice</h5>
                    <button type="button" class="btn btn-sm btn-icon btn-light" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Yakin ingin menghapus Invoice <strong id="delete_name">-</strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const table = $('#invoices_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('invoices.data') }}",
            columns: [
                { data: 'invoice_number', name: 'invoice_number' },
                { data: 'customer_name', name: 'customer_name' },
                { data: 'type', name: 'type' },
                { data: 'issue_date', name: 'issue_date' },
                { data: 'total_amount', name: 'total_amount' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' }
            ],
            language: {
                emptyTable: "Tidak ada data yang tersedia"
            }
        });

        $('#invoices_table').on('click', '.btnDeleteInvoice', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            $('#delete_name').text(name);
            $('#deleteForm').attr('action', "{{ url('invoices') }}/" + id);
            $('#confirmDeleteModal').modal('show');
        });
    });
</script>

@if(session('success'))
<script>
    toastr.success("{{ session('success') }}");
</script>
@endif

@if(session('error'))
<script>
    toastr.error("{{ session('error') }}");
</script>
@endif
@endpush
