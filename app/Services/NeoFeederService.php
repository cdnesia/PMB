<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Klien HTTP untuk web service NEO Feeder (PDDikti).
 *
 * Endpoint menerima request JSON POST dengan payload:
 *   { "act": "<nama fungsi>", "token": "...", "filter": "...", "order": "...", "limit": "...", "offset": "..." }
 *
 * Alur autentikasi: panggil `GetToken` (username + password) untuk mendapatkan
 * token JWT, lalu sertakan token tersebut pada setiap pemanggilan fungsi lain.
 */
class NeoFeederService
{
    /**
     * Panggil suatu fungsi NEO Feeder dan kembalikan payload JSON utuh
     * (termasuk key `error_code`, `error_desc`, `jumlah`, dan `data`).
     */
    public function call(string $act, array $params = []): array
    {
        $payload = array_merge(['act' => $act], $params);

        if ($act !== 'GetToken' && ! array_key_exists('token', $payload)) {
            $payload['token'] = $this->token();
        }

        return $this->post($payload);
    }

    /**
     * Ambil token autentikasi (di-cache selama config `neofeeder.token_ttl`).
     */
    public function token(): string
    {
        return Cache::remember('neofeeder.token', (int) config('neofeeder.token_ttl', 16200), function () {
            $data = $this->post([
                'act' => 'GetToken',
                'username' => config('neofeeder.username'),
                'password' => config('neofeeder.password'),
            ]);

            $token = $data['data']['token'] ?? null;

            if (! $token) {
                throw new RuntimeException('NEO Feeder: GetToken tidak mengembalikan token.');
            }

            return $token;
        });
    }

    /**
     * Hapus token yang di-cache (dipakai bila token dianggap kadaluarsa).
     */
    public function forgetToken(): void
    {
        Cache::forget('neofeeder.token');
    }

    /**
     * Ambil data wilayah. Level wilayah NEO Feeder:
     *   0 = Negara, 1 = Propinsi, 2 = Kab/Kota, 3 = Kecamatan.
     */
    public function getWilayah(?string $filter = null, ?int $limit = null, int $offset = 0): array
    {
        return $this->call('GetWilayah', [
            'filter' => $filter ?? '',
            'order' => '',
            'limit' => $limit === null ? '' : (string) $limit,
            'offset' => (string) $offset,
        ]);
    }

    /**
     * Ambil daftar level wilayah (untuk referensi / pemetaan).
     */
    public function getLevelWilayah(): array
    {
        return $this->call('GetLevelWilayah', [
            'filter' => '',
            'order' => '',
            'limit' => '',
            'offset' => '0',
        ]);
    }

    /**
     * Ambil data program studi.
     *
     * Field per baris: id_prodi (UUID), kode_program_studi, nama_program_studi,
     * status (A = aktif), id_jenjang_pendidikan, nama_jenjang_pendidikan.
     */
    public function getProdi(?string $filter = null, ?int $limit = null, int $offset = 0): array
    {
        return $this->call('GetProdi', [
            'filter' => $filter ?? '',
            'order' => '',
            'limit' => $limit === null ? '' : (string) $limit,
            'offset' => (string) $offset,
        ]);
    }

    /**
     * Ambil kamus referensi NEO Feeder (di-cache 1 hari — data referensi
     * jarang berubah). Mengembalikan array `data` (daftar objek).
     */
    public function getReference(string $act): array
    {
        return Cache::remember('neofeeder.ref.'.$act, now()->addDay(), function () use ($act) {
            $data = $this->call($act, [
                'filter' => '',
                'order' => '',
                'limit' => '',
                'offset' => '0',
            ]);

            $rows = $data['data'] ?? [];

            // Urutkan berdasarkan kode/id (key `id_*`) agar dropdown tampil rapi.
            usort($rows, function (array $a, array $b) {
                return strnatcmp($this->refId($a), $this->refId($b));
            });

            return $rows;
        });
    }

    /**
     * Ambil nilai kode referensi (key `id_*`) dari satu baris dictionary.
     */
    private function refId(array $row): string
    {
        foreach ($row as $key => $value) {
            if (is_string($key) && str_starts_with($key, 'id_')) {
                return (string) $value;
            }
        }

        return '';
    }

    /** Agama (id_agama + nama_agama). */
    public function getAgama(): array
    {
        return $this->getReference('GetAgama');
    }

    /** Jenis tinggal (id_jenis_tinggal + nama_jenis_tinggal). */
    public function getJenisTinggal(): array
    {
        return $this->getReference('GetJenisTinggal');
    }

    /** Alat transportasi (id_alat_transportasi + nama_alat_transportasi). */
    public function getAlatTransportasi(): array
    {
        return $this->getReference('GetAlatTransportasi');
    }

    /** Pembiayaan (id_pembiayaan + nama_pembiayaan). */
    public function getPembiayaan(): array
    {
        return $this->getReference('GetPembiayaan');
    }

    /** Pekerjaan (id_pekerjaan + nama_pekerjaan). */
    public function getPekerjaan(): array
    {
        return $this->getReference('GetPekerjaan');
    }

    /** Penghasilan (id_penghasilan + nama_penghasilan). */
    public function getPenghasilan(): array
    {
        return $this->getReference('GetPenghasilan');
    }

    /** Negara (id_negara + nama_negara). */
    public function getNegara(): array
    {
        return $this->getReference('GetNegara');
    }

    /**
     * Kirim request POST dan validasi respons NEO Feeder.
     */
    private function post(array $payload): array
    {
        $response = Http::timeout((int) config('neofeeder.timeout', 30))
            ->withoutVerifying()
            ->asJson()
            ->post((string) config('neofeeder.url'), $payload);

        if ($response->failed()) {
            throw new RuntimeException('NEO Feeder HTTP '.$response->status().': '.$response->body());
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw new RuntimeException('NEO Feeder: respons bukan JSON yang valid.');
        }

        if (($data['error_code'] ?? null) !== 0) {
            throw new RuntimeException('NEO Feeder error: '.($data['error_desc'] ?? json_encode($data)));
        }

        return $data;
    }
}
