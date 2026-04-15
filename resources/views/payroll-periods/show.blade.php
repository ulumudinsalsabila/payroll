@extends('layouts.master')

@section('page_title', 'Detail Periode Gaji')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
    <h3 class="fw-bold mb-0">Detail Periode Gaji</h3>
    <a href="{{ route('payroll-periods.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Kembali</a>
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

@php($__bulan = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'])

<div class="card mb-5">
    <div class="card-header">
        <div class="card-title">
            <div>
                <div class="fw-bold">Periode: {{ $__bulan[$payrollPeriod->month] ?? $payrollPeriod->month }} {{ $payrollPeriod->year }}</div>
                <div class="text-muted">
                    Status:
                    <span class="badge bg-light-{{ $payrollPeriod->status === 'draft' ? 'warning' : 'success' }} text-{{ $payrollPeriod->status === 'draft' ? 'warning' : 'success' }}">
                        {{ $payrollPeriod->status }}
                    </span>
                </div>
            </div>
        </div>
        <div class="card-toolbar">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="btn-group">
                    <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-download me-2"></i>Download Template
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#">Template Kosong</a></li>
                        <li><a class="dropdown-item" href="#">Isi dengan Data Bulan Lalu</a></li>
                    </ul>
                </div>

                <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#importExcelModal">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i>Import Excel
                </button>

                @if ($payrollPeriod->status === 'draft')
                    <button type="button" class="btn btn-success" id="btnPublishPeriod">
                        <i class="bi bi-send-check me-2"></i>Publish &amp; Send PDF
                    </button>
                @endif
            </div>
        </div>
    </div>
    <div class="card-body">
        <div><strong>Deskripsi:</strong><br>{{ $payrollPeriod->description ?: '-' }}</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Input Gaji Massal (Draft)</h3>
    </div>
    <form action="{{ route('payroll-periods.save-draft', $payrollPeriod->id) }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <tr>
                            <th style="min-width: 220px;">Nama Karyawan</th>
                            <th style="min-width: 140px;">Hari Kerja</th>
                            @foreach($earnings as $earning)
                                <th style="min-width: 160px;">{{ $earning->name }}</th>
                            @endforeach
                            @foreach($deductions as $deduction)
                                <th style="min-width: 160px;">{{ $deduction->name }}</th>
                            @endforeach
                            <th style="min-width: 160px;">PPh 21</th>
                            <th style="min-width: 160px;">Netto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                            <tr>
                                <td class="fw-semibold">{{ $employee->name }}</td>
                                <td>
                                    <input type="number" class="form-control form-control-sm" name="work_days[{{ $employee->id }}]" value="{{ old('work_days.' . $employee->id, $draftWorkDays[$employee->id] ?? '') }}" min="0">
                                </td>

                                @foreach($earnings as $earning)
                                    <td>
                                        <input type="number" class="form-control form-control-sm input-earning" name="payslips[{{ $employee->id }}][{{ $earning->id }}]" value="{{ old('payslips.' . $employee->id . '.' . $earning->id, $draftAmounts[$employee->id][$earning->id] ?? '') }}" min="0" data-employee-id="{{ $employee->id }}" data-component-id="{{ $earning->id }}">
                                    </td>
                                @endforeach

                                @foreach($deductions as $deduction)
                                    <td>
                                        <input type="number" class="form-control form-control-sm bg-secondary readonly-deduction" name="payslips[{{ $employee->id }}][{{ $deduction->id }}]" value="{{ old('payslips.' . $employee->id . '.' . $deduction->id, $draftAmounts[$employee->id][$deduction->id] ?? '') }}" min="0" readonly data-employee-id="{{ $employee->id }}" data-component-id="{{ $deduction->id }}">
                                    </td>
                                @endforeach

                                <td>
                                    <input type="number" class="form-control form-control-sm bg-secondary" name="tax[{{ $employee->id }}]" value="{{ old('tax.' . $employee->id, $draftTax[$employee->id] ?? '') }}" min="0" readonly data-employee-id="{{ $employee->id }}" data-field="tax">
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm bg-secondary fw-bold" name="netto[{{ $employee->id }}]" value="{{ old('netto.' . $employee->id, $draftNetto[$employee->id] ?? '') }}" min="0" readonly data-employee-id="{{ $employee->id }}" data-field="netto">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">Simpan Draft</button>
        </div>
    </form>
</div>

<div class="modal fade" id="importExcelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="#" onsubmit="return false;">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Excel</h5>
                    <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">File Excel</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx, .xls" required>
                        <div class="form-text">Format file harus sesuai template.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

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

  const CSRF_TOKEN = '{{ csrf_token() }}';
  const calcUrl = '{{ route('payroll-periods.calculate-row') }}';

  function debounce(fn, wait) {
    let t;
    return function () {
      const ctx = this;
      const args = arguments;
      clearTimeout(t);
      t = setTimeout(function () {
        fn.apply(ctx, args);
      }, wait);
    };
  }

  function collectEarnings(employeeId) {
    const earnings = {};
    document.querySelectorAll('.input-earning[data-employee-id="' + employeeId + '"]').forEach(function (el) {
      const cid = el.getAttribute('data-component-id');
      earnings[cid] = el.value || 0;
    });
    return earnings;
  }

  function applyCalculation(employeeId, payload) {
    const d = payload && payload.deductions ? payload.deductions : {};
    Object.keys(d).forEach(function (componentId) {
      const el = document.querySelector('.readonly-deduction[data-employee-id="' + employeeId + '"][data-component-id="' + componentId + '"]');
      if (el) el.value = d[componentId];
    });

    const taxEl = document.querySelector('[data-field="tax"][data-employee-id="' + employeeId + '"]');
    if (taxEl) taxEl.value = payload && payload.tax !== undefined ? payload.tax : '';

    const nettoEl = document.querySelector('[data-field="netto"][data-employee-id="' + employeeId + '"]');
    if (nettoEl) nettoEl.value = payload && payload.netto !== undefined ? payload.netto : '';
  }

  const triggerCalc = debounce(function (employeeId) {
    fetch(calcUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': CSRF_TOKEN,
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        employee_id: employeeId,
        earnings: collectEarnings(employeeId)
      })
    })
    .then(async function (resp) {
      const data = await resp.json().catch(function(){ return {}; });
      if (!resp.ok) throw data;
      applyCalculation(employeeId, data);
    })
    .catch(function () {
      // no-op
    });
  }, 400);

  document.querySelectorAll('.input-earning').forEach(function (el) {
    el.addEventListener('input', function () {
      const employeeId = this.getAttribute('data-employee-id');
      if (!employeeId) return;
      triggerCalc(employeeId);
    });
    el.addEventListener('change', function () {
      const employeeId = this.getAttribute('data-employee-id');
      if (!employeeId) return;
      triggerCalc(employeeId);
    });
  });

  const publishBtn = document.getElementById('btnPublishPeriod');
  if (publishBtn) {
    publishBtn.addEventListener('click', function () {
      const ok = confirm('Yakin ingin publish periode ini dan mengirim PDF? Aksi ini bersifat final.');
      if (!ok) return;
      alert('Fitur Publish & Send PDF belum diaktifkan.');
    });
  }
});
</script>
@endpush
