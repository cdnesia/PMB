<?php

namespace App\Services;

use App\Http\Middleware\SetLocale;
use Stichoza\GoogleTranslate\Exceptions\LargeTextException;
use Stichoza\GoogleTranslate\Exceptions\RateLimitException;
use Stichoza\GoogleTranslate\Exceptions\TranslationRequestException;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Throwable;

/**
 * Draft otomatis terjemahan konten yang diinput admin (nama dokumen, label syarat, dsb),
 * memakai endpoint gratis Google Translate (tanpa API key). Hasilnya draft awal yang tetap
 * bisa dikoreksi admin di form — bukan sumber kebenaran, jadi kegagalan endpoint tidak boleh
 * menggagalkan penyimpanan data.
 */
class AutoTranslateService
{
    /**
     * Istilah domain PMB yang terjemahan mesinnya sering keliru/kaku (mis. "Gelombang" jadi
     * "Wave" secara harfiah, padahal maksudnya "Batch" pendaftaran). Dicek sebagai awalan kalimat
     * (mis. "Gelombang 1") sebelum diserahkan ke Google Translate, sisanya tetap diterjemahkan.
     */
    private const GLOSARIUM_AWALAN = [
        'gelombang' => ['en' => 'Batch', 'ar' => 'الدفعة', 'zh' => '批次'],
    ];

    /**
     * Terjemahkan $text (berbahasa Indonesia) ke semua locale selain 'id'.
     * Locale yang gagal diterjemahkan (mis. endpoint down) dilewati, tidak melempar exception.
     *
     * @return array<string, string>
     */
    public function keSemuaLocale(string $text, string $localeSumber = 'id'): array
    {
        $text = trim($text);
        if ($text === '') {
            return [];
        }

        $hasil = [];

        foreach (SetLocale::SUPPORTED as $locale) {
            if ($locale === $localeSumber) {
                continue;
            }

            $terjemahan = $this->terjemahkan($text, $locale, $localeSumber);
            if ($terjemahan !== null) {
                $hasil[$locale] = $terjemahan;
            }
        }

        return $hasil;
    }

    public function terjemahkan(string $text, string $target, string $source = 'id'): ?string
    {
        $dariGlosarium = $this->terapkanGlosarium($text, $target);
        if ($dariGlosarium !== null) {
            return $dariGlosarium;
        }

        try {
            $hasil = (new GoogleTranslate($target, $source))->translate($text);

            return $hasil !== '' ? $hasil : null;
        } catch (LargeTextException|RateLimitException|TranslationRequestException|Throwable) {
            return null;
        }
    }

    /**
     * Ganti awalan istilah domain (mis. "Gelombang 1" → "Batch 1") memakai glosarium tetap,
     * tanpa memanggil Google Translate untuk istilah tsb. Null bila tidak ada yang cocok.
     */
    private function terapkanGlosarium(string $text, string $target): ?string
    {
        foreach (self::GLOSARIUM_AWALAN as $awalan => $terjemahan) {
            if (! isset($terjemahan[$target])) {
                continue;
            }

            if (preg_match('/^'.preg_quote($awalan, '/').'\s+(.+)$/iu', $text, $cocok)) {
                return $terjemahan[$target].' '.$cocok[1];
            }

            if (mb_strtolower($text) === $awalan) {
                return $terjemahan[$target];
            }
        }

        return null;
    }
}
