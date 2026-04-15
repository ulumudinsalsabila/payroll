@extends('layouts.master')

@section('page_title', 'Detail Periode Gaji')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
    <h3 class="fw-bold mb-0">Detail Periode Gaji</h3>
    <a href="{{ route('payroll-periods.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<div class="row g-5">
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-body">
                <div class="mb-3"><strong>Bulan:</strong> {{ $payrollPeriod->month }}</div>
                <div class="mb-3"><strong>Tahun:</strong> {{ $payrollPeriod->year }}</div>
                <div class="mb-3"><strong>Status:</strong> <span class="badge bg-light-{{ $payrollPeriod->status === 'draft' ? 'warning' : 'success' }} text-{{ $payrollPeriod->status === 'draft' ? 'warning' : 'success' }}">{{ $payrollPeriod->status }}</span></div>
                <div class="mb-0"><strong>Deskripsi:</strong><br>{{ $payrollPeriod->description }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-5">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">Data Payslip</h5>
            <button class="btn btn-primary"><i class="bi bi-file-earmark-spreadsheet"></i> Import Excel Data Gaji</button>
        </div>
        <div class="text-muted">Tabel Data Payslip Karyawan akan muncul di sini</div>
    </div>
</div>
@endsection
