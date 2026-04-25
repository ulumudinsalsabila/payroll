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

    @if ($payrollPeriod->status !== 'draft')
        <div class="modal fade" id="confirmReopenDraftModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Kembalikan ke Draft</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">Yakin ingin mengubah status periode ini kembali menjadi draft? Setelah itu kamu
                            bisa mengubah data dan publish ulang.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-warning" id="btnConfirmReopenDraft">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>Kembalikan
                        </button>
                    </div>
                </div>
            </div>
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

    @php($__bulan = ['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'])

    <div class="card mb-5">
        <div class="card-header">
            <div class="card-title">
                <div>
                    <div class="fw-bold">Periode: {{ $__bulan[$payrollPeriod->month] ?? $payrollPeriod->month }}
                        {{ $payrollPeriod->year }}</div>
                    <div class="text-muted">
                        Status:
                        <span
                            class="badge bg-light-{{ $payrollPeriod->status === 'draft' ? 'warning' : 'success' }} text-{{ $payrollPeriod->status === 'draft' ? 'warning' : 'success' }}">
                            {{ $payrollPeriod->status }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-toolbar">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="btn-group">
                        <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="bi bi-download me-2"></i>Download Template
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item"
                                    href="{{ route('payroll-periods.download-template', ['payroll_period' => $payrollPeriod->id, 'mode' => 'empty']) }}">Template
                                    Kosong</a></li>
                            <li><a class="dropdown-item"
                                    href="{{ route('payroll-periods.download-template', ['payroll_period' => $payrollPeriod->id, 'mode' => 'last_period']) }}">Isi
                                    dengan Data Bulan Lalu</a></li>
                        </ul>
                    </div>

                    @if ($payrollPeriod->status === 'draft')
                        <button type="button" class="btn btn-info" data-bs-toggle="modal"
                            data-bs-target="#importExcelModal">
                            <i class="bi bi-file-earmark-spreadsheet me-2"></i>Import Excel
                        </button>
                    @endif

                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal"
                        data-bs-target="#previewPdfModal">
                        <i class="bi bi-eye me-2"></i>Preview PDF
                    </button>

                    @if ($payrollPeriod->status === 'draft')
                        <button type="button" class="btn btn-success" id="btnPublishPeriod">
                            <i class="bi bi-send-check me-2"></i>Publish &amp; Send PDF
                        </button>
                        <form id="publishSendForm" method="POST"
                            action="{{ route('payroll-periods.publish-send', ['payroll_period' => $payrollPeriod->id]) }}"
                            class="d-none">
                            @csrf
                        </form>
                    @else
                        <button type="button" class="btn btn-warning" id="btnReopenDraft">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>Kembalikan ke Draft
                        </button>
                        <form id="reopenDraftForm" method="POST"
                            action="{{ route('payroll-periods.reopen-draft', ['payroll_period' => $payrollPeriod->id]) }}"
                            class="d-none">
                            @csrf
                        </form>
                    @endif
                </div>
            </div>
        </div>
        <div class="card-body">
            <div><strong>Deskripsi :</strong> {{ $payrollPeriod->description ?: '-' }}</div>
            <div class="mt-2"><strong>Default Hari Kerja :</strong> {{ $payrollPeriod->default_work_days ?? 25 }} hari</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Input Gaji Massal ({{ $payrollPeriod->status === 'draft' ? 'Draft' : 'Published' }})
            </h3>
            <div class="card-toolbar">
                <button type="button" class="btn btn-warning btn-sm" id="btnFlexibleMode" @disabled($payrollPeriod->status !== 'draft')>
                    Flexible Mode: OFF
                </button>
            </div>
        </div>
        <form action="{{ route('payroll-periods.save-draft', $payrollPeriod->id) }}" method="POST">
            @csrf
            <div class="card-body">
                @foreach ($employees as $employee)
                    <div class="card mb-3 employee-card" data-employee-id="{{ $employee->id }}">
                        <div class="card-header d-flex justify-content-between align-items-center py-2" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#employeeCollapse{{ $employee->id }}" aria-expanded="false">
                            <div class="text-start">
                                <h4 class="text-primary fw-bold text-14 mb-0">{{ $employee->name }}</h4>
                            </div>
                            <div class="text-end d-flex align-items-center">
                                <h4 class="text-primary fw-bold text-14 me-3 mb-0" id="totalGaji{{ $employee->id }}">Total Gaji : Rp {{ number_format($draftNetto[$employee->id] ?? 0, 0, ',', '.') }}</h4>
                                <i class="bi bi-chevron-down collapse-icon" id="collapseIcon{{ $employee->id }}"></i>
                            </div>
                        </div>
                        <div class="collapse pt-4" id="employeeCollapse{{ $employee->id }}">
                            <div class="card-body pt-0">
                                <div class="row g-2">
                                    <div class="col-3">
                                        <label class="form-label small">Masuk Hari Kerja</label>
                                        <input type="number" class="form-control form-control-sm"
                                            name="work_days[{{ $employee->id }}]"
                                            value="{{ old('work_days.' . $employee->id, $draftWorkDays[$employee->id] ?? '') }}"
                                            data-employee-id="{{ $employee->id }}"
                                            min="0" @disabled($payrollPeriod->status !== 'draft')>
                                </div>
                                
                                @if ($earnings->count() > 0)
                                    <div class="col-12">
                                        <h5 class="text-success mt-2">Pendapatan</h5>
                                    </div>
                                    @foreach ($earnings as $earning)
                                        <div class="col-3">
                                            <label class="form-label small">{{ $earning->name }}</label>
                                            <input type="number" class="form-control form-control-sm input-earning"
                                                name="payslips[{{ $employee->id }}][{{ $earning->id }}]"
                                                value="{{ old('payslips.' . $employee->id . '.' . $earning->id, $draftAmounts[$employee->id][$earning->id] ?? '') }}"
                                                min="0" data-employee-id="{{ $employee->id }}"
                                                data-component-id="{{ $earning->id }}" {{ $loop->first ? 'readonly' : '' }} @disabled($payrollPeriod->status !== 'draft')>
                                        </div>
                                    @endforeach
                                @endif
                                
                                @if ($deductions->count() > 0)
                                    <div class="col-12">
                                        <h5 class="text-danger mt-2">Potongan</h5>
                                    </div>
                                    @foreach ($deductions as $deduction)
                                        <div class="col-3">
                                            <label class="form-label small">{{ $deduction->name }}</label>
                                            <input type="number" class="form-control form-control-sm"
                                                name="payslips[{{ $employee->id }}][{{ $deduction->id }}]"
                                                value="{{ old('payslips.' . $employee->id . '.' . $deduction->id, $draftAmounts[$employee->id][$deduction->id] ?? '') }}"
                                                min="0" data-employee-id="{{ $employee->id }}"
                                                data-component-id="{{ $deduction->id }}"
                                                data-component-name="{{ $deduction->name }}" @disabled($payrollPeriod->status !== 'draft')>
                                        </div>
                                    @endforeach
                                @endif
                                
                                @if ($taxes->count() > 0)
                                    <div class="col-12">
                                        <h5 class="text-warning mb-2">Pajak</h5>
                                    </div>
                                    @foreach ($taxes as $tax)
                                        <div class="col-3">
                                            <label class="form-label small">{{ $tax->name }}</label>
                                            <input type="number"
                                                class="form-control form-control-sm bg-secondary readonly-tax"
                                                name="payslips[{{ $employee->id }}][{{ $tax->id }}]"
                                                value="{{ old('payslips.' . $employee->id . '.' . $tax->id, $draftAmounts[$employee->id][$tax->id] ?? '') }}"
                                                min="0" readonly data-employee-id="{{ $employee->id }}"
                                                data-component-id="{{ $tax->id }}"
                                                data-component-name="{{ $tax->name }}" data-field="tax_component"
                                                @disabled($payrollPeriod->status !== 'draft')>
                                        </div>
                                    @endforeach
                                @endif
                                
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-primary">Netto</label>
                                    <input type="number" class="form-control form-control-sm bg-secondary fw-bold"
                                        name="netto[{{ $employee->id }}]"
                                        value="{{ old('netto.' . $employee->id, $draftNetto[$employee->id] ?? '') }}"
                                        min="0" readonly data-employee-id="{{ $employee->id }}"
                                        data-field="netto" @disabled($payrollPeriod->status !== 'draft')>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if ($payrollPeriod->status === 'draft')
                <div class="card-footer d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Simpan Draft</button>
                </div>
            @endif
        </form>
    </div>

    <div class="modal fade" id="importExcelModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('payroll-periods.import-template', $payrollPeriod->id) }}"
                    enctype="multipart/form-data">
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

    <div class="modal fade" id="previewPdfModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Preview PDF Payslip</h5>
                    <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pilih Karyawan</label>
                        <select class="form-select" id="preview_employee_id">
                            <option value="">-- pilih --</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->employee_code }} -
                                    {{ $employee->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Preview berdasarkan draft yang sudah tersimpan.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" id="btnDoPreviewPdf">Preview</button>
                </div>
            </div>
        </div>
    </div>

    @if ($payrollPeriod->status === 'draft')
        <div class="modal fade" id="confirmPublishModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Publish &amp; Send PDF</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">Yakin ingin publish periode ini dan mengirim PDF? Aksi ini bersifat final.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-success" id="btnConfirmPublishSend">
                            <i class="bi bi-send-check me-2"></i>Publish
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.querySelectorAll('.alert.alert-dismissible').forEach(function(el) {
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
            const previewPdfUrl = '{{ route('payroll-periods.preview-pdf', $payrollPeriod->id) }}';
            const IS_DRAFT = @json($payrollPeriod->status === 'draft');

            let flexibleMode = false;
            const expectedByEmployee = {};

            const calcTimers = {};

            function collectEarnings(employeeId) {
                const earnings = {};
                document.querySelectorAll('.input-earning[data-employee-id="' + employeeId + '"]').forEach(function(
                    el) {
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
                Object.keys(d).forEach(function(componentId) {
                    const el = document.querySelector('.readonly-deduction[data-employee-id="' +
                        employeeId + '"][data-component-id="' + componentId + '"]');
                    if (!el) return;
                    if (flexibleMode && el.getAttribute('data-manual') === '1') return;
                    if (respectExisting && el.value !== null && el.value !== '') return;
                    el.value = d[componentId];
                });

                const t = payload && payload.taxes ? payload.taxes : {};
                Object.keys(t).forEach(function(componentId) {
                    const el = document.querySelector('.readonly-tax[data-employee-id="' + employeeId +
                        '"][data-component-id="' + componentId + '"]');
                    if (!el) return;
                    if (flexibleMode && el.getAttribute('data-manual') === '1') return;
                    if (respectExisting && el.value !== null && el.value !== '') return;
                    el.value = t[componentId];
                });

                const nettoEl = document.querySelector('[data-field="netto"][data-employee-id="' + employeeId +
                    '"]');
                if (nettoEl) {
                    if (!(flexibleMode && nettoEl.getAttribute('data-manual') === '1')) {
                        if (!(respectExisting && nettoEl.value !== null && nettoEl.value !== '')) {
                            nettoEl.value = payload && payload.netto !== undefined ? payload.netto : '';
                        }
                    }
                }

                validateAgainstExpected(employeeId, {
                    validateAll: validateAll
                });
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

            function calculateNetto(employeeId) {
                let totalEarnings = 0;
                let totalDeductions = 0;
                let totalTax = 0;

                document.querySelectorAll('.input-earning[data-employee-id="' + employeeId + '"]').forEach(function(el) {
                    totalEarnings += parseIntSafe(el.value);
                });

                document.querySelectorAll('input[data-employee-id="' + employeeId + '"][data-component-id]').forEach(function(el) {
                    const componentId = el.getAttribute('data-component-id');
                    const componentType = getComponentType(componentId);
                    if (componentType === 'deduction') {
                        totalDeductions += parseIntSafe(el.value);
                    } else if (componentType === 'tax') {
                        totalTax += parseIntSafe(el.value);
                    }
                });

                // Get work days and default work days
                const workDaysEl = document.querySelector('[name="work_days[' + employeeId + ']"]');
                const workDays = workDaysEl ? parseIntSafe(workDaysEl.value) : 0;
                const defaultWorkDays = {{ $payrollPeriod->default_work_days ?? 25 }};
                
                // Calculate and update netto
                let netto = totalEarnings - totalDeductions - totalTax;
                
                // Apply potongan jika kurang dari default hari kerja
                if (workDays < defaultWorkDays && workDays > 0) {
                    // Get basic salary untuk perhitungan potongan
                    const basicSalaryEl = document.querySelector('.input-earning[data-employee-id="' + employeeId + '"][readonly]');
                    let basicSalary = 0;
                    if (basicSalaryEl) {
                        basicSalary = parseIntSafe(basicSalaryEl.value);
                    } else {
                        // Fallback ke earning pertama jika tidak ada readonly
                        const firstEarningEl = document.querySelector('.input-earning[data-employee-id="' + employeeId + '"]');
                        if (firstEarningEl) {
                            basicSalary = parseIntSafe(firstEarningEl.value);
                        }
                    }
                    
                    // Hitung potongan per hari tidak masuk
                    const potonganPerHari = basicSalary > 0 ? Math.round(basicSalary / defaultWorkDays) : 0;
                    const totalPotongan = (defaultWorkDays - workDays) * potonganPerHari;
                    
                    // Kurangi netto dengan potongan
                    netto = netto - totalPotongan;
                }
                
                const nettoEl = document.querySelector('[data-field="netto"][data-employee-id="' + employeeId + '"]');
                if (nettoEl) {
                    nettoEl.value = netto;
                }
                
                // Update total gaji di header
                const totalGajiEl = document.getElementById('totalGaji' + employeeId);
                if (totalGajiEl) {
                    totalGajiEl.textContent = 'Total Gaji : Rp ' + netto.toLocaleString('id-ID');
                }
            }

            function getComponentType(componentId) {
                // This would ideally come from a data attribute or global variable
                // For now, we'll determine based on the input's class or context
                const el = document.querySelector('[data-component-id="' + componentId + '"]');
                if (el) {
                    if (el.classList.contains('input-earning')) return 'earning';
                    if (el.classList.contains('readonly-tax')) return 'tax';
                    // Default to deduction for other inputs
                    return 'deduction';
                }
                return 'deduction';
            }


            function setFlexibleMode(enabled, options) {
                const opts = options || {};
                const skipRecalc = !!opts.skipRecalc;
                flexibleMode = !!enabled;
                const btn = document.getElementById('btnFlexibleMode');
                if (btn) btn.textContent = flexibleMode ? 'Flexible Mode: ON' : 'Flexible Mode: OFF';

                document.querySelectorAll('.readonly-tax, [data-field="netto"]').forEach(
                    function(el) {
                        el.readOnly = !flexibleMode;
                        if (flexibleMode) {
                            el.classList.remove('bg-secondary');
                            el.setAttribute('data-manual', '0');
                        } else {
                            el.classList.add('bg-secondary');
                            el.removeAttribute('data-manual');
                        }
                    });

                if (!flexibleMode && !skipRecalc) {
                    const employeeIds = new Set();
                    document.querySelectorAll('.input-earning[data-employee-id]').forEach(function(el) {
                        employeeIds.add(el.getAttribute('data-employee-id'));
                    });
                    employeeIds.forEach(function(eid) {
                        triggerCalc(eid);
                    });
                }
            }

            const flexibleBtn = document.getElementById('btnFlexibleMode');
            if (flexibleBtn) {
                flexibleBtn.addEventListener('click', function() {
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
                    .then(async function(resp) {
                        const data = await resp.json().catch(function() {
                            return {};
                        });
                        if (!resp.ok) throw data;
                        applyCalculation(employeeId, data, options);
                    })
                    .catch(function() {
                        // no-op
                    });
            }

            function triggerCalc(employeeId) {
                if (!employeeId) return;
                if (calcTimers[employeeId]) clearTimeout(calcTimers[employeeId]);
                calcTimers[employeeId] = setTimeout(function() {
                    requestCalc(employeeId);
                }, 400);
            }

            // Add event listeners for work days inputs
            document.querySelectorAll('[name^="work_days["]').forEach(function(el) {
                const defaultWorkDays = {{ $payrollPeriod->default_work_days ?? 25 }};
                
                // Set max attribute to default work days
                el.setAttribute('max', defaultWorkDays);
                
                el.addEventListener('input', function() {
                    const employeeId = this.getAttribute('data-employee-id');
                    if (!employeeId) return;
                    
                    // Validate max value
                    const value = parseIntSafe(this.value);
                    if (value > defaultWorkDays) {
                        this.value = defaultWorkDays;
                    }
                    
                    calculateNetto(employeeId);
                });
                el.addEventListener('change', function() {
                    const employeeId = this.getAttribute('data-employee-id');
                    if (!employeeId) return;
                    
                    // Validate max value
                    const value = parseIntSafe(this.value);
                    if (value > defaultWorkDays) {
                        this.value = defaultWorkDays;
                    }
                    
                    calculateNetto(employeeId);
                });
            });

            document.querySelectorAll('.input-earning').forEach(function(el) {
                el.addEventListener('input', function() {
                    const employeeId = this.getAttribute('data-employee-id');
                    if (!employeeId) return;
                    calculateNetto(employeeId);
                });
                el.addEventListener('change', function() {
                    const employeeId = this.getAttribute('data-employee-id');
                    if (!employeeId) return;
                    calculateNetto(employeeId);
                });
            });

            // Add event listeners for deduction inputs
            document.querySelectorAll('input[data-employee-id][data-component-id]').forEach(function(el) {
                // Skip earnings and tax inputs, only handle deductions
                if (el.classList.contains('input-earning') || el.classList.contains('readonly-tax')) return;
                
                el.addEventListener('input', function() {
                    const employeeId = this.getAttribute('data-employee-id');
                    if (!employeeId) return;
                    calculateNetto(employeeId);
                });
                el.addEventListener('change', function() {
                    const employeeId = this.getAttribute('data-employee-id');
                    if (!employeeId) return;
                    calculateNetto(employeeId);
                });
            });

            // Add event listeners for tax inputs
            document.querySelectorAll('.readonly-tax').forEach(function(el) {
                el.addEventListener('input', function() {
                    if (!flexibleMode) return;
                    this.setAttribute('data-manual', '1');
                    const employeeId = this.getAttribute('data-employee-id');
                    if (employeeId) calculateNetto(employeeId);
                });
                el.addEventListener('change', function() {
                    if (!flexibleMode) return;
                    this.setAttribute('data-manual', '1');
                    const employeeId = this.getAttribute('data-employee-id');
                    if (employeeId) calculateNetto(employeeId);
                });
            });

            // Handle netto input (keep manual override functionality)
            document.querySelectorAll('[data-field="netto"]').forEach(function(el) {
                el.addEventListener('input', function() {
                    if (!flexibleMode) return;
                    this.setAttribute('data-manual', '1');
                    const employeeId = this.getAttribute('data-employee-id');
                });
                el.addEventListener('change', function() {
                    if (!flexibleMode) return;
                    this.setAttribute('data-manual', '1');
                    const employeeId = this.getAttribute('data-employee-id');
                });
            });

            if (IS_DRAFT) {
                setFlexibleMode(false, {
                    skipRecalc: true
                });

                const initEmployeeIds = new Set();
                // Get employee IDs from both desktop and mobile views
                document.querySelectorAll('.input-earning[data-employee-id]').forEach(function(el) {
                    initEmployeeIds.add(el.getAttribute('data-employee-id'));
                });
                document.querySelectorAll('.employee-card[data-employee-id]').forEach(function(el) {
                    initEmployeeIds.add(el.getAttribute('data-employee-id'));
                });
                initEmployeeIds.forEach(function(eid) {
                    // Calculate initial netto for all employees
                    calculateNetto(eid);
                });
            }

            const publishBtn = document.getElementById('btnPublishPeriod');
            if (publishBtn) {
                publishBtn.addEventListener('click', function() {
                    const modalEl = document.getElementById('confirmPublishModal');
                    if (!modalEl) return;
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                });
            }

            const btnConfirmPublishSend = document.getElementById('btnConfirmPublishSend');
            if (btnConfirmPublishSend) {
                btnConfirmPublishSend.addEventListener('click', function() {
                    const modalEl = document.getElementById('confirmPublishModal');
                    if (modalEl) {
                        try {
                            const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
                            modalInstance.hide();
                        } catch (e) {}
                    }

                    btnConfirmPublishSend.disabled = true;
                    if (publishBtn) publishBtn.disabled = true;

                    const form = document.getElementById('publishSendForm');
                    if (!form) return;

                    if (typeof Swal === 'undefined') {
                        form.submit();
                        return;
                    }

                    Swal.fire({
                        title: 'Memproses Pengiriman...',
                        text: 'Sedang men-generate PDF dan mengirim email. Mohon jangan tutup halaman ini.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                            form.submit();
                        }
                    });
                });
            }

            const reopenBtn = document.getElementById('btnReopenDraft');
            if (reopenBtn) {
                reopenBtn.addEventListener('click', function() {
                    const modalEl = document.getElementById('confirmReopenDraftModal');
                    if (!modalEl) return;
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                });
            }

            const btnConfirmReopenDraft = document.getElementById('btnConfirmReopenDraft');
            if (btnConfirmReopenDraft) {
                btnConfirmReopenDraft.addEventListener('click', function() {
                    btnConfirmReopenDraft.disabled = true;
                    if (reopenBtn) reopenBtn.disabled = true;
                    const form = document.getElementById('reopenDraftForm');
                    if (form) form.submit();
                });
            }

            const btnDoPreviewPdf = document.getElementById('btnDoPreviewPdf');
            if (btnDoPreviewPdf) {
                btnDoPreviewPdf.addEventListener('click', function() {
                    const sel = document.getElementById('preview_employee_id');
                    const employeeId = sel ? sel.value : '';
                    if (!employeeId) {
                        alert('Silakan pilih karyawan terlebih dahulu.');
                        return;
                    }
                    const url = previewPdfUrl + '?employee_id=' + encodeURIComponent(employeeId);
                    window.open(url, '_blank');
                });
            }

            // Add event listeners for collapse toggle icons
            document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(function(element) {
                element.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-bs-target');
                    const iconId = targetId.replace('#employeeCollapse', '#collapseIcon');
                    const icon = document.querySelector(iconId);
                    
                    if (icon) {
                        const collapseElement = document.querySelector(targetId);
                        if (collapseElement) {
                            collapseElement.addEventListener('show.bs.collapse', function() {
                                icon.classList.remove('bi-chevron-down');
                                icon.classList.add('bi-chevron-up');
                            });
                            
                            collapseElement.addEventListener('hide.bs.collapse', function() {
                                icon.classList.remove('bi-chevron-up');
                                icon.classList.add('bi-chevron-down');
                            });
                        }
                    }
                });
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Swal === 'undefined') return;

            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Proses Selesai!',
                    text: @json(session('success')),
                    confirmButtonText: 'Tutup',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    }
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan!',
                    text: @json(session('error')),
                    confirmButtonText: 'Tutup',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    }
                });
            @endif
        });
    </script>
@endpush
