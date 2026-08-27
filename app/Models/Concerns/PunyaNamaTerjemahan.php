<?php

namespace App\Models\Concerns;

use App\Services\AutoTranslateService;
use Illuminate\Support\Facades\Cache;

/**
 * Terjemahan `nama` ke locale aktif secara live (tanpa disimpan di database),
 * dicache per teks+locale supaya tidak memanggil Google Translate berulang-ulang.
 */
trait PunyaNamaTerjemahan
{
    public function namaLokal(): string
    {
        $locale = app()->getLocale();

        if ($locale === 'id' || trim($this->nama) === '') {
            return $this->nama;
        }

        return Cache::rememberForever(
            "terjemahan:{$locale}:".md5($this->nama),
            fn () => app(AutoTranslateService::class)->terjemahkan($this->nama, $locale) ?? $this->nama
        );
    }
}
