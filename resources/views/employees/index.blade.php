@extends('layouts.master')

@section('page_title', 'Data Karyawan')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <h3 class="fw-bold mb-0">Data Karyawan</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#employeeModal" id="btnAddEmployee">
            <i class="bi bi-plus-lg me-2"></i>Tambah Karyawan
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
            <label class="form-label mb-1">Cari Kode/Nama</label>
            <input type="text" id="search_emp" class="form-control form-control-sm" placeholder="Ketik kode atau nama..."
                style="min-width: 240px;">
        </div>
        <div class="w-auto">
            <label class="form-label mb-1">Filter Departemen</label>
            <select id="filter_department" class="form-select form-select-sm" style="min-width: 200px;">
                <option value="">Semua</option>
                @foreach ($departments as $d)
                    <option value="{{ $d }}">{{ $d }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-auto">
            <label class="form-label mb-1">Filter Posisi</label>
            <select id="filter_position" class="form-select form-select-sm" style="min-width: 200px;">
                <option value="">Semua</option>
                @foreach ($positions as $p)
                    <option value="{{ $p }}">{{ $p }}</option>
                @endforeach
            </select>
        </div>

    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="employees_table">
                <thead class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Posisi</th>
                        <th>Departemen</th>
                        <th>Sisa Cuti</th>
                        <th class="text-end">Gaji Pokok</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- Modal Create/Edit -->
    <div class="modal fade" id="employeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="employeeForm" method="POST" action="{{ route('employees.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="employeeFormMethod" value="POST">
                    <input type="hidden" name="id" id="employee_id">

                    <div class="modal-header">
                        <h5 class="modal-title" id="employeeModalTitle">Tambah Karyawan</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-4">

                            <div class="col-md-6">
                                <label class="form-label">Nama</label>
                                <input type="text" name="name" id="name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email (Opsional)</label>
                                <input type="email" name="email" id="email" class="form-control"
                                    placeholder="nama@perusahaan.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status Karyawan</label>
                                <select name="is_active" id="is_active" class="form-select" required>
                                    <option value="1">Active</option>
                                    <option value="0">Non-Active</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Posisi</label>
                                <input type="text" name="position" id="position" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Departemen</label>
                                <input type="text" name="department" id="department" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Alamat</label>
                                <textarea name="address" id="address" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Bank (Opsional)</label>
                                <input type="text" name="bank_name" id="bank_name" class="form-control"
                                    placeholder="BCA / Mandiri / BRI ...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Nama Rekening (Opsional)</label>
                                <input type="text" name="bank_account_name" id="bank_account_name"
                                    class="form-control" placeholder="Nama pemilik rekening">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Nomor Rekening (Opsional)</label>
                                <input type="text" name="bank_account_number" id="bank_account_number"
                                    class="form-control" placeholder="1234567890">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Masuk</label>
                                <input type="date" name="join_date" id="join_date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nomor NPWP (Opsional)</label>
                                <input type="text" name="npwp_number" id="npwp_number" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Sisa Cuti</label>
                                <input type="number" name="leave_balance" id="leave_balance" class="form-control"
                                    min="0" value="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gaji Pokok</label>
                                <input type="number" name="basic_salary" id="basic_salary" class="form-control"
                                    min="0" placeholder="0">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveEmployee">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Delete -->
    <div class="modal fade" id="confirmDeleteEmployeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="employeeDeleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title">Hapus Karyawan</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Yakin ingin menghapus karyawan <strong id="employee_delete_name">-</strong>?</p>
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
        function formatRupiah(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount);
        }

        $(document).ready(function() {
            function esc(v) {
                return $('<div/>').text(v == null ? '' : String(v)).html();
            }

            const dt = $('#employees_table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                ordering: true,
                ajax: {
                    url: `{{ route('employees.data') }}`,
                    type: 'GET'
                },
                columns: [{
                        data: 'employee_code',
                        name: 'employee_code',
                        render: function(data) {
                            return esc(data);
                        }
                    },
                    {
                        data: 'name',
                        name: 'name',
                        render: function(data) {
                            return esc(data);
                        }
                    },
                    {
                        data: 'position',
                        name: 'position',
                        render: function(data) {
                            return esc(data);
                        }
                    },
                    {
                        data: 'department',
                        name: 'department',
                        render: function(data) {
                            return esc(data);
                        }
                    },
                    {
                        data: 'leave_balance',
                        name: 'leave_balance',
                        render: function(data) {
                            return esc(data);
                        }
                    },
                    {
                        data: 'basic_salary',
                        name: 'basic_salary',
                        className: 'text-end',
                        render: function(data) {
                            return data ? formatRupiah(data) : '0';
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-end',
                        render: function() {
                            return '' +
                                '<button class="btn btn-warning btn-sm me-2 btnEditEmployee"><i class="bi bi-pencil-square"></i> Edit</button>' +
                                '<button class="btn btn-danger btn-sm btnDeleteEmployee"><i class="bi bi-trash"></i> Hapus</button>';
                        }
                    }
                ]
            });

            // Optional: aktifkan Flatpickr (Metronic) bila tersedia
            if (typeof flatpickr !== 'undefined') {
                flatpickr('#join_date', {
                    dateFormat: 'Y-m-d'
                });
            }

            // Auto close alerts after 3 seconds
            setTimeout(function() {
                document.querySelectorAll('.alert-dismissible').forEach(function(el) {
                    try {
                        bootstrap.Alert.getOrCreateInstance(el).close();
                    } catch (e) {}
                });
            }, 3000);

            // Global search for kode/nama
            $('#search_emp').on('keyup change', function() {
                dt.search(this.value).draw();
            });

            // Filter Department (kolom 3)
            $('#filter_department').on('change', function() {
                const v = this.value;
                if (v) {
                    dt.column(3).search(v).draw();
                } else {
                    dt.column(3).search('').draw();
                }
            });

            // Filter Posisi (kolom 2)
            $('#filter_position').on('change', function() {
                const v = this.value;
                if (v) {
                    dt.column(2).search(v).draw();
                } else {
                    dt.column(2).search('').draw();
                }
            });

            function toEditMode(rowData) {
                const id = rowData.id;
                $('#employee_id').val(id);

                $('#name').val(rowData.name || '');
                $('#email').val(rowData.email || '');
                $('#is_active').val(String(rowData.is_active) === '1' ? '1' : '0');
                $('#position').val(rowData.position || '');
                $('#department').val(rowData.department || '');
                $('#address').val(rowData.address || '');
                $('#bank_name').val(rowData.bank_name || '');
                $('#bank_account_name').val(rowData.bank_account_name || '');
                $('#bank_account_number').val(rowData.bank_account_number || '');
                $('#join_date').val(rowData.join_date || '');
                $('#npwp_number').val(rowData.npwp_number || '');
                $('#leave_balance').val(rowData.leave_balance == null ? '0' : String(rowData.leave_balance));
                $('#basic_salary').val(rowData.basic_salary == null ? '0' : String(rowData.basic_salary));

                const form = document.getElementById('employeeForm');
                form.action = `{{ url('employees') }}/${id}`;
                document.getElementById('employeeFormMethod').value = 'PUT';
                document.getElementById('employeeModalTitle').textContent = 'Edit Karyawan';

                const modal = new bootstrap.Modal(document.getElementById('employeeModal'));
                modal.show();
            }

            $('#btnAddEmployee').on('click', function() {
                const form = document.getElementById('employeeForm');
                form.action = `{{ route('employees.store') }}`;
                document.getElementById('employeeFormMethod').value = 'POST';
                document.getElementById('employeeModalTitle').textContent = 'Tambah Karyawan';

                $('#employee_id').val('');

                $('#name').val('');
                $('#email').val('');
                $('#is_active').val('1');
                $('#position').val('');
                $('#department').val('');
                $('#address').val('');
                $('#bank_name').val('');
                $('#bank_account_name').val('');
                $('#bank_account_number').val('');
                $('#join_date').val('');
                $('#npwp_number').val('');
                $('#leave_balance').val('0');
                $('#basic_salary').val('');
            });

            $('#employees_table').on('click', '.btnEditEmployee', function() {
                const tr = $(this).closest('tr');
                const rowData = dt.row(tr).data() || {};
                toEditMode(rowData);
            });

            $('#employees_table').on('click', '.btnDeleteEmployee', function() {
                const tr = $(this).closest('tr');
                const rowData = dt.row(tr).data() || {};
                const id = rowData.id;
                const label = (rowData.employee_code || '-') + ' - ' + (rowData.name || '-');
                $('#employee_delete_name').text(label);
                const form = document.getElementById('employeeDeleteForm');
                form.action = `{{ url('employees') }}/${id}`;

                const modal = new bootstrap.Modal(document.getElementById('confirmDeleteEmployeeModal'));
                modal.show();
            });
        });
    </script>
@endpush
