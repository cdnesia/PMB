---
name: pmb-ui
description: 'UI conventions and component system for the PMB Laravel app (Tailwind + Alpine + Select2). Use when building or editing Blade views, styling buttons/forms/tables/badges, adding Select2 dropdowns, showing toast notifications, or keeping visual consistency across admin and mahasiswa pages.'
---

# PMB UI Conventions

Use this for any Blade view work to keep the design system consistent.

## Persona & Standar Kerja
Bertindaklah sebagai **senior UI/UX professional**:
- Terapkan hierarki visual, spacing 8-point yang konsisten, dan aksesibilitas (focus ring, label, kontras).
- Setiap halaman harus ada konteks bagi pengguna: heading jelas, status/state terlihat (kosong, memuat, error, sukses), dan CTA tidak ambigu.
- Jangan mencampur gaya; gunakan komponen `x-ui-*` yang sudah ada. Bila butuh gaya baru, kembangkan di level komponen (bukan inline per-halaman) agar konsisten di seluruh app.

## Design tokens
- Primary color: **indigo** (`indigo-600`/`-700`). Sidebar: `slate-900`.
- Radius: `rounded-lg` (input/button), `rounded-xl` (card). Shadow: `shadow-sm ring-1 ring-gray-200`.
- Font: Figtree. Spacing: 8-point (mt-2/mb-4/mb-6/mt-8).

## Layouts
- `layouts/admin` — sidebar + topbar (nav dari array `$nav`), mobile drawer.
- `layouts/mahasiswa` — top navbar + footer, dropdown profil.
- `layouts/guest` — split-screen auth (branding panel + form).
- Semua layout render toast via `<x-toast>` dan flash session (`success`/`error`/`info`/`warning` + `$errors`).

## Components (Blade, di `resources/views/components/`)
| Component | Usage |
|-----------|-------|
| `x-ui-button` | `variant` (primary/secondary/danger/success), `size` (sm/md/lg), `icon`, `href` (auto `<a>`), `type` |
| `x-ui-card` | container putih; `:padding="''"` untuk tabel full-bleed |
| `x-ui-page-header` | title + description + `<x-slot:action>` |
| `x-ui-input` / `x-ui-select` | input/select standar; select otomatis Select2 |
| `x-ui-label` | label; `required` tampil `*` |
| `x-ui-badge` | `color` gray/green/red/amber/blue/indigo |
| `x-ui-status-badge` | `status` string → label+color terotomatis (draft, lolos, lunas, dst) |
| `x-ui-empty-state` | `:colspan` + `message` |
| `x-icon` | `name` (dashboard, calendar, route, academic, square-stack, adjust, chart, plus, pencil, trash, arrow-left, logout, user, check, document, warning, info, eye, external-link, credit-card) |

## Button sizing rule (penting)
- Ukuran ikon mengikuti tombol: `sm→h-3.5`, `md→h-4`, `lg→h-5` (otomatis di `x-ui-button`).
- Tombol aksi tabel pakai icon-only: `class="rounded-md p-1.5 text-gray-400 hover:bg-indigo-50 hover:text-indigo-600"` (edit) / `hover:bg-red-50 hover:text-red-600` (hapus).

## Select2
- Semua `<x-ui-select>` otomatis Select2. Untuk select yang opsi-nya dinamis (`x-for` Alpine), directive `x-select2` sudah menangani rebuild via MutationObserver + sync `x-model`.
- Select2 4.0.13 butuh jQuery 3.7.1 + init eksplisit (lihat skill pmb-domain → gotchas).

## Toast
- Flash `session('success')` otomatis jadi toast hijau; `$errors` jadi toast merah.
- Panggil manual dari JS: `window.notify('success'|'error'|'warning'|'info', 'pesan')`.

## Form footer standar
```blade
<div class="mt-8 flex items-center gap-3 border-t border-gray-100 pt-6">
    <x-ui-button variant="primary" icon="check">Simpan</x-ui-button>
    <x-ui-button variant="secondary" type="button" :href="route('admin.x.index')">Batal</x-ui-button>
</div>
```

## Status badge mapping
`ui-status-badge` sudah punya map: aktif, draft, ditutup, arsip, lolos, cadangan, tidak_lolos, menunggu_pembayaran, lunas, terverifikasi, daftar_ulang, mahasiswa_baru, ditolak, menunggu, belum_diunggah, belum_bayar, buka, tutup. Tambah status baru → edit `resources/views/components/ui-status-badge.blade.php`.
