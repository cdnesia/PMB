<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Balas sukses sebagai JSON untuk request AJAX (modal CRUD berbasis
     * fetch), atau redirect dengan flash message seperti biasa untuk
     * request halaman penuh.
     */
    protected function ajaxSuccess(Request $request, string $message, string $route, array $params = [], int $status = 200): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return redirect()->route($route, $params)->with('success', $message);
    }
}
