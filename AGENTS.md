# Agents.md — Pedoman Umum Pengembangan Laravel 10 (Template)

Dokumen ini adalah **aturan wajib** (system prompt) untuk seluruh AI agent dan developer manusia yang bekerja pada sebuah codebase **Laravel 10**.

Target utama:
- Menjaga **konsistensi UI/UX** (Bootstrap + Metronic atau template setara) di seluruh modul.
- Menjaga konsistensi **pola CRUD berbasis Modal** (bukan halaman create/edit terpisah) untuk master data.
- Menjaga konsistensi **pola DataTables** untuk listing.
- Menjaga konsistensi **pola Controller** (resource controller, validasi via `$request->validate()`), serta **flash message** / JSON response.
- Menjaga konsistensi **kontrol akses** (statis berbasis role atau dinamis berbasis permission/policy) sesuai strategi yang dipilih proyek.

---
## Daftar Isi

- [0) Ringkasan Tech Stack & UI Kit (Laravel 10)](#0-ringkasan-tech-stack--ui-kit-laravel-10)
- [1) Pola Frontend & Implementasi UI](#1-pola-frontend--implementasi-ui)
  - [1.1 Struktur Blade, Layout Utama, dan Slot](#11-struktur-blade-layout-utama-dan-slot)
  - [1.2 Pola CRUD di UI: Modal Create/Edit + Konfirmasi Delete](#12-pola-crud-di-ui-modal-createedit--konfirmasi-delete)
  - [1.3 Pola Data Display: DataTables](#13-pola-data-display-datatables)
  - [1.4 Komponen UI Kustom / Kompleks](#14-komponen-ui-kustom--kompleks)
- [2) Penanganan JavaScript & AJAX](#2-penanganan-javascript--ajax)
  - [2.5 Tips Blade & JSON pada Atribut HTML](#25-tips-blade--json-pada-atribut-html)
- [3) Notifikasi UI](#3-notifikasi-ui)
- [4) Kontrol Akses](#4-kontrol-akses-general)
  - [4.1 Kontrol Akses Statis (Role Tetap)](#41-kontrol-akses-statis-role-tetap)
  - [4.2 Kontrol Akses Dinamis (Permission/Policy)](#42-kontrol-akses-dinamis-permissionpolicy)
- [5) Standar System Logs](#5-standar-system-logs)
- [6) Konvensi Khusus Proyek (Checklist)](#6-konvensi-khusus-proyek-checklist)

# 0) Ringkasan Tech Stack & UI Kit (Laravel 10)

- **Backend**
  - Laravel `^10.x`.
  - PHP `^8.1` (atau mengikuti requirement Laravel 10 yang kamu pakai).
  - Paket tambahan (opsional, sesuai kebutuhan proyek), contoh: `maatwebsite/excel` untuk export.

- **Frontend build**
  - Vite (`vite.config.js`) dengan entry umum:
    - `resources/css/app.css`
    - `resources/js/app.js`
  - Boleh memakai `axios`, **Fetch**, atau **jQuery AJAX**. Di pedoman ini, contoh utama untuk AJAX memakai **Fetch**.

- **UI Framework / Template**
  - Disarankan menggunakan Bootstrap 5 + template admin (mis. Metronic) agar komponen UI konsisten.
  - Vendor umum:
    - DataTables
    - FullCalendar (opsional)
  - Ikon: Bootstrap Icons (atau icon set lain yang konsisten).

**Aturan:** setiap halaman baru wajib `@extends('layouts.master')` (atau layout utama proyek) dan meletakkan JS halaman pada `@push('scripts')`.

Tambahan aturan konsistensi proyek (wajib):
- Password default untuk user role `crew` saat dibuat oleh Admin: `Qwerty123*`.
- Seluruh URL file lampiran (storage publik) menggunakan `url('storage/...')`, BUKAN `asset()`, agar tidak terpengaruh `ASSET_URL` pada `.env`.

---

# 1) POLA FRONTEND & IMPLEMENTASI UI

## 1.1 Struktur Blade, Layout Utama, dan Slot

- **Layout utama:** `resources/views/layouts/master.blade.php`
  - Halaman menaruh konten utama pada `@yield('content')`.
  - Halaman menambahkan CSS/JS lokal via `@stack('styles')` dan `@stack('scripts')`.
  - Sidebar dipanggil via `@include('layouts.sidebar')`.

**Snippet (layout master):**
```blade
<!-- resources/views/layouts/master.blade.php -->
<link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" />

...
<div class="app-container container-fluid py-5">
    @yield('content')
</div>

...
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>

@stack('scripts')
```

**Aturan implementasi view:**
- **Gunakan header halaman yang konsisten**:
  - Judul di kiri.
  - Tombol aksi utama di kanan.
- **Gunakan komponen Metronic/Bootstrap**:
  - `card`, `card-body`, `table-responsive`, `btn btn-primary`, dll.
- **Satu section konten per file**: Pastikan hanya ada satu pasangan `@section('content') ... @endsection` dalam setiap view. Hindari menutup section dua kali.

Contoh pola header:
```blade
<div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
    <h3 class="fw-bold mb-0">Data ...</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#...Modal" id="btnAdd...">
        <i class="bi bi-plus-lg me-2"></i>Tambah ...
    </button>
</div>
```

---

## 1.2 Pola CRUD di UI: **Modal Create/Edit + Modal Konfirmasi Delete**

Standar ini menggunakan **Modal** untuk create/edit, dan **modal konfirmasi** untuk delete. Untuk master data, hindari halaman `create.blade.php` atau `edit.blade.php` terpisah (kecuali memang ada keputusan desain yang berbeda).

### 1.2.1 Kerangka Modal Create/Edit

**Template wajib (contoh resource generik):**
```blade
<!-- resources/views/admin/<resource>/index.blade.php -->
<div class="modal fade" id="resourceModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="resourceForm" method="POST" action="{{ route('<resource>.store') }}">
        @csrf
        <input type="hidden" name="_method" id="resourceFormMethod" value="POST">
        <input type="hidden" name="id" id="resource_id">

        <div class="modal-header">
          <h5 class="modal-title" id="resourceModalTitle">Tambah Data</h5>
          <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <div class="modal-body">
          ...fields...
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="btnSaveResource">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
```

**Aturan:**
- **Selalu ada** `@csrf`.
- **Selalu ada** hidden `_method` untuk switch POST/PUT.
- Mode create/edit ditentukan dengan:
  - `form.action` diarahkan ke `route('resource.store')` (create) atau `url('resource') + '/' + id` (edit).
  - `formMethod.value` diset ke `POST` atau `PUT`.
  - `title.textContent` disesuaikan.

### 1.2.2 Kerangka Modal Konfirmasi Delete

**Template wajib:**
```blade
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="deleteForm" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-header">
          <h5 class="modal-title">Hapus ...</h5>
          <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
        <div class="modal-body">
          <p>Yakin ingin menghapus ... <strong id="delete_name">-</strong>?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-danger">Hapus</button>
        </div>
      </form>
    </div>
  </div>
</div>
```

**Aturan:**
- Tombol delete pada tabel wajib menyimpan `data-id` dan `data-name`.
- JS wajib mengubah `deleteForm.action` ke URL `resource/{id}`.

---

## 1.3 Pola Data Display: DataTables

### 1.3.1 Konfigurasi DataTables

Standar default: gunakan **client-side DataTables** untuk data yang sudah dirender oleh Blade. Bila data besar, boleh beralih ke server-side, tapi pastikan konsisten di seluruh modul.

**Snippet (init DataTable):**
```js
$(document).ready(function() {
  $('#<resource>_table').DataTable({
    pageLength: 10,
    ordering: true,
    language: {
      url: ''
    }
  });
});
```

**Aturan:**
- Table markup mengikuti style Metronic:
  - `table align-middle table-row-dashed fs-6 gy-5`
  - `<thead>` memakai kelas `text-start text-muted fw-bold fs-7 text-uppercase gs-0`
- Inisialisasi DataTables dilakukan di `@push('scripts')` per halaman.

### 1.3.2 Reload DataTables Setelah CRUD

Standar default: CRUD master data dilakukan via submit form normal (bukan AJAX). Setelah submit, halaman redirect dan tabel tampil ulang.

---

### 1.3.3 Event Aksi pada Tabel (Wajib Delegated untuk DataTables)

Karena DataTables melakukan re-render DOM saat pagination/sort/search, binding event langsung ke tombol dengan `querySelectorAll(...).addEventListener(...)` akan tidak bekerja di halaman 2 dst. Gunakan delegated event pada elemen tabel (jQuery) agar tetap berfungsi.

Snippet (jQuery, disarankan):
```js
// Pastikan inisialisasi DataTables lebih dulu
const dt = $('#<resource>_table').DataTable({ pageLength: 10, ordering: true });

// Delegated handler untuk tombol Edit
$('#<resource>_table').on('click', '.btnEdit<Resource>', function(){
  // ... isi form modal edit dari data-attribute tombol ini
});

// Delegated handler untuk tombol Delete
$('#<resource>_table').on('click', '.btnDelete<Resource>', function(){
  // ... konfirmasi hapus dan submit form hidden
});
```

Fallback tanpa jQuery (bila tidak memakai DataTables): gunakan `addEventListener` biasa atau manual re-bind setelah konten berubah. Namun untuk halaman standar dengan DataTables, pakai pola delegated di atas.

## 1.4 Komponen UI Kustom / Kompleks

### 1.4.1 Image Input (Metronic `data-kt-image-input`)

Digunakan untuk upload foto profil pengguna atau entitas lain yang relevan.

**Snippet (profile modal):**
```blade
<div class="image-input image-input-circle" data-kt-image-input="true"
     style="background-image: url('{{ asset('assets/media/svg/avatars/blank.svg') }}')">
  <div id="profile_photo_wrapper" class="image-input-wrapper w-125px h-125px"></div>
  <label class="btn btn-icon btn-circle ..." data-kt-image-input-action="change">
    <input type="file" name="photo" id="profile_photo" accept=".png, .jpg, .jpeg, .webp" />
  </label>
</div>
<div class="invalid-feedback d-block" data-field="photo"></div>
```

**Snippet (preview pakai FileReader):**
```js
photoInput?.addEventListener('change', function(){
  const f = photoInput.files && photoInput.files[0];
  if (!f) return;
  const reader = new FileReader();
  reader.onload = (e) => {
    if (photoWrapper) photoWrapper.style.backgroundImage = `url('${e.target.result}')`;
  };
  reader.readAsDataURL(f);
});
```

### 1.4.2 Select2 untuk Combobox

Pola `select2` untuk combobox umum di dalam modal.

**Snippet:**
```js
$('#generic_select_id').select2({
  dropdownParent: $('#genericModal'),
  width: '100%'
});
```

**Aturan:**
- Untuk select di dalam modal, **wajib** `dropdownParent: $('#<modalId>')` agar dropdown tidak tertutup modal.

### 1.4.3 Date Input

Filter pada log notifikasi memakai input native HTML5:
```blade
<input type="date" name="date_from" class="form-control">
```

---

# 2) PENANGANAN JAVASCRIPT & AJAX

## 2.1 Prinsip Umum

- JS per halaman ditaruh pada `@push('scripts')`.
- Untuk master data, submit form modal umumnya **non-AJAX** (HTTP form submit biasa) lalu controller melakukan `redirect()->route(...)->with(...)`.
- Untuk halaman profil, submit menggunakan **Fetch** + JSON response.

---

## 2.2 Pola Submit AJAX (Fetch) + Validasi

Gunakan pola ini untuk form yang butuh UX cepat (mis. profil, upload ringan, modal detail dengan update kecil).

### 2.2.1 Template Helper: clearValidation

**Snippet:**
```js
function clearValidation(scope) {
  (scope || document).querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
  (scope || document).querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
}
```

### 2.2.2 Submit Form Profile (Fetch)

**Snippet:**
```js
profileForm?.addEventListener('submit', function(e) {
  e.preventDefault();
  clearValidation(profileForm);

  const formData = new FormData(profileForm);
  formData.append('_method', 'PUT');

  fetch(routes.updateProfile, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
    body: formData
  })
  .then(async (resp) => {
    const data = await resp.json();
    if (!resp.ok) throw { status: resp.status, data };
    window.toastr?.success?.(data.message || 'Berhasil disimpan');
  })
  .catch(err => {
    const errs = err?.data?.errors || {};
    Object.keys(errs).forEach(field => {
      const input = profileForm.querySelector(`[name="${field}"]`);
      input && input.classList.add('is-invalid');
      const fb = profileForm.querySelector(`.invalid-feedback[data-field="${field}"]`);
      fb && (fb.textContent = errs[field][0]);
    });
    if (!Object.keys(errs).length) window.toastr?.error?.('Terjadi kesalahan.');
  });
});
```

**Aturan wajib untuk AJAX form di proyek Laravel 10 ini:**
- Gunakan `fetch()` + `FormData`.
- Gunakan header `X-CSRF-TOKEN` dari `csrf_token()`.
- Bila metode `PUT/PATCH/DELETE`, gunakan `_method` pada FormData.
- Handling error:
  - Baca `err.data.errors` (struktur Laravel validation).
  - Tandai field dengan `.is-invalid`.
  - Render pesan ke elemen `.invalid-feedback[data-field="..."]`.
- Notifikasi: gunakan `window.toastr?.success?.(...)` / `window.toastr?.error?.(...)`.

---

## 2.3 Notifikasi UI (Toastr) dan Flash Message

Untuk halaman non-AJAX (redirect), pola notifikasi di view adalah:

**Snippet (flash success/error/errors):**
```blade
@if(session('success'))
<script>
  (function(){
    var msg = @json(session('success'));
    if (window.toastr && toastr.success) { toastr.success(msg); }
    else { console.log('SUCCESS:', msg); }
  })();
</script>
@endif

@if($errors && $errors->any())
<script>
  (function(){
    var errs = @json($errors->all());
    var msg = errs.join('\n');
    if (window.toastr && toastr.error) { toastr.error(msg); }
    else { console.error('ERRORS:', msg); }
  })();
</script>
@endif
```

**Aturan:**
- Bila controller memakai `with('success')` / `with('error')`, view wajib menyediakan render Toastr seperti ini (atau mengikuti halaman yang sudah ada).

---

## 2.4 Modal Detail dengan Fetch (Read-only)

Ada pola fetch saat modal dibuka (`show.bs.modal`) untuk mengambil detail log.

**Snippet:**
```js
modal.addEventListener('show.bs.modal', function (event) {
  const button = event.relatedTarget;
  const id = button.getAttribute('data-id');
  fetch("{{ url('/notification-logs') }}/" + id)
    .then(res => res.json())
    .then(data => { body.innerHTML = `...`; })
    .catch(() => { body.innerHTML = '<div class="text-danger">Gagal memuat detail.</div>'; });
});
```

**Aturan:**
- Untuk modal detail/read-only, **boleh** fetch on-open dan render HTML template string.
- Pastikan escape minimal untuk konten raw (contoh: `.replace(/</g,'&lt;')`).

---

## 2.5 Tips Blade & JSON pada Atribut HTML

Untuk menyisipkan data kompleks ke atribut `data-*` (mis. list item), hindari pola yang memicu error parser Blade.

Aturan:
- Gunakan `->toJson()` pada koleksi/array, dan bungkus atribut dengan tanda kutip ganda HTML.
- Hindari arrow function `fn($it) => [...]` di dalam `@json(...)` pada atribut, karena raw bracket/parenthesis dapat mengacaukan parser. Gunakan closure klasik.

Snippet aman:
```blade
<button
  data-items="{{ $order->orderItems->map(function($it){ return [
    'color_code' => $it->color_code,
    'color_name' => $it->color_name,
    'size' => $it->size,
    'quantity' => $it->quantity,
  ]; })->values()->toJson() }}">
```

Catatan:
- Selalu pakai kutip ganda (`"..."`) pada atribut HTML saat menyisipkan JSON (yang juga memakai kutip ganda di dalamnya).
- Bila butuh `@json(...)`, gunakan pada konten script/JS, bukan di dalam atribut kompleks.

---

# 3) KONVENSI CONTROLLER & VALIDASI

## 3.1 Struktur Controller

- Controller berada di `app/Http/Controllers`.
- Banyak modul master data menggunakan `Route::resource(...)->except(['show'])`.
- Pola method yang dipakai:
  - `index()` untuk render listing.
  - `store(Request $request)` untuk create.
  - `update(Request $request, string $id)` untuk update.
  - `destroy(string $id)` untuk delete.

**Snippet (resource controller generik):**
```php
public function index()
{
    $items = Model::query()->latest()->get();
    return view('admin.<resource>.index', compact('items'));
}

public function store(Request $request)
{
    $validated = $request->validate([
        'name' => ['required','string','max:50'],
    ]);

    Model::create($validated);

    return redirect()->route('<resource>.index')->with('success', 'Data berhasil ditambahkan');
}
```

**Aturan:**
- Untuk master data, controller **mengembalikan redirect** + flash message.
- Data untuk view dikirim via `compact(...)`.

---

## 3.2 Validasi Input

### 3.2.1 Status Form Request

- Standar ini memilih validasi langsung pada controller via `$request->validate([...])` untuk menjaga pola tetap sederhana.

**Aturan wajib:**
- Jika menambah endpoint baru, gunakan validasi inline dengan `$request->validate([...])`.
- Jangan memperkenalkan FormRequest terpisah kecuali ada keputusan arsitektur eksplisit.

### 3.2.2 Validasi Bisnis Tambahan

Contoh validasi bisnis setelah validation rule:

**Snippet:**
```php
if (!empty($validated['homeroom_teacher_id'])) {
    $isTeacher = User::where('id', $validated['homeroom_teacher_id'])
        ->where('role', 'teacher')
        ->exists();
    if (!$isTeacher) {
        return back()->with('error', 'Role tidak sesuai.')->withInput();
    }
}
```

---

## 3.3 Standar Response

### 3.3.1 Redirect + Flash Message (Non-AJAX)

**Snippet:**
```php
return redirect()->route('<resource>.index')->with('success', 'Data berhasil ditambahkan');
```

### 3.3.2 JSON Response (AJAX)

Digunakan pada form AJAX.

**Snippet:**
```php
return response()->json([
    'message' => 'Berhasil disimpan',
    'data' => $data,
]);
```

Untuk error upload, controller mengembalikan struktur mirip validation:
```php
return response()->json([
    'message' => 'Gagal upload foto',
    'errors' => ['photo' => ['...']],
], 500);
```

**Aturan:**
- Response JSON **wajib** punya `message`.
- Jika error field-level, **wajib** gunakan `errors: { field: [msg] }` agar UI dapat menampilkan per-field.

---

# 4) KONTROL AKSES (GENERAL)

Dokumen ini mendukung dua pendekatan umum untuk kontrol akses: statis (role tetap) dan dinamis (permission/policy). Pilih salah satu secara konsisten per proyek.

## 4.1 Kontrol Akses Statis (Role Tetap)

Bagian ini sengaja dikosongkan agar dapat diisi sesuai proyek yang memakai role statis.

- Middleware alias:
```php
// app/Http/Kernel.php
protected $routeMiddleware = [
    // ...
    'role' => \App\Http\Middleware\RoleMiddleware::class,
];
```

- Penerapan di routes:
```php
// routes/web.php
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    // ... admin routes
});

Route::prefix('crew')->middleware(['auth', 'role:crew'])->group(function () {
    // ... crew routes
});
```

- Catatan implementasi:
```text
// Konvensi RBAC statis proyek ini:
// 1) Field role berada pada tabel users (mis. 'admin' | 'crew').
// 2) Sidebar bersifat statis berdasar role, tanpa @can/permission dinamis.
// 3) Gunakan group route auth+role seperti contoh di atas untuk seluruh modul.
// 4) Redirect pasca-login diarahkan ke dashboard sesuai role.
```

- Implementasi middleware sederhana:
```php
// app/Http/Middleware/RoleMiddleware.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!Auth::check() || Auth::user()->role !== $role) {
            abort(403);
        }
        return $next($request);
    }
}
```

---

## 4.2 Kontrol Akses Dinamis (Permission/Policy)

Pendekatan dinamis menggunakan permission-key dan/atau policy untuk mengatur akses granular. Bisa memanfaatkan Gate/Policy native Laravel atau paket seperti `spatie/laravel-permission`.

**Prinsip umum:**
- User dapat memiliki banyak role dan mewarisi banyak permission.

### 4.2.1 How to setup (Dinamis)

- Pilih salah satu pendekatan berikut dan terapkan konsisten di seluruh proyek.

- Opsi A — Native Gate/Policy Laravel:
  1) Buat Policy untuk model terkait
     ```bash
     php artisan make:policy EntityPolicy --model=Entity
     ```
  2) Daftarkan Policy di `App\Providers\AuthServiceProvider`
     ```php
     protected $policies = [
         App\Models\Entity::class => App\Policies\EntityPolicy::class,
     ];
     ```
  3) Implementasikan metode seperti `view`, `create`, `update`, `delete` di Policy.
  4) Pakai middleware `can:<ability>` pada route, atau panggil `Gate::authorize('<ability>', $entity)` / `$this->authorize('<ability>', $entity)` di controller.

- Opsi B — Paket spatie/laravel-permission:
  1) Instal & publish migrasi
     ```bash
     composer require spatie/laravel-permission
     php artisan vendor:publish --provider="Spatie\\Permission\\PermissionServiceProvider"
     php artisan migrate
     ```
  2) Tambahkan trait pada model `User`
     ```php
     use Spatie\Permission\Traits\HasRoles;

     class User extends Authenticatable {
         use HasRoles; // otomatis memakai guard "web" kecuali diubah
     }
     ```
  3) Seed roles & permissions (contoh minimal)
     ```php
     use Spatie\Permission\Models\Role;
     use Spatie\Permission\Models\Permission;

     $manage = Permission::firstOrCreate(['name' => 'module.manage']);
     $create = Permission::firstOrCreate(['name' => 'entity.create']);
     $update = Permission::firstOrCreate(['name' => 'entity.update']);

     $admin = Role::firstOrCreate(['name' => 'admin']);
     $admin->syncPermissions([$manage, $create, $update]);

     $user = User::first();
     $user?->assignRole('admin');
     ```
  4) Gunakan middleware `can:<permission>` pada routes dan `@can` di Blade. (Opsional: middleware `role:<role>` atau `permission:<perm>` bila alias didaftarkan.)
  5) Jika mengubah role/permission di runtime, reset cache:
     ```bash
     php artisan optimize:clear
     # atau khusus: php artisan permission:cache-reset
     ```

**Contoh (routes):**
```php
Route::group(['middleware' => ['auth','can:module.manage']], function () {
    Route::resource('admin/modules', Admin\\ModuleController::class)->except(['show']);
});

Route::post('entities/{id}/approve', [EntityApprovalController::class, 'approve'])
    ->middleware(['auth','can:entity.approve'])
    ->name('entities.approve');
```

**Contoh (Blade):**
```blade
@can('entity.create')
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#entityModal">Tambah</button>
@endcan
```

---

# 5) STANDAR SYSTEM LOGS

Seluruh aksi penting sistem dicatat ke tabel `system_logs` (lihat migration `database/migrations/2026_04_05_111609_create_system_logs_table.php`). Struktur kolom utama:
- `id` (UUID), `user_id` (nullable)
- `action` (string) — contoh: `created`, `updated`, `deleted`, `assigned`, `unassigned`, `login`, `logout`
- `table_name` (string), `record_id` (UUID id dari entitas)
- `old_values` (json), `new_values` (json)
- `method` (HTTP method), `url` (full URL), `request_payload` (json body/query), `ip_address`

## 5.1 Setup
- Pastikan migration telah dijalankan (kolom sesuai file migration).
- Buat model `App\Models\SystemLog` (jika belum ada) dan relasi di `User` sudah ada (`user->systemLogs()`).
- Disarankan menambahkan helper/trait untuk mempermudah pencatatan.

Contoh helper sederhana:
```php
// app/Support/SystemLogger.php
namespace App\Support;

use App\Models\SystemLog;
use Illuminate\Support\Facades\Auth;

class SystemLogger
{
    public static function record(string $action, string $table, string $recordId, array $old = null, array $new = null): void
    {
        $req = request();
        SystemLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'table_name' => $table,
            'record_id' => $recordId,
            'old_values' => $old,
            'new_values' => $new,
            'method' => $req?->method(),
            'url' => $req?->fullUrl(),
            'request_payload' => $req?->all(),
            'ip_address' => $req?->ip(),
        ]);
    }
}
```

## 5.2 Penggunaan (Standarisasi di Controller)

- **Create (store):** catat `action = created`, `old_values = null`, `new_values = payload akhir`.
```php
$model = Model::create($payload);
SystemLogger::record('created', 'models', $model->id, null, $model->toArray());
```

- **Update:** catat `action = updated`, simpan diff atau snapshot lama/baru (minimal snapshot).
```php
$before = $model->replicate()->toArray();
$model->update($payload);
SystemLogger::record('updated', 'models', $model->id, $before, $model->toArray());
```

- **Delete:** catat `action = deleted`, `old_values = snapshot sebelum delete`.
```php
$before = $model->toArray();
$model->delete();
SystemLogger::record('deleted', 'models', $model->id, $before, null);
```

- **Pivot assign/unassign (contoh assign crew ke flight):**
```php
// assign
$flight->crews()->attach($crewId, [...]);
SystemLogger::record('assigned', 'crew_flight_schedules', $assignmentId, null, ['flight_id' => $flight->id, 'crew_id' => $crewId]);

// unassign
$flight->crews()->detach($assignmentId);
SystemLogger::record('unassigned', 'crew_flight_schedules', $assignmentId, ['flight_id' => $flight->id, 'crew_id' => $crewId], null);
```

Catatan:
- Gunakan nama `table_name` sesuai nama tabel database.
- Pastikan `record_id` menyimpan UUID dari entitas asli/pivot.
- `request_payload` boleh dibersihkan dari field sensitif (password, token) sebelum dicatat.
- Gunakan middleware `can:<permission>` pada routes untuk proteksi endpoint.
- Di Blade, gunakan `@can('<permission>') ... @endcan` untuk menampilkan aksi/komponen bersyarat.


---

# 6) Konvensi Khusus Proyek (Checklist)

- Password default role crew: `Qwerty123*`.
- URL file lampiran/storage publik: wajib `url('storage/...')`, jangan `asset()`.
- Sidebar statis per role (admin/crew), tanpa permission dinamis.
- Controller: resource, validasi `$request->validate()`, redirect+flash (non-AJAX), JSON bila perlu.
- CRUD master data via Modal (create/edit) + modal konfirmasi delete.
- Listing: DataTables client-side sebagai default.
- JS halaman diletakkan di `@push('scripts')`.

**Contoh (Controller check):**
```php
if (!auth()->user()->can('entity.update')) {
    abort(403);
}
```

Gunakan Policy untuk skenario yang bergantung pada instance model (ownership, scoping data), dengan method seperti `view`, `update`, `delete`.

---

# 5) KONVENSI MODEL & ROUTING

## 5.1 Konvensi Model

### 5.1.1 Mass Assignment

Project menggunakan `$fillable` (bukan `$guarded`).

**Snippet (Student):**
```php
protected $fillable = [
  'user_id', 'nis', 'full_name', 'class_id', 'parent_name', 'parent_email',
];
```

**Aturan:**
- Model baru wajib mendefinisikan `$fillable` secara eksplisit.

### 5.1.2 UUID

Banyak model memakai trait `App\Traits\UsesUuid`.

**Snippet:**
```php
use App\Traits\UsesUuid;

class Student extends Model
{
    use HasFactory, UsesUuid;
}
```

**Aturan:**
- Jika menambah model baru, cek apakah tabel memakai UUID. Bila ya, gunakan `UsesUuid`.

### 5.1.3 Penamaan Relasi

Relasi memakai nama yang jelas dan konsisten (generik):
- `Order::customer()` (belongsTo)
- `Customer::orders()` (hasMany)
- `User::profile()` (hasOne)

**Snippet:**
```php
public function customer(): BelongsTo
{
    return $this->belongsTo(Customer::class, 'customer_id');
}
```

---

## 5.2 Konvensi Routing & Naming

- Route `resource()` dipakai untuk master data.
- Route group memakai middleware array dan kadang `prefix` + `name()`.
- Naming mengikuti Laravel default untuk resource:
  - `entities.index`, `entities.store`, `entities.update`, dll.

**Aturan:**
- Jika membuat route baru untuk area administrasi, gunakan prefix `admin/...` dan namespace `admin.*` bila diperlukan.
- Terapkan middleware akses sesuai strategi kontrol akses yang dipilih (statis/dinamis) secara konsisten.

---

# 6) Template Wajib untuk Modul CRUD Baru (Checklist)

Gunakan checklist ini setiap kali menambah modul master data baru.

- **View**
  - `@extends('layouts.master')`
  - Header halaman konsisten (`h3` + tombol `Tambah` membuka modal)
  - Table markup Metronic + `id="<resource>_table"`
  - Modal Create/Edit:
    - `<form method="POST" action="{{ route('<resource>.store') }}">`
    - `@csrf`
    - hidden `_method` id `...FormMethod`
  - Modal Delete Confirm:
    - `<form id="deleteForm" method="POST">` + `@method('DELETE')`
  - `@push('scripts')`:
    - init DataTable
    - handler tombol add/edit/delete yang set `form.action`, `_method`, isi input, set title
  - Flash message Toastr untuk `session('success')`, `session('error')`, dan `$errors->any()`

- **Controller**
  - Resource controller methods: `index`, `store`, `update`, `destroy`
  - Validasi inline via `$request->validate([...])`
  - Persist via `Model::create($validated)` / `$model->update($validated)`
  - Response: `redirect()->route('<resource>.index')->with('success', '...')`

- **Routes**
  - Tambahkan pada group role yang sesuai.
  - Gunakan `Route::resource(...)->except(['show'])`.

---

# 7) Larangan / Anti-Pattern

- Jangan membuat halaman `create.blade.php` / `edit.blade.php` untuk master data jika pola modul sejenis memakai modal.
- Jangan mengubah layout global tanpa alasan kuat; semua halaman harus tetap kompatibel dengan `layouts.master`.
- Jangan menambahkan FormRequest baru tanpa kebutuhan dan keputusan arsitektur, karena standar ini memilih `$request->validate()`.
- Jangan memperkenalkan library notifikasi baru (mis. SweetAlert) untuk modul baru; gunakan `toastr` sebagaimana yang sudah dipakai.

---

# Status

Dokumen `Agents.md` ini adalah **template pedoman**. Saat dipakai di proyek lain:

- Pastikan path layout, asset, dan vendor UI mengikuti struktur proyek tersebut.
- Jika role/prefix route berbeda, sesuaikan bagian middleware dan routing.
- Jika proyek memilih server-side DataTables atau FormRequest, buat keputusan arsitektur eksplisit dan konsisten di seluruh modul.

---

# 8) Standarisasi Penggunaan Metronic (Khusus AviaSync)

Untuk standarisasi setup dan cara penggunaan komponen Metronic yang dipakai di AviaSync (layout, DataTables, modal CRUD, toastr, image input, select2, FullCalendar), lihat:

- `docs/metronic.md`