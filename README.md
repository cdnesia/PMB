# Sistem PMB — Penerimaan Mahasiswa Baru

Aplikasi web untuk mengelola seluruh siklus Penerimaan Mahasiswa Baru, dibangun dengan **Laravel 13** dan **MariaDB** (driver `mariadb`).

## Fitur Utama

- **Master data konfiguratif**: Tahun Penerimaan, Jalur & biaya pendaftaran, Program Studi (Prodi), Kelas Perkuliahan, Kuota Prodi.
- **Setting Prodi (matriks)**: mengatur kombinasi Prodi × Kelas Perkuliahan × Jalur tanpa perubahan kode.
- **Portal mahasiswa**: registrasi akun → pilih jalur → pilih **maksimal 2 prodi** → pilih kelas perkuliahan → nomor pendaftaran unik.
- **Kuota otomatis**: `terpakai` bertambah secara atomik saat pendaftaran; kombinasi penuh otomatis ditolak.
- **RBAC** (Spatie Permission): `super-admin`, `admin-pmb`, `operator-prodi`, `verifikator`, `bendahara`, `pimpinan`, `mahasiswa`.

## Prasyarat

- PHP 8.3+
- Composer
- MariaDB / MySQL (Laravel 13 punya driver `mariadb` khusus)
- Node.js (untuk build asset Vite)

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Konfigurasi database di `.env`:

```env
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pmb
DB_USERNAME=root
DB_PASSWORD=
```

Jalankan migrasi & seeder:

```bash
php artisan migrate:fresh --seed
```

Build asset (sudah dilakukan saat setup Breeze):

```bash
npm install && npm run build
```

## Menjalankan

Via Herd/Valet: buka `http://pmb.test`. Atau via built-in server:

```bash
php artisan serve
```

## Akun Bawaan (Seeder)

| Peran       | Email              | Password   |
|-------------|--------------------|------------|
| Super Admin | admin@pmb.test     | password   |
| Admin PMB   | adminpmb@pmb.test  | password   |
| Mahasiswa   | mahasiswa@pmb.test | password   |

## Struktur Entitas Utama

```
tahun_penerimaan, jalur, prodi, kelas_perkuliahan, gelombang
prodi_kelas_jalur   (matriks setting prodi)
kuota               (per tahun × jalur × prodi × kelas)
pendaftaran         (user, tahun, jalur, nomor_pendaftaran, status)
pendaftaran_prodi   (urutan 1|2, prodi, kelas)  — mendukung 2 pilihan prodi
```

## Alur Pendaftaran (Mahasiswa)

1. Registrasi akun (email + password), otomatis mendapat role `mahasiswa`.
2. Pilih **jalur** penerimaan.
3. Pilih **prodi** (maksimal 2) dan **kelas perkuliahan** untuk masing-masing.
4. Sistem memvalidasi kombinasi (matriks) dan kuota.
5. Nomor pendaftaran unik diterbitkan (format `PMB-{tahun}-{nomor}`).

## Aturan Kuota (race-condition safe)

Klaim kuota dilakukan dalam transaksi dengan *atomic update*:

```php
Kuota::whereKey($id)
    ->whereRaw('terpakai < jumlah')
    ->update(['terpakai' => DB::raw('terpakai + 1')]);
```

Jika `0` baris terpengaruh → kuota penuh → pendaftaran dibatalkan.
