<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Bahasa yang tersedia untuk sisi pendaftar/mahasiswa. Panel admin & referrer
     * sengaja tidak diberi switcher sehingga selalu memakai locale default (id).
     */
    public const SUPPORTED = ['id', 'en', 'ar', 'zh'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale', config('app.locale'));

        if (! in_array($locale, self::SUPPORTED, true)) {
            $locale = config('app.locale');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
