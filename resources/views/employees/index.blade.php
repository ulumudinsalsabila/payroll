@extends('layouts.master')

@section('page_title', 'Data Karyawan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
    <h3 class="fw-bold mb-0">Data Karyawan</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#employeeModal" id="btnAddEmployee">
        <i class="bi bi-plus-lg me-2"></i>Tambah Karyawan
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <div class="mb-2">Terjadi kesalahan validasi:</div>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card">
    <div class="card-body table-responsive">
        <table class="table align-middle table-row-dashed fs-6 gy-5" id="employees_table">
            <thead class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Posisi</th>
                    <th>Departemen</th>
                    <th>PTKP</th>
                    <th>TER</th>
                    <th>Sisa Cuti</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employees as $e)
                <tr
                  data-id="{{ $e->id }}"
                  data-employee_code="{{ $e->employee_code }}"
                  data-name="{{ $e->name }}"
                  data-position="{{ $e->position }}"
                  data-department="{{ $e->department }}"
                  data-address="{{ $e->address }}"
                  data-join_date="{{ $e->join_date?->format('Y-m-d') }}"
                  data-leave_balance="{{ $e->leave_balance }}"
                  data-npwp_number="{{ $e->npwp_number }}"
                  data-ptkp_status="{{ $e->ptkp_status }}"
                  data-ter_category="{{ $e->ter_category }}"
                >
                    <td>{{ $e->employee_code }}</td>
                    <td>{{ $e->name }}</td>
                    <td>{{ $e->position }}</td>
                    <td>{{ $e->department }}</td>
                    <td>{{ $e->ptkp_status }}</td>
                    <td>{{ $e->ter_category }}</td>
                    <td>{{ $e->leave_balance }}</td>
                    <td class="text-end">
                        <button class="btn btn-warning btn-sm me-2 btnEditEmployee"><i class="bi bi-pencil-square"></i> Edit</button>
                        <button class="btn btn-danger btn-sm btnDeleteEmployee"><i class="bi bi-trash"></i> Hapus</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
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
            <div class="col-md-6">
              <label class="form-label">Tanggal Masuk</label>
              <input type="date" name="join_date" id="join_date" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Nomor NPWP (Opsional)</label>
              <input type="text" name="npwp_number" id="npwp_number" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Status PTKP</label>
              <select name="ptkp_status" id="ptkp_status" class="form-select" required>
                <option value="">- Pilih -</option>
                <option value="TK/0">TK/0</option>
                <option value="TK/1">TK/1</option>
                <option value="TK/2">TK/2</option>
                <option value="TK/3">TK/3</option>
                <option value="K/0">K/0</option>
                <option value="K/1">K/1</option>
                <option value="K/2">K/2</option>
                <option value="K/3">K/3</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Kategori TER</label>
              <select name="ter_category" id="ter_category" class="form-select" required>
                <option value="">- Pilih -</option>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="C">C</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Sisa Cuti</label>
              <input type="number" name="leave_balance" id="leave_balance" class="form-control" min="0" value="0">
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
$(document).ready(function() {
  const dt = $('#employees_table').DataTable({ pageLength: 10, ordering: true });

  // Optional: aktifkan Flatpickr (Metronic) bila tersedia
  if (typeof flatpickr !== 'undefined') {
    flatpickr('#join_date', { dateFormat: 'Y-m-d' });
  }

  // Auto close alerts after 3 seconds
  setTimeout(function(){
    document.querySelectorAll('.alert-dismissible').forEach(function(el){
      try {
        bootstrap.Alert.getOrCreateInstance(el).close();
      } catch (e) {}
    });
  }, 3000);

  function toEditMode(row) {
    const id = row.data('id');
    $('#employee_id').val(id);
    
    $('#name').val(row.data('name'));
    $('#position').val(row.data('position'));
    $('#department').val(row.data('department'));
    $('#address').val(row.data('address'));
    $('#join_date').val(row.data('join_date'));
    $('#npwp_number').val(row.data('npwp_number'));
    $('#ptkp_status').val(row.data('ptkp_status'));
    $('#ter_category').val(row.data('ter_category'));
    $('#leave_balance').val(row.data('leave_balance'));

    const form = document.getElementById('employeeForm');
    form.action = `{{ url('employees') }}/${id}`;
    document.getElementById('employeeFormMethod').value = 'PUT';
    document.getElementById('employeeModalTitle').textContent = 'Edit Karyawan';

    const modal = new bootstrap.Modal(document.getElementById('employeeModal'));
    modal.show();
  }

  $('#btnAddEmployee').on('click', function(){
    const form = document.getElementById('employeeForm');
    form.action = `{{ route('employees.store') }}`;
    document.getElementById('employeeFormMethod').value = 'POST';
    document.getElementById('employeeModalTitle').textContent = 'Tambah Karyawan';

    $('#employee_id').val('');
    
    $('#name').val('');
    $('#position').val('');
    $('#department').val('');
    $('#address').val('');
    $('#join_date').val('');
    $('#npwp_number').val('');
    $('#ptkp_status').val('');
    $('#ter_category').val('');
    $('#leave_balance').val('0');
  });

  $('#employees_table').on('click', '.btnEditEmployee', function(){
    const row = $(this).closest('tr');
    toEditMode(row);
  });

  $('#employees_table').on('click', '.btnDeleteEmployee', function(){
    const row = $(this).closest('tr');
    const id = row.data('id');
    const label = row.data('employee_code') + ' - ' + row.data('name');
    $('#employee_delete_name').text(label);
    const form = document.getElementById('employeeDeleteForm');
    form.action = `{{ url('employees') }}/${id}`;

    const modal = new bootstrap.Modal(document.getElementById('confirmDeleteEmployeeModal'));
    modal.show();
  });
});
</script>
@endpush
