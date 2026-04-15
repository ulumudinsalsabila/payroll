@extends('layouts.master')

@section('page_title', 'Periode Gaji')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
    <h3 class="fw-bold mb-0">Data Periode Gaji</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#periodModal" id="btnAddPeriod">
        <i class="bi bi-plus-lg me-2"></i>Tambah Periode
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
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    setTimeout(function () {
      document.querySelectorAll('.alert.alert-dismissible').forEach(function (el) {
        try {
          bootstrap.Alert.getOrCreateInstance(el).close();
        } catch (e) {
          el.remove();
        }
      });
    }, 3000);
  });
  </script>
@endpush

<div class="card">
    <div class="card-body table-responsive">
        <table class="table align-middle table-row-dashed fs-6 gy-5" id="periods_table">
            <thead class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                <tr>
                    <th>Tahun</th>
                    <th>Bulan</th>
                    <th>Deskripsi</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @php($__bulan = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'])
                @foreach($periods as $p)
                <tr data-id="{{ $p->id }}"
                    data-month="{{ $p->month }}"
                    data-year="{{ $p->year }}"
                    data-description="{{ $p->description }}">
                    <td data-order="{{ $p->year }}">{{ $p->year }}</td>
                    <td data-order="{{ $p->month }}">{{ $__bulan[$p->month] ?? $p->month }}</td>
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
            <select name="month" id="month" class="form-select" required>
              <option value="">- Pilih -</option>
              <option value="01">Januari</option>
              <option value="02">Februari</option>
              <option value="03">Maret</option>
              <option value="04">April</option>
              <option value="05">Mei</option>
              <option value="06">Juni</option>
              <option value="07">Juli</option>
              <option value="08">Agustus</option>
              <option value="09">September</option>
              <option value="10">Oktober</option>
              <option value="11">November</option>
              <option value="12">Desember</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Tahun</label>
            <select name="year" id="year" class="form-select" required>
              <option value="">- Pilih -</option>
              @for ($y = (int) date('Y') - 5; $y <= (int) date('Y') + 1; $y++)
                <option value="{{ $y }}">{{ $y }}</option>
              @endfor
            </select>
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
  const dt = $('#periods_table').DataTable({
    pageLength: 10,
    ordering: true,
    order: [[0, 'desc'], [1, 'desc']] // Tahun desc, Bulan desc
  });

  const monthNames = {
    '01': 'Januari', '02': 'Februari', '03': 'Maret', '04': 'April',
    '05': 'Mei', '06': 'Juni', '07': 'Juli', '08': 'Agustus',
    '09': 'September', '10': 'Oktober', '11': 'November', '12': 'Desember'
  };

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
    const now = new Date();
    const mm = String(now.getMonth() + 1).padStart(2, '0');
    const yy = String(now.getFullYear());
    $('#month').val(mm);
    $('#year').val(yy);
    $('#description').val('');
  });

  $('#periods_table').on('click', '.btnEditPeriod', function(){
    const row = $(this).closest('tr');
    toEditMode(row);
  });

  $('#periods_table').on('click', '.btnDeletePeriod', function(){
    const row = $(this).closest('tr');
    const id = row.data('id');
    const mm = row.data('month');
    const label = (monthNames[mm] || mm) + ' ' + row.data('year');
    $('#delete_name').text(label);
    const form = document.getElementById('deleteForm');
    form.action = `{{ url('payroll-periods') }}/${id}`;

    const modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
    modal.show();
  });
});
</script>
@endpush
