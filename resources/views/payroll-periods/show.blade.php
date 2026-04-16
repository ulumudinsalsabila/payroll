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
        <div class="card-toolbar">
            <button type="button" class="btn btn-warning btn-sm" id="btnFlexibleMode">
                Flexible Mode: OFF
            </button>
        </div>
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
                                        <input type="number" class="form-control form-control-sm bg-secondary readonly-deduction" name="payslips[{{ $employee->id }}][{{ $deduction->id }}]" value="{{ old('payslips.' . $employee->id . '.' . $deduction->id, $draftAmounts[$employee->id][$deduction->id] ?? '') }}" min="0" readonly data-employee-id="{{ $employee->id }}" data-component-id="{{ $deduction->id }}" data-component-name="{{ $deduction->name }}" data-percentage="{{ $deduction->percentage }}" data-max-cap="{{ $deduction->max_cap }}">
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
  const BASIC_SALARY_COMPONENT_ID = @json($basicSalaryComponentId ?? null);

  let flexibleMode = false;
  const expectedByEmployee = {};

  const calcTimers = {};

  function collectEarnings(employeeId) {
    const earnings = {};
    document.querySelectorAll('.input-earning[data-employee-id="' + employeeId + '"]').forEach(function (el) {
      const cid = el.getAttribute('data-component-id');
      earnings[cid] = el.value || 0;
    });
    return earnings;
  }

  function applyCalculation(employeeId, payload, options) {
    const opts = options || {};
    const respectExisting = !!opts.respectExisting;
    const validateAll = !!opts.validateAll;

    expectedByEmployee[employeeId] = payload || {};

    const d = payload && payload.deductions ? payload.deductions : {};
    Object.keys(d).forEach(function (componentId) {
      const el = document.querySelector('.readonly-deduction[data-employee-id="' + employeeId + '"][data-component-id="' + componentId + '"]');
      if (!el) return;
      if (flexibleMode && el.getAttribute('data-manual') === '1') return;
      if (respectExisting && el.value !== null && el.value !== '') return;
      el.value = d[componentId];
    });

    const taxEl = document.querySelector('[data-field="tax"][data-employee-id="' + employeeId + '"]');
    if (taxEl) {
      if (!(flexibleMode && taxEl.getAttribute('data-manual') === '1')) {
        if (!(respectExisting && taxEl.value !== null && taxEl.value !== '')) {
          taxEl.value = payload && payload.tax !== undefined ? payload.tax : '';
        }
      }
    }

    const nettoEl = document.querySelector('[data-field="netto"][data-employee-id="' + employeeId + '"]');
    if (nettoEl) {
      if (!(flexibleMode && nettoEl.getAttribute('data-manual') === '1')) {
        if (!(respectExisting && nettoEl.value !== null && nettoEl.value !== '')) {
          nettoEl.value = payload && payload.netto !== undefined ? payload.netto : '';
        }
      }
    }

    validateAgainstExpected(employeeId, { validateAll: validateAll });
  }

  function ensureHintEl(inputEl) {
    if (!inputEl) return null;
    let hint = inputEl.nextElementSibling;
    if (hint && hint.classList && hint.classList.contains('calc-hint')) return hint;
    hint = document.createElement('div');
    hint.className = 'calc-hint text-warning small mt-1 d-none';
    inputEl.insertAdjacentElement('afterend', hint);
    return hint;
  }

  function clearWarning(inputEl) {
    if (!inputEl) return;
    inputEl.classList.remove('border', 'border-warning', 'border-2');
    const hint = ensureHintEl(inputEl);
    if (hint) {
      hint.textContent = '';
      hint.classList.add('d-none');
    }
  }

  function showWarning(inputEl, message) {
    if (!inputEl) return;
    inputEl.classList.add('border', 'border-warning', 'border-2');
    const hint = ensureHintEl(inputEl);
    if (hint) {
      hint.textContent = message;
      hint.classList.remove('d-none');
    }
  }

  function parseIntSafe(v) {
    const n = parseInt(v, 10);
    return isNaN(n) ? 0 : n;
  }

  function expectedDeductionAmount(employeeId, inputEl) {
    const pct = parseFloat(inputEl.getAttribute('data-percentage') || '0');
    const capRaw = inputEl.getAttribute('data-max-cap');
    const cap = capRaw !== null && capRaw !== '' ? parseIntSafe(capRaw) : 0;

    let base = 0;
    let baseLabel = 'Total Pendapatan';
    if (BASIC_SALARY_COMPONENT_ID) {
      const basicEl = document.querySelector('.input-earning[data-employee-id="' + employeeId + '"][data-component-id="' + BASIC_SALARY_COMPONENT_ID + '"]');
      base = parseIntSafe(basicEl ? basicEl.value : 0);
      if (base > 0) baseLabel = 'Gaji Pokok';
    }
    if (!base) {
      const earnings = collectEarnings(employeeId);
      Object.keys(earnings).forEach(function (k) {
        base += parseIntSafe(earnings[k]);
      });
    }
    if (cap > 0) base = Math.min(base, cap);

    const expected = Math.round(base * (pct / 100));
    return {
      expected: expected,
      base: base,
      baseLabel: baseLabel,
      pct: pct,
      cap: cap
    };
  }

  function validateAgainstExpected(employeeId, options) {
    const opts = options || {};
    const validateAll = !!opts.validateAll;
    if (!employeeId) return;

    document.querySelectorAll('.readonly-deduction[data-employee-id="' + employeeId + '"]').forEach(function (el) {
      const currentRaw = el.value;
      const currentHasValue = currentRaw !== null && currentRaw !== '';

      if (flexibleMode) {
        if (el.getAttribute('data-manual') !== '1') {
          clearWarning(el);
          return;
        }
      } else {
        if (!validateAll && !currentHasValue) {
          clearWarning(el);
          return;
        }
        if (validateAll && !currentHasValue) {
          clearWarning(el);
          return;
        }
      }

      const current = parseIntSafe(el.value);
      const info = expectedDeductionAmount(employeeId, el);
      if (current === info.expected) {
        clearWarning(el);
        return;
      }

      const name = el.getAttribute('data-component-name') || 'Potongan';
      const formula = info.cap > 0
        ? `Seharusnya value ${name} adalah min(${info.baseLabel}, ${info.cap}) x ${info.pct} / 100 = ${info.expected}`
        : `Seharusnya value ${name} adalah ${info.baseLabel} x ${info.pct} / 100 = ${info.expected}`;
      showWarning(el, formula);
    });

    const expectedPayload = expectedByEmployee[employeeId] || {};
    const expTax = expectedPayload.tax;
    const expNetto = expectedPayload.netto;

    const taxEl = document.querySelector('[data-field="tax"][data-employee-id="' + employeeId + '"]');
    if (taxEl && expTax !== undefined) {
      const currentTaxRaw = taxEl.value;
      const currentTaxHasValue = currentTaxRaw !== null && currentTaxRaw !== '';
      const shouldCheckTax = flexibleMode ? (taxEl.getAttribute('data-manual') === '1') : (validateAll ? currentTaxHasValue : currentTaxHasValue);
      if (shouldCheckTax) {
        const currentTax = parseIntSafe(taxEl.value);
        if (currentTax === parseIntSafe(expTax)) {
          clearWarning(taxEl);
        } else {
          showWarning(taxEl, `Seharusnya value PPh 21 adalah ${expTax}`);
        }
      } else {
        clearWarning(taxEl);
      }
    } else {
      clearWarning(taxEl);
    }

    const nettoEl = document.querySelector('[data-field="netto"][data-employee-id="' + employeeId + '"]');
    if (nettoEl && expNetto !== undefined) {
      const currentNettoRaw = nettoEl.value;
      const currentNettoHasValue = currentNettoRaw !== null && currentNettoRaw !== '';
      const shouldCheckNetto = flexibleMode ? (nettoEl.getAttribute('data-manual') === '1') : (validateAll ? currentNettoHasValue : currentNettoHasValue);
      if (shouldCheckNetto) {
        const currentNetto = parseIntSafe(nettoEl.value);
        if (currentNetto === parseIntSafe(expNetto)) {
          clearWarning(nettoEl);
        } else {
          showWarning(nettoEl, `Seharusnya value Netto adalah Total Pendapatan - Total Potongan - PPh 21 = ${expNetto}`);
        }
      } else {
        clearWarning(nettoEl);
      }
    } else {
      clearWarning(nettoEl);
    }
  }

  function setFlexibleMode(enabled, options) {
    const opts = options || {};
    const skipRecalc = !!opts.skipRecalc;
    flexibleMode = !!enabled;
    const btn = document.getElementById('btnFlexibleMode');
    if (btn) btn.textContent = flexibleMode ? 'Flexible Mode: ON' : 'Flexible Mode: OFF';

    document.querySelectorAll('.readonly-deduction, [data-field="tax"], [data-field="netto"]').forEach(function (el) {
      el.readOnly = !flexibleMode;
      if (flexibleMode) {
        el.classList.remove('bg-secondary');
      } else {
        el.classList.add('bg-secondary');
        el.removeAttribute('data-manual');
        clearWarning(el);
      }
    });

    if (!flexibleMode && !skipRecalc) {
      const employeeIds = new Set();
      document.querySelectorAll('.input-earning[data-employee-id]').forEach(function (el) {
        employeeIds.add(el.getAttribute('data-employee-id'));
      });
      employeeIds.forEach(function (eid) {
        triggerCalc(eid);
      });
    }
  }

  const flexibleBtn = document.getElementById('btnFlexibleMode');
  if (flexibleBtn) {
    flexibleBtn.addEventListener('click', function () {
      setFlexibleMode(!flexibleMode);
    });
  }

  function requestCalc(employeeId, options) {
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
      applyCalculation(employeeId, data, options);
    })
    .catch(function () {
      // no-op
    });
  }

  function triggerCalc(employeeId) {
    if (!employeeId) return;
    if (calcTimers[employeeId]) clearTimeout(calcTimers[employeeId]);
    calcTimers[employeeId] = setTimeout(function () {
      requestCalc(employeeId);
    }, 400);
  }

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

  document.querySelectorAll('.readonly-deduction, [data-field="tax"], [data-field="netto"]').forEach(function (el) {
    el.addEventListener('input', function () {
      if (!flexibleMode) return;
      this.setAttribute('data-manual', '1');
      const employeeId = this.getAttribute('data-employee-id');
      validateAgainstExpected(employeeId);
    });
    el.addEventListener('change', function () {
      if (!flexibleMode) return;
      this.setAttribute('data-manual', '1');
      const employeeId = this.getAttribute('data-employee-id');
      validateAgainstExpected(employeeId);
    });
  });

  setFlexibleMode(false, { skipRecalc: true });

  const initEmployeeIds = new Set();
  document.querySelectorAll('.input-earning[data-employee-id]').forEach(function (el) {
    initEmployeeIds.add(el.getAttribute('data-employee-id'));
  });
  initEmployeeIds.forEach(function (eid) {
    requestCalc(eid, { respectExisting: true, validateAll: true });
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
