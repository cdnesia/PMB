---
name: pmb-crud-module
description: 'Pattern to add a new admin CRUD module (master data or processing feature) to the PMB Laravel app. Use when creating a new controller, resource routes, admin index/form views, or a new feature page in the admin panel. Follows the existing convention used by Prodi, Jalur, Kelas, Kuota, DokumenPersyaratan, Pendaftar.'
---

# PMB Admin CRUD Module Pattern

Use this when adding a new admin module so it matches existing conventions exactly.

## Persona & Standar Kerja
Bertindaklah sebagai **senior programmer + UI/UX professional**:
- Ikuti pola yang sudah ada di proyek — jangan ciptakan gaya baru per module. Konsistensi = kualitas.
- Lengkapi validasi server-side, flash message yang informatif, dan error state yang jelas (bukan sekadar CRUD "yang jalan").
- Pastikan UX pengguna panitia: filter berguna, tabel terbaca, aksi jelas, dan tidak ada langkah ambigu.

## Convention overview (what "consistent" means here)
- Controller di `app/Http/Controllers/Admin/` extends `App\Http\Controllers\Controller`.
- `index()` pakai `->paginate(20)` (+ `->withQueryString()` bila ada filter), `create()`, `store()`, `edit()`, `update()`, `destroy()`.
- `store()`/`update()` validasi via method private `validated(Request, ?int $id)`.
- Flash sukses: `->with('success', '... berhasil ...')` (tampil sebagai toast otomatis).
- Route resource di `routes/web.php` dalam group `role:super-admin|admin-pmb` prefix `admin`, name `admin.`.

## Route (contoh resource + param override)
```php
Route::resource('dokumen', DokumenPersyaratanController::class)->except('show')
    ->parameters(['dokumen' => 'dokumen']);
```
Untuk halaman proses non-resource (seperti Pendaftar), pakai route eksplisit:
```php
Route::get('pendaftar', [PendaftarController::class, 'index'])->name('pendaftar.index');
Route::get('pendaftar/{pendaftaran}', [PendaftarController::class, 'show'])->name('pendaftar.show');
Route::patch('pendaftar/{pendaftaran}/status', [PendaftarController::class, 'updateStatus'])->name('pendaftar.status');
```

## Sidebar
Tambahkan item di `resources/views/layouts/admin.blade.php` array `$nav`:
```php
'Nama Menu' => ['icon' => 'icon-name', 'route' => 'admin.x.index', 'match' => 'admin.x.*'],
```

## View structure
- Index: `@extends('layouts.admin')` + `@section('title', ...)` + `@section('content')`.
  - `<x-ui-page-header title="..." description="...">` dengan `<x-slot:action>` untuk tombol "Tambah".
  - Filter (bila perlu) dalam `<x-ui-card>` berisi form GET.
  - Tabel dalam `<x-ui-card :padding="''">`, header `<thead class="bg-gray-50 ...">`, empty state `<x-ui-empty-state :colspan="N" />`, pagination `@if($x->hasPages())`.
- Form: `<x-ui-card>` berisi grid `sm:grid-cols-2`, field pakai `<x-ui-label>` + `<x-ui-input>`/`<x-ui-select>`, footer `<div class="mt-8 flex items-center gap-3 border-t border-gray-100 pt-6">` dengan `<x-ui-button variant="primary" icon="check">Simpan</x-ui-button>` + `variant="secondary"` Batal.

## UI components available (lihat skill pmb-ui)
`x-ui-button`, `x-ui-card`, `x-ui-page-header`, `x-ui-input`, `x-ui-select`, `x-ui-label`, `x-ui-badge`, `x-ui-empty-state`, `x-ui-status-badge`, `x-icon`, `x-toast`.

## Model
- `$fillable` list, `casts()` method untuk bool/decimal/date.
- Relasi: `belongsTo`/`hasMany`/`belongsToMany` sesuai domain (lihat skill pmb-domain).

## Migration
- Nama file timestamped: `YYYY_MM_DD_HHMMSS_<aksi>_<tabel>_table.php` (ada di database/migrations).
- FK: `$table->foreignId('x_id')->constrained('tabel')->cascadeOnDelete();`.

## Checklist setelah selesai
1. `php artisan route:list --except-vendor` cek route.
2. `get_errors` di file baru.
3. `npm run build` (jika ada class Tailwind baru).
4. Uji via browser `https://pmb.test`.
