# Laravel 13 & Project Gotchas (PMB)

Baca sebelum menyentuh kode yang berkaitan. Ini menghindari bug yang sudah pernah terjadi.

## spatie/laravel-permission v8
- **TIDAK auto-register** middleware alias `role`/`permission`. Harus daftar manual di `bootstrap/app.php`:
```php
$middleware->alias([
    'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
    'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
    'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
]);
```

## Route resource parameter naming
- Laravel 13 `Route::resource('kelas')` → param `{kela}` (singular), `kuota` → `{kuotum}`, `dokumen` → `{dokumen}` (sudah sama).
- Jika tipe parameter controller tidak cocok, override: `->parameters(['kelas' => 'kelas', 'kuota' => 'kuota', 'dokumen' => 'dokumen'])`.

## User model (Laravel 13 style)
- Pakai attribute `#[Fillable([...])]` dan `#[Hidden([...])]`, BUKAN `protected $fillable`.
- Tambahkan field baru ke `#[Fillable]` (mis. `phone`).

## Select2 (4.0.13)
- **Wajib jQuery 3.7.1** — jQuery 4 hapus `$.isArray`/`$.isFunction` yang dipakai select2 (error: `$.isArray is not a function`).
- `import 'select2'` saja TIDAK cukup (CJS export = factory). Harus:
```js
import select2Init from 'select2/dist/js/select2.js';
const init = typeof select2Init === 'function' ? select2Init : select2Init?.default;
if (typeof init === 'function') init(window, jQuery);
```
- Directive `x-select2` sudah ada di `app.js`; komponen `<x-ui-select>` otomatis pakai select2.

## Upload file
- Form upload WAJIB `enctype="multipart/form-data"`, kalau tidak file tidak terkirim (error "must be a file").
- Simpan via `$file->store('dokumen', 'public')`; perlu `php artisan storage:link` (sudah dibuat).
- Akses publik: `asset('storage/'.$path)`.

## Build & run
- Asset: `npm run build` (atau `npm run dev` saat develop).
- Migrasi+seed: `php artisan migrate:fresh --seed`.
- Site: `https://pmb.test` (Herd). DB local: MySQL 8.4.7, root/`rootroot`, db `pmb` + `pmb_test`.

## RBAC roles
`super-admin`, `admin-pmb`, `operator-prodi`, `verifikator`, `bendahara`, `pimpinan`, `mahasiswa`.
Route admin diproteksi `role:super-admin|admin-pmb`; mahasiswa `role:mahasiswa`.
