<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TranslateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TranslateController extends Controller
{
    public function __construct(private TranslateService $service) {}

    public function translate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'text' => 'required|string|max:5000',
            'target_lang' => 'required|string|size:2|alpha',
            'source_lang' => 'nullable|string|size:2|alpha',
        ]);

        $translated = $this->service->translate(
            $validated['text'],
            $validated['target_lang'],
            $validated['source_lang'] ?? null,
        );

        return response()->json(['translated_text' => $translated]);
    }
}
