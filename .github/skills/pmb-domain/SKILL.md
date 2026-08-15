---
name: pmb-domain
description: 'Domain knowledge for the PMB (Penerimaan Mahasiswa Baru) Laravel app. Use when adding/modifying models, migrations, relationships, business rules, status transitions, kuota logic, or when unsure how entities connect. Covers TahunPenerimaan, Jalur, Prodi, KelasPerkuliahan, Gelombang, ProdiKelasJalur, Kuota, Pendaftaran, PendaftaranProdi, DokumenPersyaratan, DokumenPendaftar.'
---

# PMB Domain Model & Business Rules

Use this skill before touching any model, migration, or status/kuota logic in this Laravel 13 project.

## Persona & Standar Kerja
Bertindaklah sebagai **senior programmer + UI/UX professional**:
- Tulis kode yang **benar, aman, dan idiomatis** — bukan sekadar jalan. Utamakan integritas data (transaksi, atomic update), keamanan (validasi server-side, RBAC), dan maintainability (nama jelas, relasi eksplisit).
- Antisipasi **edge case** (race condition, input kosong, relasi null) sebelum menulis kode.
- Jangan menambahkan kompleksitas yang tidak perlu; ikuti pola yang sudah ada di proyek.
- Setiap perubahan domain harus konsisten ujung-ke-ujung: model → migrasi → controller → validasi → view.

## Stack (fixed)
- Laravel 13.25, PHP 8.3, MySQL via driver `DB_CONNECTION=mariadb`.
- Vite + Tailwind 3 + Alpine 3 + jQuery 3.7.1 + Select2 4.0.13.
- Auth: Laravel Breeze. RBAC: spatie/laravel-permission v8.

## Entities & Relationships

| Model | Table | Key fields |
|-------|-------|------------|
| User | `users` | `name, email (unique), phone (unique), password`; `HasRoles` |
| TahunPenerimaan | `tahun_penerimaan` | `kode, nama, status (draft/aktif/ditutup/arsip), tanggal_mulai, tanggal_selesai` |
| Jalur | `jalur` | `kode, nama, kategori (nasional/mandiri), urutan, biaya_pendaftaran, requires_cbt (bool), is_active` |
| Prodi | `prodi` | `kode, nama, jenjang (D3/D4/S1/S2), fakultas, is_active` |
| KelasPerkuliahan | `kelas_perkuliahan` | `kode, nama, is_active` |
| Gelombang | `gelombang` | `tahun_id, nama, tanggal_*` |
| ProdiKelasJalur | `prodi_kelas_jalur` | `prodi_id, kelas_id, jalur_id` (unique triplet) — matriks "setting prodi" |
| Kuota | `kuota` | `tahun_id, jalur_id, prodi_id, kelas_id (nullable), jumlah, terpakai, is_active` |
| Pendaftaran | `pendaftaran` | `user_id, tahun_id, jalur_id, gelombang_id, nomor_pendaftaran, status, status_pembayaran, nilai_seleksi, catatan` |
| PendaftaranProdi | `pendaftaran_prodi` | `pendaftaran_id, urutan (1|2), prodi_id, kelas_id, status` — mendukung 2 pilihan prodi |
| DokumenPersyaratan | `dokumen_persyaratan` | `jalur_id (null), prodi_id (null), nama, wajib, is_active` |
| DokumenPendaftar | `dokumen_pendaftar` | `pendaftaran_id, dokumen_persyaratan_id, nama, file_path, file_name, file_size, status` |

## Status Flow (Pendaftaran.status)
`draft → (bayar) lunas → (verifikasi berkas) terverifikasi → (seleksi) lolos|cadangan|tidak_lolos → daftar_ulang → mahasiswa_baru`. `ditolak` = terminal negatif.

- `status_pembayaran` terpisah: `belum_bayar|lunas`.
- Kelulusan PER pilihan prodi (`PendaftaranProdi.status`): `lolos|cadangan|tidak_lolos`.
- Dokumen status: `menunggu|terverifikasi|ditolak|belum_diunggah`.

## Business Rules
1. Pendaftar hanya bisa pilih kombinasi prodi/kelas/jalur yang ada di `prodi_kelas_jalur`.
2. Maksimal 2 pilihan prodi (`urutan` 1 & 2); prodi2 tidak boleh sama dgn prodi1.
3. Kuota per Tahun×Jalur×Prodi×Kelas; `terpakai <= jumlah` (klaim ATOMIK).
4. Nomor pendaftaran: format `PMB-{tahun.kode}-{5 digit id}`.
5. Dokumen wajib sesuai jalur + prodi (lihat resolusi di bawah).

## Dokumen Resolusi (wajib ditampilkan ke pendaftar)
Suatu `dokumen_persyaratan` berlaku jika:
`(jalur_id IS NULL OR jalur_id = jalur terpilih) AND (prodi_id IS NULL OR prodi_id IN prodi terpilih)`.

## Kuota Claim (race-condition safe) — jangan ubah pola ini
```php
$updated = Kuota::whereKey($id)->whereRaw('terpakai < jumlah')
    ->update(['terpakai' => DB::raw('terpakai + 1')]);
if (!$updated) throw ValidationException::withMessages([...]);
```
Dijalankan di dalam `DB::transaction()` bersama insert `pendaftaran` + `pendaftaran_prodi`.

## Seeder accounts
`admin@pmb.test`/`password` (super-admin), `adminpmb@pmb.test`/`password` (admin-pmb), `mahasiswa@pmb.test`/`password` (mahasiswa).

## Laravel 13 / project gotchas (critical — see references)
See [gotchas](./references/gotchas.md) before editing routes, middleware, or User model.
