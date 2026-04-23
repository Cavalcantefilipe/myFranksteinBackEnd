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
            'target_lang' => 'required|string|max:10|regex:/^[A-Za-z]{2}(-[A-Za-z]{2,4})?$/',
            'source_lang' => 'nullable|string|max:10|regex:/^[A-Za-z]{2}(-[A-Za-z]{2,4})?$/',
        ]);

        $translated = $this->service->translate(
            $validated['text'],
            $validated['target_lang'],
            $validated['source_lang'] ?? null,
        );

        return response()->json(['translated_text' => $translated]);
    }
}
