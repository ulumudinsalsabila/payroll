@extends('layouts.master')

@section('page_title', 'Periode Gaji')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
    <h3 class="fw-bold mb-0">Data Periode Gaji</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#periodModal" id="btnAddPeriod">
        <i class="bi bi-plus-lg me-2"></i>Tambah Periode
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-body table-responsive">
        <table class="table align-middle table-row-dashed fs-6 gy-5" id="periods_table">
            <thead class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                <tr>
                    <th>Bulan</th>
                    <th>Tahun</th>
                    <th>Deskripsi</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($periods as $p)
                <tr data-id="{{ $p->id }}"
                    data-month="{{ $p->month }}"
                    data-year="{{ $p->year }}"
                    data-description="{{ $p->description }}">
                    <td>{{ $p->month }}</td>
                    <td>{{ $p->year }}</td>
                    <td>{{ $p->description }}</td>
                    <td><span class="badge bg-light-{{ $p->status === 'draft' ? 'warning' : 'success' }} text-{{ $p->status === 'draft' ? 'warning' : 'success' }}">{{ $p->status }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('payroll-periods.show', $p->id) }}" class="btn btn-light btn-sm me-2"><i class="bi bi-eye"></i> Detail</a>
                        <button class="btn btn-warning btn-sm me-2 btnEditPeriod"><i class="bi bi-pencil-square"></i> Edit</button>
                        <button class="btn btn-danger btn-sm btnDeletePeriod"><i class="bi bi-trash"></i> Hapus</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Create/Edit -->
<div class="modal fade" id="periodModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="periodForm" method="POST" action="{{ route('payroll-periods.store') }}">
        @csrf
        <input type="hidden" name="_method" id="periodFormMethod" value="POST">
        <input type="hidden" name="id" id="period_id">

        <div class="modal-header">
          <h5 class="modal-title" id="periodModalTitle">Tambah Periode</h5>
          <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Bulan</label>
            <input type="text" name="month" id="month" class="form-control" maxlength="2" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Tahun</label>
            <input type="text" name="year" id="year" class="form-control" maxlength="4" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Deskripsi</label>
            <textarea name="description" id="description" class="form-control" rows="3"></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="btnSavePeriod">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Konfirmasi Delete -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="deleteForm" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-header">
          <h5 class="modal-title">Hapus Periode</h5>
          <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
        <div class="modal-body">
          <p>Yakin ingin menghapus periode <strong id="delete_name">-</strong>?</p>
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
  const dt = $('#periods_table').DataTable({ pageLength: 10, ordering: true });

  function toEditMode(row) {
    const id = row.data('id');
    $('#period_id').val(id);
    $('#month').val(row.data('month'));
    $('#year').val(row.data('year'));
    $('#description').val(row.data('description'));

    const form = document.getElementById('periodForm');
    form.action = `{{ url('payroll-periods') }}/${id}`;
    document.getElementById('periodFormMethod').value = 'PUT';
    document.getElementById('periodModalTitle').textContent = 'Edit Periode';

    const modal = new bootstrap.Modal(document.getElementById('periodModal'));
    modal.show();
  }

  $('#btnAddPeriod').on('click', function(){
    const form = document.getElementById('periodForm');
    form.action = `{{ route('payroll-periods.store') }}`;
    document.getElementById('periodFormMethod').value = 'POST';
    document.getElementById('periodModalTitle').textContent = 'Tambah Periode';
    $('#period_id').val('');
    $('#month').val('');
    $('#year').val('');
    $('#description').val('');
  });

  $('#periods_table').on('click', '.btnEditPeriod', function(){
    const row = $(this).closest('tr');
    toEditMode(row);
  });

  $('#periods_table').on('click', '.btnDeletePeriod', function(){
    const row = $(this).closest('tr');
    const id = row.data('id');
    const label = row.data('month') + '/' + row.data('year');
    $('#delete_name').text(label);
    const form = document.getElementById('deleteForm');
    form.action = `{{ url('payroll-periods') }}/${id}`;

    const modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
    modal.show();
  });
});
</script>
@endpush
