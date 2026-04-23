<?php

namespace App\Services;

use App\Models\TranslationCache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TranslateService
{
    public function translate(string $text, string $targetLang, ?string $sourceLang = null): string
    {
        $target = strtoupper($targetLang);
        $source = $sourceLang ? strtoupper($sourceLang) : null;

        $cached = TranslationCache::where('source_text', $text)
            ->where('target_lang', $target)
            ->where('source_lang', $source)
            ->first();

        if ($cached) {
            return $cached->translated_text;
        }

        $apiKey = config('services.deepl.key');
        if (empty($apiKey)) {
            throw new RuntimeException('DEEPL_API_KEY is not configured');
        }

        $base = config('services.deepl.url');
        $payload = [
            'text' => [$text],
            'target_lang' => $target,
        ];
        if ($source) {
            $payload['source_lang'] = $source;
        }

        $response = Http::timeout(15)
            ->withHeaders([
                'Authorization' => "DeepL-Auth-Key {$apiKey}",
            ])
            ->post("{$base}/translate", $payload);

        if ($response->failed()) {
            throw new RuntimeException('Translation failed: ' . $response->body());
        }

        $translated = $response->json('translations.0.text');
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
