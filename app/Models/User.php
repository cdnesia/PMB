<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'phone', 'password', 'referrer_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasUuids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Referrer::class, 'referrer_id');
    }

    public function referrerProfile(): HasOne
    {
        return $this->hasOne(Referrer::class, 'user_id');
    }

    public function pendaftaran(): HasMany
    {
        return $this->hasMany(Pendaftaran::class, 'user_id');
    }

    /**
     * Hapus akun ini beserta seluruh data pendaftaran, riwayat, dan file
     * terkait secara bersih. Baris turunan di database sudah otomatis ikut
     * terhapus lewat FK cascade, sehingga di sini hanya perlu: (1) menghapus
     * file fisik yang tidak lagi bisa dilacak setelah baris DB-nya hilang,
     * dan (2) mengembalikan kuota prodi yang sempat diklaim pendaftaran ini.
     */
    public function hapusBersih(): void
    {
        $pendaftaran = $this->pendaftaran()
            ->with(['prodiPilihan', 'dokumen', 'syaratJawaban', 'daftarUlang', 'pembayaran'])
            ->get();

        $filePaths = [];

        foreach ($pendaftaran as $p) {
            foreach ($p->dokumen as $dokumen) {
                if ($dokumen->file_path) {
                    $filePaths[] = $dokumen->file_path;
                }
            }

            foreach ($p->syaratJawaban as $syarat) {
                if ($syarat->file_path) {
                    $filePaths[] = $syarat->file_path;
                }
            }

            if ($p->daftarUlang?->bukti_bayar) {
                $filePaths[] = $p->daftarUlang->bukti_bayar;
            }

            if ($p->pembayaran?->bukti_bayar) {
                $filePaths[] = $p->pembayaran->bukti_bayar;
            }

            foreach ($p->prodiPilihan as $pilihan) {
                $this->kembalikanKuota($p, $pilihan);
            }
        }

        if ($filePaths !== []) {
            Storage::disk('public')->delete($filePaths);
        }

        $this->delete();
    }

    private function kembalikanKuota(Pendaftaran $pendaftaran, PendaftaranProdi $pilihan): void
    {
        $kuota = Kuota::where('tahun_id', $pendaftaran->tahun_id)
            ->where('jalur_id', $pendaftaran->jalur_id)
            ->where('prodi_id', $pilihan->prodi_id)
            ->where(function ($q) use ($pilihan) {
                $q->where('kelas_id', $pilihan->kelas_id)->orWhereNull('kelas_id');
            })
            ->orderByRaw('kelas_id IS NULL ASC')
            ->first();

        if ($kuota) {
            Kuota::whereKey($kuota->id)
                ->where('terpakai', '>', 0)
                ->update(['terpakai' => DB::raw('terpakai - 1')]);
        }
    }
}
