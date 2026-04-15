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
        <input type="text" id="search_component_name" class="form-control form-control-sm" placeholder="Ketik nama..." style="min-width: 220px;">
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
                    <th>Persentase (%)</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($components as $c)
                <tr
                  data-id="{{ $c->id }}"
                  data-name="{{ $c->name }}"
                  data-type="{{ $c->type }}"
                  data-percentage="{{ $c->type === 'deduction' ? $c->percentage : '' }}"
                  data-max_cap="{{ $c->type === 'deduction' ? $c->max_cap : '' }}"
                  data-is_active="{{ $c->is_active ? '1' : '0' }}"
                >
                    <td>{{ $c->name }}</td>
                    <td>
                        @php
                            $map = [
                                'earning' => ['Pendapatan','success'],
                                'deduction' => ['Potongan','danger'],
                                'tax' => ['Pajak','warning'],
                            ];
                            [$label,$cls] = $map[$c->type] ?? [$c->type,'secondary'];
                        @endphp
                        <span class="badge badge-light-{{ $cls }}">{{ $label }}</span>
                    </td>
                    <td>
                        {{ $c->type === 'deduction' && $c->percentage !== null ? number_format((float) $c->percentage, 2, '.', '') : '-' }}
                    </td>
                    <td>
                        @if($c->is_active)
                            <span class="badge badge-light-success">Aktif</span>
                        @else
                            <span class="badge badge-light-secondary">Non-aktif</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <button class="btn btn-warning btn-sm me-2 btnEditComponent"><i class="bi bi-pencil-square"></i> Edit</button>
                        <button class="btn btn-danger btn-sm btnDeleteComponent"><i class="bi bi-trash"></i> Hapus</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
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
              <label class="form-label">Persentase (%)</label>
              <input type="number" name="percentage" id="percentage" class="form-control" step="0.01" min="0" max="100">
            </div>
            <div class="col-12">
              <label class="form-label">Batas Maksimal Upah / Max Cap</label>
              <input type="number" name="max_cap" id="max_cap" class="form-control" min="0">
              <div class="form-text">Kosongkan jika tidak ada batas maksimal</div>
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
  const dt = $('#components_table').DataTable({ pageLength: 10, ordering: true });

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
  @if(session('success'))
    if (typeof toastr !== 'undefined') toastr.success(@json(session('success')));
  @endif
  @if(session('error'))
    if (typeof toastr !== 'undefined') toastr.error(@json(session('error')));
  @endif
  @if($errors->any())
    if (typeof toastr !== 'undefined') toastr.error('Terjadi kesalahan validasi. Periksa formulir Anda.');
  @endif

  function escRe(s){ return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }

  // Search nama komponen (kolom 0)
  $('#search_component_name').on('keyup change', function(){
    dt.column(0).search(this.value).draw();
  });

  // Filter tipe (kolom 1) berdasarkan label Indonesia
  const typeMap = { earning: 'Pendapatan', deduction: 'Potongan', tax: 'Pajak' };
  $('#filter_component_type').on('change', function(){
    const v = this.value;
    if (v) {
      const label = typeMap[v] || v;
      dt.column(1).search('^' + escRe(label) + '$', true, false).draw();
    } else {
      dt.column(1).search('').draw();
    }
  });

  // Filter status (kolom 3)
  $('#filter_component_status').on('change', function(){
    const v = this.value;
    if (v === '1') {
      dt.column(3).search('^Aktif$', true, false).draw();
    } else if (v === '0') {
      dt.column(3).search('^Non-aktif$', true, false).draw();
    } else {
      dt.column(3).search('').draw();
    }
  });

  function toEditMode(row) {
    const id = row.data('id');
    $('#component_id').val(id);
    $('#name').val(row.data('name'));
    $('#type').val(row.data('type'));
    $('#percentage').val(row.data('percentage'));
    $('#max_cap').val(row.data('max_cap'));
    $('#is_active').prop('checked', row.data('is_active') == '1');

    toggleCalcFields();

    const form = document.getElementById('componentForm');
    form.action = `{{ url('payslip-components') }}/${id}`;
    document.getElementById('componentFormMethod').value = 'PUT';
    document.getElementById('componentModalTitle').textContent = 'Edit Komponen';

    const modal = new bootstrap.Modal(document.getElementById('componentModal'));
    modal.show();
  }

  $('#btnAddComponent').on('click', function(){
    const form = document.getElementById('componentForm');
    form.action = `{{ route('payslip-components.store') }}`;
    document.getElementById('componentFormMethod').value = 'POST';
    document.getElementById('componentModalTitle').textContent = 'Tambah Komponen';

    $('#component_id').val('');
    $('#name').val('');
    $('#type').val('');
    $('#percentage').val('');
    $('#max_cap').val('');
    $('#is_active').prop('checked', true);

    toggleCalcFields();
  });

  function toggleCalcFields() {
    const t = $('#type').val();
    const isDeduction = t === 'deduction';
    $('#percentage').prop('disabled', !isDeduction).prop('required', isDeduction);
    $('#max_cap').prop('disabled', !isDeduction);
    if (!isDeduction) {
      $('#percentage').val('');
      $('#max_cap').val('');
    }
  }

  $('#type').on('change', function(){
    toggleCalcFields();
  });

  $('#components_table').on('click', '.btnEditComponent', function(){
    const row = $(this).closest('tr');
    toEditMode(row);
  });

  $('#components_table').on('click', '.btnDeleteComponent', function(){
    const row = $(this).closest('tr');
    const id = row.data('id');
    const label = row.data('name');
    $('#component_delete_name').text(label);
    const form = document.getElementById('componentDeleteForm');
    form.action = `{{ url('payslip-components') }}/${id}`;

    const modal = new bootstrap.Modal(document.getElementById('confirmDeleteComponentModal'));
    modal.show();
  });
});
</script>
@endpush
