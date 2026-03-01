<?php

namespace App\Http\Controllers;

use App\Helpers\HelpSystem;
use Illuminate\Http\JsonResponse;

class HelpController extends Controller
{
    public function content(string $key): JsonResponse
    {
        // Sanitize: only allow safe characters
        $key = preg_replace('/[^a-zA-Z0-9_-]/', '', $key);

        return response()->json([
            'html' => HelpSystem::toHtml($key),
        ]);
    }
}
