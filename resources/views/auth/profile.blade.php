@extends('layouts.master')

@section('page_title', 'Profil Saya')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <div>
            <h3 class="fw-bold mb-0">Profil Pengguna</h3>
            <span class="text-muted">Perbarui informasi dasar, foto, dan password akun Anda.</span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-5">
        <div class="col-lg-4">
            <div class="card card-flush h-100">
                <div class="card-body text-center">
                    @php
                        $fallbackAvatar = asset('assets/media/avatars/blank.png');
                        $photoPath = trim((string) ($user->photo ?? ''));
                        $photoPath = ltrim($photoPath, '/');
                        $photoPath = preg_replace('/^storage\//', '', $photoPath);
                        $photoUrl = !empty($photoPath) ? url('storage/profile/' . $photoPath) : null;
                    @endphp
                    <div class="image-input image-input-outline" data-kt-image-input="true" style="background-image: url('{{ $fallbackAvatar }}')">
                        <div class="image-input-wrapper w-150px h-150px" style="background-image: url('{{ $photoUrl ?? $fallbackAvatar }}');"></div>
                        <label class="btn btn-icon btn-circle btn-active-color-primary w-35px h-35px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Ganti foto">
                            <i class="bi bi-pencil-fill fs-7"></i>
                            <input type="file" name="photo" accept=".png, .jpg, .jpeg" form="profile_form" />
                            <input type="hidden" name="photo_remove" />
                        </label>
                        <span class="btn btn-icon btn-circle btn-active-color-primary w-35px h-35px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Batalkan">
                            <i class="bi bi-x fs-2"></i>
                        </span>
                    </div>
                    <h4 class="fw-bold mt-4 mb-1">{{ $user->name }}</h4>
                    <div class="text-muted mb-4 text-uppercase">{{ $user->role ?? '-' }}</div>
                    <div class="border-top pt-4 text-start">
                        <div class="mb-3">
                            <span class="text-muted d-block">Email</span>
                            <span class="fw-semibold">{{ $user->email }}</span>
                        </div>
                        <div>
                            <span class="text-muted d-block">Terakhir diperbarui</span>
                            <span class="fw-semibold">{{ optional($user->updated_at)->format('d M Y H:i') ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card card-flush h-100">
                <div class="card-header">
                    <div class="card-title">
                        <h4 class="fw-bold mb-0">Informasi Akun</h4>
                    </div>
                </div>
                <div class="card-body">
                    <form id="profile_form" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row g-5">
                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-5 mt-1">
                            <div class="col-md-6">
                                <label class="form-label">Nomor Telepon</label>
                                <input type="text" name="telepon" value="{{ old('telepon', $user->telepon) }}" class="form-control @error('telepon') is-invalid @enderror" placeholder="Contoh: 0812xxx">
                                @error('telepon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password Baru</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Kosongkan jika tidak diubah">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-5 mt-1">
                            <div class="col-md-6">
                                <label class="form-label">Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password baru">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-5">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
