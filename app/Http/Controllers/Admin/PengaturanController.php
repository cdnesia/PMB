<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengaturan;
use App\Services\NeoFeederSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PengaturanController extends Controller
{
    /**
     * Field pengaturan yang dapat diubah dari panel admin.
     * key => [label, tipe]
     */
    private const FIELDS = [
        'seo_title' => ['Judul SEO (title tag)', 'text'],
        'seo_description' => ['Deskripsi SEO (meta description)', 'textarea'],
        'seo_keywords' => ['Kata Kunci SEO (meta keywords)', 'text'],
        'seo_author' => ['Author', 'text'],
    ];

    public function index(): View
    {
        $values = [];
        foreach (self::FIELDS as $key => [$label]) {
            $values[$key] = Pengaturan::get($key);
        }

        return view('admin.pengaturan.index', [
            'fields' => self::FIELDS,
            'values' => $values,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = [];
        foreach (self::FIELDS as $key => [$label, $type]) {
            $rules[$key] = $type === 'textarea' ? 'nullable|string|max:1000' : 'nullable|string|max:255';
        }

        $data = $request->validate($rules);

        foreach (self::FIELDS as $key => [$label]) {
            Pengaturan::set($key, $data[$key] ?? null);
        }

        return redirect()->route('admin.pengaturan.index')->with('success', 'Pengaturan SEO berhasil disimpan.');
    }

    /**
     * Sinkronkan data referensi (agama & wilayah) dari NEO Feeder ke database
     * lokal, sehingga halaman pendaftaran tidak perlu memanggil NEO Feeder.
     */
    public function syncNeoFeeder(Request $request, NeoFeederSyncService $sync): RedirectResponse
    {
        try {
            $agama = $sync->syncAgama();
            $wilayah = $sync->syncWilayah($request->boolean('fresh'));
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('admin.pengaturan.index')
                ->with('error', 'Gagal sinkronisasi NEO Feeder: '.$e->getMessage());
        }

        $message = sprintf(
            'Sinkronisasi berhasil. Agama: %d baru, %d diperbarui. Wilayah: %d baru, %d diperbarui.',
            $agama['created'],
            $agama['updated'],
            $wilayah['created'],
            $wilayah['updated']
        );

        return redirect()->route('admin.pengaturan.index')->with('success', $message);
    }
}
