@extends('layouts.master')

@section('page_title', 'Data Absensi (Bulanan)')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
    <h3 class="fw-bold mb-0">Rekap Absensi Bulanan</h3>
    <div>
        <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="bi bi-file-earmark-arrow-up me-2"></i>Import Statistik Fingerprint
        </button>
        <button class="btn btn-danger me-2" data-bs-toggle="modal" data-bs-target="#deletePeriodModal">
            <i class="bi bi-trash me-2"></i>Hapus Periode
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#attendanceModal" id="btnAddAttendance">
            <i class="bi bi-plus-lg me-2"></i>Tambah Rekap
        </button>
    </div>
</div>

<div class="card card-flush mb-5">
    <div class="card-body">
        <div class="row align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-bold">Filter Periode Bulan</label>
                <input type="month" id="filter_period" class="form-control" value="{{ date('Y-m') }}">
            </div>
            <div class="col-md-3">
                <button type="button" id="btnResetFilter" class="btn btn-light">Reset</button>
            </div>
        </div>
    </div>
</div>

<div class="card card-flush">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="attendances_table">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th>Periode</th>
                        <th>Karyawan</th>
                        <th>Hadir (Hari)</th>
                        <th>Absen (Hari)</th>
                        <th>Terlambat (Min)</th>
                        <th>Lembur</th>
                        <th>Catatan</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Import -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('attendances.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Laporan Statistik Absensi</h5>
                    <button type="button" class="btn btn-sm btn-icon btn-light" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">File Excel (Laporan Statistik)</label>
                        <input type="file" class="form-control" name="excel_file" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <div class="alert alert-info py-3 d-flex align-items-center">
                        <i class="bi bi-info-circle fs-2 me-3"></i>
                        <span>Import ini dikhususkan untuk file <strong>"Lap. Statistik Absensi"</strong> dari mesin fingerprint.</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Create/Edit -->
<div class="modal fade" id="attendanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="attendanceForm" method="POST" action="{{ route('attendances.store') }}">
                @csrf
                <input type="hidden" name="_method" id="attendanceFormMethod" value="POST">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="attendanceModalTitle">Tambah Rekap Absensi</h5>
                    <button type="button" class="btn btn-sm btn-icon btn-light" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-5">
                        <label class="required form-label">Karyawan</label>
                        <select name="employee_id" id="employee_id" class="form-select" data-control="select2" data-dropdown-parent="#attendanceModal" required>
                            <option value="">Pilih Karyawan</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-5">
                        <label class="required form-label">Periode Bulan</label>
                        <input type="month" name="period_month" id="period_month" class="form-control" value="{{ date('Y-m') }}" required>
                    </div>
                    <div class="row mb-5">
                        <div class="col-6">
                            <label class="required form-label">Hadir (Hari)</label>
                            <input type="number" name="present_days" id="present_days" class="form-control" min="0" required>
                        </div>
                        <div class="col-6">
                            <label class="required form-label">Absen (Hari)</label>
                            <input type="number" name="absent_days" id="absent_days" class="form-control" min="0" required>
                        </div>
                    </div>
                    <div class="row mb-5">
                        <div class="col-6">
                            <label class="required form-label">Terlambat (Menit)</label>
                            <input type="number" name="late_minutes" id="late_minutes" class="form-control" min="0" required>
                        </div>
                        <div class="col-6">
                            <label class="required form-label">Lembur</label>
                            <input type="text" name="overtime_hours" id="overtime_hours" class="form-control" placeholder="0:00" required>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" id="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveAttendance">Simpan</button>
                </div>
            </form>
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
                    <h5 class="modal-title">Hapus Data Rekap</h5>
                    <button type="button" class="btn btn-sm btn-icon btn-light" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Yakin ingin menghapus rekap absensi <strong id="delete_name">-</strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Delete Period -->
<div class="modal fade" id="deletePeriodModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('attendances.destroy-period') }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Hapus Periode Absensi</h5>
                    <button type="button" class="btn btn-sm btn-icon btn-light" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger py-3 d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle fs-2 me-3 text-danger"></i>
                        <span><strong>Perhatian:</strong> Seluruh data absensi pada periode yang dipilih akan dihapus permanen!</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Pilih Periode Bulan</label>
                        <input type="month" name="period_month" class="form-control" required value="{{ date('Y-m') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus Permanen</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const table = $('#attendances_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('attendances.data') }}",
                data: function (d) {
                    d.period_month = $('#filter_period').val();
                }
            },
            columns: [
                { data: 'period_month', name: 'period_month' },
                { data: 'employee_name', name: 'employee_name' },
                { data: 'present_days', name: 'present_days' },
                { data: 'absent_days', name: 'absent_days' },
                { data: 'late_minutes', name: 'late_minutes' },
                { data: 'overtime_hours', name: 'overtime_hours' },
                { data: 'notes', name: 'notes', render: function(data) { return data ? data : '-'; } },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' }
            ],
            language: {
                emptyTable: "Tidak ada data yang tersedia"
            }
        });

        $('#filter_period').on('change', function() {
            table.draw();
        });

        $('#btnResetFilter').on('click', function() {
            $('#filter_period').val('');
            table.draw();
        });

        // Add
        $('#btnAddAttendance').click(function() {
            $('#attendanceForm')[0].reset();
            $('#employee_id').val('').trigger('change');
            $('#attendanceFormMethod').val('POST');
            $('#attendanceForm').attr('action', "{{ route('attendances.store') }}");
            $('#attendanceModalTitle').text('Tambah Rekap Absensi');
        });

        // Edit
        $('#attendances_table').on('click', '.btnEdit', function() {
            const id = $(this).data('id');
            $('#attendanceFormMethod').val('PUT');
            $('#attendanceForm').attr('action', "{{ url('attendances') }}/" + id);
            $('#attendanceModalTitle').text('Edit Rekap Absensi');
            
            $('#employee_id').val($(this).data('employee_id')).trigger('change');
            $('#period_month').val($(this).data('period_month'));
            $('#present_days').val($(this).data('present_days'));
            $('#absent_days').val($(this).data('absent_days'));
            $('#late_minutes').val($(this).data('late_minutes'));
            $('#overtime_hours').val($(this).data('overtime_hours'));
            $('#notes').val($(this).data('notes'));

            $('#attendanceModal').modal('show');
        });

        // Delete
        $('#attendances_table').on('click', '.btnDelete', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            $('#delete_name').text(name);
            $('#deleteForm').attr('action', "{{ url('attendances') }}/" + id);
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

@if($errors->any())
<script>
    toastr.error("{{ implode('\n', $errors->all()) }}");
</script>
@endif
@endpush
