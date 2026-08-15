<?php

return [

    /*
    |--------------------------------------------------------------------------
    | NEO Feeder (PDDikti) Web Service
    |--------------------------------------------------------------------------
    |
    | Koneksi ke web service NEO Feeder milik perguruan tinggi. Endpoint
    | menerima request JSON POST dengan field `act` (nama fungsi), `token`,
    | `filter`, `order`, `limit`, dan `offset`.
    |
    */

    'url' => env('NEOFEEDER_URL'),

    'username' => env('NEOFEEDER_USERNAME'),

    'password' => env('NEOFEEDER_PASSWORD'),

    'timeout' => env('NEOFEEDER_TIMEOUT', 30),

    // Umur token NEO Feeder ~5 jam. Cache token 4.5 jam agar tidak kadaluarsa.
    'token_ttl' => env('NEOFEEDER_TOKEN_TTL', 16200),
];
