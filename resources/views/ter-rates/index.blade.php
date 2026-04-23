@extends('layouts.master')

@section('page_title', 'Referensi Tarif TER')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <h3 class="fw-bold mb-0">Referensi Tarif TER</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#rateModal" id="btnAddRate">
            <i class="bi bi-plus-lg me-2"></i>Tambah Tarif
        </button>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="mb-2">Terjadi kesalahan validasi:</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex flex-wrap gap-3 mb-4">
        <div class="w-auto">
            <label class="form-label mb-1">Filter Kategori</label>
            <select id="filter_category" class="form-select form-select-sm" style="min-width: 160px;">
                <option value="">Semua</option>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="C">C</option>
            </select>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="ter_rates_table">
                <thead class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                    <tr>
                        <th>Kategori</th>
                        <th>Batas Bawah (Rp)</th>
                        <th>Batas Atas (Rp)</th>
                        <th>Persentase (%)</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- Modal Create/Edit -->
    <div class="modal fade" id="rateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="rateForm" method="POST" action="{{ route('ter-rates.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="rateFormMethod" value="POST">
                    <input type="hidden" name="id" id="rate_id">

                    <div class="modal-header">
                        <h5 class="modal-title" id="rateModalTitle">Tambah Tarif</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label">Kategori</label>
                                <select name="category" id="category" class="form-select" required>
                                    <option value="">- Pilih -</option>
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="C">C</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Batas Bawah (Min Bruto)</label>
                                <input type="number" name="min_bruto" id="min_bruto" class="form-control" min="0"
                                    step="1" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Batas Atas (Max Bruto)</label>
                                <input type="number" name="max_bruto" id="max_bruto" class="form-control"
                                    min="0" step="1">
                                <div class="form-text">Kosongkan jika tak terhingga.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Persentase (%)</label>
                                <input type="number" name="percentage" id="percentage" class="form-control"
                                    min="0" max="100" step="0.01" required>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveRate">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Delete -->
    <div class="modal fade" id="confirmDeleteRateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="rateDeleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title">Hapus Tarif</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Yakin ingin menghapus tarif kategori <strong id="rate_delete_label">-</strong>?</p>
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
            function esc(v) {
                return $('<div/>').text(v == null ? '' : String(v)).html();
            }

            const dt = $('#ter_rates_table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                ordering: true,
                order: [],
                ajax: {
                    url: `{{ route('ter-rates.data') }}`,
                    type: 'GET'
                },
                columns: [
                    {
                        data: 'category',
                        name: 'category',
                        render: function(data) {
                            const cls = data === 'A' ? 'success' : (data === 'B' ? 'primary' : 'warning');
                            return '<span class="badge badge-light-' + esc(cls) + '">' + esc(data) + '</span>';
                        }
                    },
                    {
                        data: 'min_bruto',
                        name: 'min_bruto',
                        render: function(data) {
                            const n = Number(data || 0);
                            return 'Rp ' + n.toLocaleString('id-ID');
                        }
                    },
                    {
                        data: 'max_bruto',
                        name: 'max_bruto',
                        render: function(data) {
                            if (data === null || data === '') {
                                return '<span class="badge badge-light-warning">Tak Terhingga</span>';
                            }
                            const n = Number(data || 0);
                            return 'Rp ' + n.toLocaleString('id-ID');
                        }
                    },
                    {
                        data: 'percentage',
                        name: 'percentage',
                        render: function(data) {
                            const n = Number(data);
                            if (Number.isNaN(n)) return esc(data);
                            return n.toFixed(2).replace('.', ',') + '%';
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-end',
                        render: function() {
                            return ''
                                + '<button class="btn btn-warning btn-sm me-2 btnEditRate"><i class="bi bi-pencil-square"></i> Edit</button>'
                                + '<button class="btn btn-danger btn-sm btnDeleteRate"><i class="bi bi-trash"></i> Hapus</button>';
                        }
                    }
                ]
            });

            // Auto close alerts after 3 seconds
            setTimeout(function() {
                document.querySelectorAll('.alert-dismissible').forEach(function(el) {
                    try {
                        bootstrap.Alert.getOrCreateInstance(el).close();
                    } catch (e) {}
                });
            }, 3000);

            // Filter kategori
            $('#filter_category').on('change', function() {
                const v = $(this).val();
                dt.column(0).search(v || '').draw();
            });

            function toEditMode(rowData) {
                const id = rowData.id;
                $('#rate_id').val(id);
                $('#category').val(rowData.category || '');
                $('#min_bruto').val(rowData.min_bruto == null ? '' : String(rowData.min_bruto));
                $('#max_bruto').val(rowData.max_bruto == null ? '' : String(rowData.max_bruto));
                $('#percentage').val(rowData.percentage == null ? '' : String(rowData.percentage));

                const form = document.getElementById('rateForm');
                form.action = `{{ url('ter-rates') }}/${id}`;
                document.getElementById('rateFormMethod').value = 'PUT';
                document.getElementById('rateModalTitle').textContent = 'Edit Tarif';

                const modal = new bootstrap.Modal(document.getElementById('rateModal'));
                modal.show();
            }

            $('#btnAddRate').on('click', function() {
                const form = document.getElementById('rateForm');
                form.action = `{{ route('ter-rates.store') }}`;
                document.getElementById('rateFormMethod').value = 'POST';
                document.getElementById('rateModalTitle').textContent = 'Tambah Tarif';

                $('#rate_id').val('');
                $('#category').val('');
                $('#min_bruto').val('');
                $('#max_bruto').val('');
                $('#percentage').val('');
            });

            $('#ter_rates_table').on('click', '.btnEditRate', function() {
                const tr = $(this).closest('tr');
                const rowData = dt.row(tr).data() || {};
                toEditMode(rowData);
            });

            $('#ter_rates_table').on('click', '.btnDeleteRate', function() {
                const tr = $(this).closest('tr');
                const rowData = dt.row(tr).data() || {};
                const id = rowData.id;
                const label = rowData.category;
                $('#rate_delete_label').text(label);
                const form = document.getElementById('rateDeleteForm');
                form.action = `{{ url('ter-rates') }}/${id}`;

                const modal = new bootstrap.Modal(document.getElementById('confirmDeleteRateModal'));
                modal.show();
            });
        });
    </script>
@endpush
