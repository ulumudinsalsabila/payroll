@extends('layouts.master')

@section('page_title', 'Data Barang')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <h3 class="fw-bold mb-0">Data Barang</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal" id="btnAddProduct">
            <i class="bi bi-plus-lg me-2"></i>Tambah Barang
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

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="products_table">
                <thead class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                    <tr>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- Modal Create/Edit -->
    <div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="productForm" method="POST" action="{{ route('products.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="productFormMethod" value="POST">
                    <input type="hidden" name="id" id="product_id">

                    <div class="modal-header">
                        <h5 class="modal-title" id="productModalTitle">Tambah Barang</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Kode Barang</label>
                                <input type="text" name="code" id="code" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nama Barang</label>
                                <input type="text" name="name" id="name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Harga</label>
                                <input type="number" name="price" id="price" class="form-control" required min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="is_active" id="is_active" class="form-select" required>
                                    <option value="1">Aktif</option>
                                    <option value="0">Non-Aktif</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveProduct">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Delete -->
    <div class="modal fade" id="confirmDeleteProductModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="productDeleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title">Hapus Barang</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Yakin ingin menghapus barang <strong id="delete_product_name">-</strong>?</p>
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

            const baseUrl = '{{ url('products') }}';
            const dataUrl = '{{ route('products.data') }}';

            const dt = $('#products_table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                ordering: true,
                order: [[0, 'asc']],
                ajax: {
                    url: dataUrl,
                    type: 'GET'
                },
                columns: [
                    {
                        data: 'code',
                        name: 'code',
                        render: function(data) {
                            return '<span class="fw-bold">' + esc(data) + '</span>';
                        }
                    },
                    {
                        data: 'name',
                        name: 'name',
                        render: function(data, type, row) {
                            return '<div>' + esc(data) + '</div>' + 
                                   (row.description ? '<small class="text-muted">' + esc(row.description) + '</small>' : '');
                        }
                    },
                    {
                        data: 'price',
                        name: 'price',
                        className: 'text-end',
                        render: function(data) {
                            return '<span class="fw-bold text-primary">' + esc(data) + '</span>';
                        }
                    },
                    {
                        data: 'is_active',
                        name: 'is_active',
                        className: 'text-center',
                        render: function(data) {
                            return data;
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-end',
                        render: function(row) {
                            const id = row.id;
                            return '<button class="btn btn-warning btn-sm me-2 btnEditProduct" data-id="' + esc(id) + '"><i class="bi bi-pencil-square"></i> Edit</button>' +
                                   '<button class="btn btn-danger btn-sm btnDeleteProduct" data-id="' + esc(id) + '" data-name="' + esc(row.name) + '"><i class="bi bi-trash"></i> Hapus</button>';
                        }
                    }
                ]
            });

            // Edit button handler
            $('#products_table').on('click', '.btnEditProduct', function() {
                const id = $(this).data('id');
                const rowData = dt.row($(this).closest('tr')).data();
                
                $('#product_id').val(id);
                $('#code').val(rowData.code);
                $('#name').val(rowData.name);
                $('#price').val(rowData.price.replace(/[^0-9]/g, ''));
                $('#description').val(rowData.description || '');
                $('#is_active').val(rowData.is_active_raw || '1');

                const form = document.getElementById('productForm');
                form.action = baseUrl + '/' + id;
                document.getElementById('productFormMethod').value = 'PUT';
                document.getElementById('productModalTitle').textContent = 'Edit Barang';

                const modal = new bootstrap.Modal(document.getElementById('productModal'));
                modal.show();
            });

            // Delete button handler
            $('#products_table').on('click', '.btnDeleteProduct', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                
                $('#delete_product_name').text(name);
                
                const form = document.getElementById('productDeleteForm');
                form.action = baseUrl + '/' + id;

                const modal = new bootstrap.Modal(document.getElementById('confirmDeleteProductModal'));
                modal.show();
            });

            // Reset form saat tambah baru
            $('#btnAddProduct').on('click', function() {
                const form = document.getElementById('productForm');
                form.action = '{{ route('products.store') }}';
                document.getElementById('productFormMethod').value = 'POST';
                document.getElementById('productModalTitle').textContent = 'Tambah Barang';
                
                $('#product_id').val('');
                $('#code').val('');
                $('#name').val('');
                $('#price').val('');
                $('#description').val('');
                $('#is_active').val('1');
            });
        });
    </script>
@endpush
