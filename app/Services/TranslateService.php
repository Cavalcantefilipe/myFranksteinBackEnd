<?php

namespace App\Services;

use App\Models\TranslationCache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TranslateService
{
    public function translate(string $text, string $targetLang, ?string $sourceLang = null): string
    {
        $target = strtolower($targetLang);
        $source = $sourceLang ? strtolower($sourceLang) : null;

        $cached = TranslationCache::where('source_text', $text)
            ->where('target_lang', $target)
            ->where('source_lang', $source)
            ->first();

        if ($cached) {
            return $cached->translated_text;
        }

        $apiKey = config('services.google_translate.key');
        if (empty($apiKey)) {
            throw new RuntimeException('GOOGLE_TRANSLATE_API_KEY is not configured');
        }

        $base = config('services.google_translate.url');

        $payload = [
            'q' => $text,
            'target' => $target,
            'format' => 'text',
        ];
        if ($source) {
            $payload['source'] = $source;
        }

        $response = Http::timeout(15)
            ->withHeaders(['X-Goog-Api-Key' => $apiKey])
            ->asJson()
            ->post($base, $payload);

        if ($response->failed()) {
            throw new RuntimeException('Translation failed: ' . $response->body());
        }

        $translated = $response->json('data.translations.0.translatedText');
        if (!$translated) {
            throw new RuntimeException('Translation returned no text');
        }

        TranslationCache::create([
            'source_text' => $text,
            'target_lang' => $target,
            'source_lang' => $source,
            'translated_text' => $translated,
        ]);

        return $translated;
    }
}
