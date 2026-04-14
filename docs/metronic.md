# Standarisasi UI Metronic (AviaSync)

Dokumen ini adalah standar penggunaan template Metronic (Bootstrap 5) yang dipakai di AviaSync. Fokus hanya pada komponen yang benar-benar dipakai di project ini.

---

## 1) Struktur Asset Metronic yang Dipakai

Project ini memuat Metronic dari folder `public/assets` (bukan dari Vite).

- CSS global:
  - `public/assets/plugins/global/plugins.bundle.css`
  - `public/assets/css/style.bundle.css`
- JS global:
  - `public/assets/plugins/global/plugins.bundle.js`
  - `public/assets/js/scripts.bundle.js`
- Vendor yang dipakai di beberapa halaman:
  - DataTables: `public/assets/plugins/custom/datatables/datatables.bundle.css|js`
  - FullCalendar: `public/assets/plugins/custom/fullcalendar/fullcalendar.bundle.css|js`

Sumber kebenaran pemanggilan asset ada di:
- `resources/views/layouts/master.blade.php`

---

## 2) Layout & Struktur Halaman

Aturan implementasi halaman:

- Wajib:
  - `@extends('layouts.master')`
  - `@section('content') ... @endsection`
- Opsional (disarankan):
  - `@section('page_title', '...')`
- CSS/JS khusus halaman:
  - Taruh CSS di `@push('styles')`
  - Taruh JS di `@push('scripts')`

Contoh kerangka minimal:

```blade
@extends('layouts.master')

@section('page_title', 'Judul Halaman')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <h3 class="fw-bold mb-0">Judul</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#resourceModal">
            <i class="bi bi-plus-lg me-2"></i>Tambah
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            ...
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // init JS halaman
        });
    </script>
@endpush
```

---

## 3) Komponen yang Dipakai

### 3.1 Card / Layout Dasar

Gunakan komponen Bootstrap/Metronic yang konsisten:

- `card`, `card-body`
- `d-flex`, `flex-wrap`, `align-items-center`, `justify-content-between`
- Tombol:
  - Primary action: `btn btn-primary`
  - Secondary: `btn btn-light`
  - Danger: `btn btn-danger`

Catatan:
- Project ini punya script global yang menambahkan `btn-sm` ke `.btn`. Jadi cukup pakai `btn btn-primary` dst.

#### 3.1.1 Basic Card

```html
<div class="card shadow-sm">
    <div class="card-header">
        <h3 class="card-title">Title</h3>
        <div class="card-toolbar">
            <button type="button" class="btn btn-sm btn-light">
                Action
            </button>
        </div>
    </div>
    <div class="card-body">
        Lorem Ipsum is simply dummy text...
    </div>
    <div class="card-footer">
        Footer
    </div>
</div>
```

#### 3.1.2 Card Scroll (Konten dengan tinggi tetap)

```html
<div class="card bg-light shadow-sm">
    <div class="card-header">
        <h3 class="card-title">Title</h3>
        <div class="card-toolbar">
            <button type="button" class="btn btn-sm btn-light">
                Action
            </button>
        </div>
    </div>
    <div class="card-body card-scroll h-200px">
        Lorem Ipsum is simply dummy text...
    </div>
    <div class="card-footer">
        Footer
    </div>
    </div>
```

#### 3.1.3 Collapsible Card

```html
<div class="card shadow-sm">
    <div class="card-header collapsible cursor-pointer rotate" data-bs-toggle="collapse" data-bs-target="#kt_docs_card_collapsible">
        <h3 class="card-title">Title</h3>
        <div class="card-toolbar rotate-180">
            <i class="ki-duotone ki-down fs-1"></i>
        </div>
    </div>
    <div id="kt_docs_card_collapsible" class="collapse show">
        <div class="card-body">
            Lorem Ipsum is simply dummy text...
        </div>
        <div class="card-footer">
            Footer
        </div>
    </div>
</div>
```

Catatan: pastikan `id` pada target collapse unik di halaman.

#### 3.1.4 Linkable Card

```html
<a href="#" class="card hover-elevate-up shadow-sm parent-hover">
    <div class="card-body d-flex align-items">
        <span class="svg-icon fs-1">...</span>
        <span class="ms-3 text-gray-700 parent-hover-primary fs-6 fw-bold">
            Example link title
        </span>
    </div>
</a>
```

#### 3.1.5 Removable Card

```html
<div class="card shadow-sm">
    <div class="card-header">
        <h3 class="card-title">Title</h3>
        <div class="card-toolbar">
            <a href="#" class="btn btn-icon btn-sm btn-active-color-primary" data-kt-card-action="remove" data-kt-card-confirm="true" data-kt-card-confirm-message="Are you sure to remove this card ?" data-bs-toggle="tooltip" title="Remove card" data-bs-dismiss="click">
                <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
            </a>
        </div>
    </div>
    <div class="card-body">...</div>
</div>
```

#### 3.1.6 Flush Borders

```html
<div class="card card-flush shadow-sm">
    <div class="card-header">
        <h3 class="card-title">Title</h3>
        <div class="card-toolbar">
            <button type="button" class="btn btn-sm btn-light">Action</button>
        </div>
    </div>
    <div class="card-body py-5">
        Lorem Ipsum is simply dummy text...
    </div>
    <div class="card-footer">Footer</div>
</div>
```

#### 3.1.7 Bordered Style

```html
<div class="card card-bordered">
    <div class="card-header">
        <h3 class="card-title">Title</h3>
        <div class="card-toolbar">
            <button type="button" class="btn btn-sm btn-light">Action</button>
        </div>
    </div>
    <div class="card-body">Lorem Ipsum is simply dummy text...</div>
    <div class="card-footer">Footer</div>
</div>
```

#### 3.1.8 Dashed Style

```html
<div class="card card-dashed">
    <div class="card-header">
        <h3 class="card-title">Title</h3>
        <div class="card-toolbar">
            <button type="button" class="btn btn-sm btn-light">Action</button>
        </div>
    </div>
    <div class="card-body">Lorem Ipsum is simply dummy text...</div>
    <div class="card-footer">Footer</div>
</div>
```

---

### 3.2 DataTables

DataTables dipakai untuk listing (client-side) di banyak halaman.

**Markup tabel (standar):**

```blade
<div class="table-responsive">
    <table id="resource_table" class="table align-middle table-row-dashed fs-6 gy-5">
        <thead>
            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                <th>...</th>
            </tr>
        </thead>
        <tbody>
            ...
        </tbody>
    </table>
</div>
```

**Init (disarankan untuk halaman baru):**

```js
document.addEventListener('DOMContentLoaded', function () {
  if (window.jQuery && jQuery.fn && jQuery.fn.DataTable) {
    const dt = jQuery('#resource_table').DataTable({
      pageLength: 10,
      ordering: true,
    });

    // Optional: external search
    const $search = jQuery('#resource_search');
    $search.on('keyup change', function () {
      dt.search(this.value || '').draw();
    });
  }
});
```

Aturan:
- Inisialisasi di `@push('scripts')`.
- Kalau ada input search terpisah, gunakan `dt.search(...).draw()`.

---

### 3.3 Bootstrap Modal (CRUD Modal)

Standar CRUD master data menggunakan modal (bukan halaman create/edit terpisah).

Pola yang dipakai:
- Modal Create/Edit:
  - `<form method="POST" action="...">`
  - `@csrf`
  - hidden `_method` untuk switch `POST`/`PUT`
- Modal Confirm Delete:
  - `<form method="POST"> @csrf @method('DELETE')`

JS yang umum:
- Listen event `show.bs.modal` untuk set `form.action`, `_method`, title, dan isi field.

---

### 3.4 Toastr (Flash Message)

Toastr dipakai untuk menampilkan flash message.

Pola yang dipakai:

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

@if(session('error'))
    <script>
        (function(){
            var msg = @json(session('error'));
            if (window.toastr && toastr.error) { toastr.error(msg); }
            else { console.error('ERROR:', msg); }
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

---

### 3.5 Metronic Image Input (`data-kt-image-input`)

Dipakai untuk upload foto profil crew.

Pola markup:

```blade
<div class="image-input image-input-circle" data-kt-image-input="true" id="photo_container">
    <div id="photo_wrapper" class="image-input-wrapper w-125px h-125px"
        style="background-image: url('{{ asset('assets/media/svg/avatars/blank.svg') }}');"></div>

    <label class="btn btn-icon btn-circle btn-active-color-primary w-30px h-30px bg-body shadow"
        data-kt-image-input-action="change">
        <i class="bi bi-pencil-fill fs-7"></i>
        <input type="file" name="profile_picture" id="profile_picture" accept=".png, .jpg, .jpeg, .webp" />
        <input type="hidden" name="avatar_remove" />
    </label>
</div>
```

Pola preview (opsional):
- Set `backgroundImage` pada wrapper.

---

### 3.6 Select2

Dipakai untuk dropdown yang butuh searchable select.

Aturan penting:
- Kalau select berada di dalam modal, **wajib** set `dropdownParent` agar dropdown tidak ketutup modal.

Contoh:

```js
document.addEventListener('DOMContentLoaded', function () {
  const modal = document.getElementById('assignCrewModal');
  if (window.$ && modal) {
    window.$(modal).on('shown.bs.modal', function () {
      window.$('.select2-crew').select2({
        dropdownParent: window.$('#assignCrewModal'),
        width: '100%',
        placeholder: '-- Pilih Crew --'
      });
    });
  }
});
```

---

### 3.7 FullCalendar

Dipakai di Crew Dashboard untuk tampilan kalender jadwal.

Catatan:
- Script dan CSS FullCalendar sudah disediakan dari Metronic bundle yang dipanggil di `layouts.master`.

Pola init ringkas:

```js
document.addEventListener('DOMContentLoaded', function () {
  var calendarEl = document.getElementById('kt_calendar_app');
  if (!calendarEl || !window.FullCalendar) return;

  var events = []; // isi dari controller via @json(...)

  var calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    events: events,
    eventClick: function(info) {
      // buka modal detail (Bootstrap Modal)
    }
  });

  calendar.render();
});
```

---

## 4) Konvensi Penting Terkait UI

- Attachment/file publik (storage) wajib pakai:
  - `url('storage/...')`
- Hindari menambah library notifikasi baru untuk modul baru.
- Untuk modul CRUD master data: tetap ikuti pola modal (create/edit + confirm delete).

---

## 5) Referensi Cepat (File yang Jadi Acuan)

- Layout utama:
  - `resources/views/layouts/master.blade.php`
- Contoh CRUD + DataTables + ImageInput + Toastr:
  - `resources/views/admin/crew.blade.php`
- Contoh Select2 di modal:
  - `resources/views/admin/flight_schedules/show.blade.php`
- Contoh FullCalendar + DataTables:
  - `resources/views/crew/dashboard.blade.php`
