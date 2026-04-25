@extends('layouts.master')

@section('page_title', 'Komponen Gaji')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <h3 class="fw-bold mb-0">Komponen Gaji</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#componentModal" id="btnAddComponent">
            <i class="bi bi-plus-lg me-2"></i>Tambah Komponen
        </button>
    </div>


    <div class="d-flex flex-wrap gap-3 mb-4">
        <div class="w-auto">
            <label class="form-label mb-1">Cari Nama Komponen</label>
            <input type="text" id="search_component_name" class="form-control form-control-sm"
                placeholder="Ketik nama..." style="min-width: 220px;">
        </div>
        <div class="w-auto">
            <label class="form-label mb-1">Filter Tipe</label>
            <select id="filter_component_type" class="form-select form-select-sm" style="min-width: 180px;">
                <option value="">Semua</option>
                <option value="earning">Pendapatan</option>
                <option value="deduction">Potongan</option>
                <option value="tax">Pajak</option>
            </select>
        </div>
        <div class="w-auto">
            <label class="form-label mb-1">Filter Status</label>
            <select id="filter_component_status" class="form-select form-select-sm" style="min-width: 160px;">
                <option value="">Semua</option>
                <option value="1">Aktif</option>
                <option value="0">Non-aktif</option>
            </select>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="components_table">
                <thead class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                    <tr>
                        <th>Nama Komponen</th>
                        <th>Tipe</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- Modal Create/Edit -->
    <div class="modal fade" id="componentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="componentForm" method="POST" action="{{ route('payslip-components.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="componentFormMethod" value="POST">
                    <input type="hidden" name="id" id="component_id">

                    <div class="modal-header">
                        <h5 class="modal-title" id="componentModalTitle">Tambah Komponen</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-4">
                            <div class="col-12">
                                <label class="form-label">Nama Komponen</label>
                                <input type="text" name="name" id="name" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Tipe</label>
                                <select name="type" id="type" class="form-select" required>
                                    <option value="">- Pilih -</option>
                                    <option value="earning">Pendapatan</option>
                                    <option value="deduction">Potongan</option>
                                    <option value="tax">Pajak</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active">
                                    <label class="form-check-label" for="is_active">Aktif</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveComponent">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Delete -->
    <div class="modal fade" id="confirmDeleteComponentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="componentDeleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title">Hapus Komponen</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Yakin ingin menghapus komponen <strong id="component_delete_name">-</strong>?</p>
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

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        $(document).ready(function() {
            function esc(v) {
                return $('<div/>').text(v == null ? '' : String(v)).html();
            }

            const dt = $('#components_table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                ordering: true,
                ajax: {
                    url: `{{ route('payslip-components.data') }}`,
                    type: 'GET'
                },
                columns: [{
                        data: 'name',
                        name: 'name',
                        render: function(data) {
                            return esc(data);
                        }
                    },
                    {
                        data: 'type',
                        name: 'type',
                        render: function(data) {
                            const map = {
                                earning: ['Pendapatan', 'success'],
                                deduction: ['Potongan', 'danger'],
                                tax: ['Pajak', 'warning']
                            };
                            const m = map[data] || [data, 'secondary'];
                            return '<span class="badge badge-light-' + esc(m[1]) + '">' + esc(m[
                                0]) + '</span>';
                        }
                    },
                    {
                        data: 'is_active',
                        name: 'is_active',
                        render: function(data) {
                            if (String(data) === '1')
                            return '<span class="badge badge-light-success">Aktif</span>';
                            return '<span class="badge badge-light-secondary">Non-aktif</span>';
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-end',
                        render: function() {
                            return '' +
                                '<button class="btn btn-warning btn-sm me-2 btnEditComponent"><i class="bi bi-pencil-square"></i> Edit</button>' +
                                '<button class="btn btn-danger btn-sm btnDeleteComponent"><i class="bi bi-trash"></i> Hapus</button>';
                        }
                    }
                ]
            });

            // Toastr defaults
            if (typeof toastr !== 'undefined') {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    timeOut: 3000,
                    positionClass: 'toast-top-right'
                };
            }

            // Show flash via Toastr if available
            @if (session('success'))
                if (typeof toastr !== 'undefined') toastr.success(@json(session('success')));
            @endif
            @if (session('error'))
                if (typeof toastr !== 'undefined') toastr.error(@json(session('error')));
            @endif
            @if ($errors->any())
                if (typeof toastr !== 'undefined') toastr.error(
                    'Terjadi kesalahan validasi. Periksa formulir Anda.');
            @endif

            // Search nama komponen
            $('#search_component_name').on('keyup change', function() {
                dt.column(0).search(this.value).draw();
            });

            // Filter tipe (server-side)
            $('#filter_component_type').on('change', function() {
                const v = this.value;
                if (v) {
                    dt.column(1).search(v).draw();
                } else {
                    dt.column(1).search('').draw();
                }
            });

            // Filter status (server-side)
            $('#filter_component_status').on('change', function() {
                const v = this.value;
                if (v) {
                    dt.column(2).search(v || '').draw();
                }
            });

            function toEditMode(rowData) {
                const id = rowData.id;
                $('#component_id').val(id);
                $('#name').val(rowData.name || '');
                $('#type').val(rowData.type || '');
                $('#is_active').prop('checked', String(rowData.is_active) === '1');

                const form = document.getElementById('componentForm');
                form.action = `{{ url('payslip-components') }}/${id}`;
                document.getElementById('componentFormMethod').value = 'PUT';
                document.getElementById('componentModalTitle').textContent = 'Edit Komponen';

                const modal = new bootstrap.Modal(document.getElementById('componentModal'));
                modal.show();
            }

            $('#btnAddComponent').on('click', function() {
                const form = document.getElementById('componentForm');
                form.action = `{{ route('payslip-components.store') }}`;
                document.getElementById('componentFormMethod').value = 'POST';
                document.getElementById('componentModalTitle').textContent = 'Tambah Komponen';

                $('#component_id').val('');
                $('#name').val('');
                $('#type').val('');
                $('#is_active').prop('checked', true);
            });

            $('#components_table').on('click', '.btnEditComponent', function() {
                const tr = $(this).closest('tr');
                const rowData = dt.row(tr).data() || {};
                toEditMode(rowData);
            });

            $('#components_table').on('click', '.btnDeleteComponent', function() {
                const tr = $(this).closest('tr');
                const rowData = dt.row(tr).data() || {};
                const id = rowData.id;
                const label = rowData.name || '-';
                $('#component_delete_name').text(label);
                const form = document.getElementById('componentDeleteForm');
                form.action = `{{ url('payslip-components') }}/${id}`;

                const modal = new bootstrap.Modal(document.getElementById('confirmDeleteComponentModal'));
                modal.show();
            });
        });
    </script>
@endpush
