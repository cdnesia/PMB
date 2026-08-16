<?php

namespace App\Services;

use App\Models\Agama;
use App\Models\Wilayah;

class NeoFeederSyncService
{
    public function __construct(private NeoFeederService $neo)
    {
    }

    /**
     * Sinkronkan data agama dari NEO Feeder ke tabel lokal `agama`.
     *
     * @return array{created: int, updated: int}
     */
    public function syncAgama(): array
    {
        $created = 0;
        $updated = 0;

        foreach ($this->neo->getAgama() as $row) {
            $kode = (int) ($row['id_agama'] ?? 0);
            $nama = $row['nama_agama'] ?? null;

            if ($kode <= 0 || ! $nama) {
                continue;
            }

            $exists = Agama::where('kode', $kode)->exists();

            Agama::updateOrCreate(['kode' => $kode], ['nama' => $nama]);

            $exists ? $updated++ : $created++;
        }

        return ['created' => $created, 'updated' => $updated];
    }

    /**
     * Sinkronkan data wilayah (negara, provinsi, kota, kecamatan) dari NEO Feeder
     * ke tabel lokal `wilayah`.
     *
     * @return array{created: int, updated: int}
     */
    public function syncWilayah(bool $fresh = false): array
    {
        if ($fresh) {
            Wilayah::query()->delete();
        }

        // NEO Feeder tidak menyediakan level kelurahan/desa (paling dalam kecamatan).
        $mapping = [
            0 => Wilayah::LEVEL_NEGARA,
            1 => Wilayah::LEVEL_PROVINSI,
            2 => Wilayah::LEVEL_KOTA,
            3 => Wilayah::LEVEL_KECAMATAN,
        ];

        $created = 0;
        $updated = 0;

        foreach ($mapping as $neoLevel => $level) {
            $rows = $this->neo->getWilayah("id_level_wilayah={$neoLevel}")['data'] ?? [];

            foreach ($rows as $row) {
                $kode = trim((string) ($row['id_wilayah'] ?? ''));
                if ($kode === '') {
                    continue;
                }

                $induk = trim((string) ($row['id_induk_wilayah'] ?? ''));
                $parentId = $induk !== '' ? Wilayah::where('kode', $induk)->value('id') : null;

                $attributes = [
                    'nama' => $row['nama_wilayah'],
                    'level' => $level,
                    'parent_id' => $parentId,
                ];

                $exists = Wilayah::where('kode', $kode)->exists();

                Wilayah::updateOrCreate(['kode' => $kode], $attributes);

                $exists ? $updated++ : $created++;
            }
        }

        return ['created' => $created, 'updated' => $updated];
    }
}