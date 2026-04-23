<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class QuoteService
{
    public function random(): array
    {
        $url = config('services.quotes.url');

        $response = Http::timeout(10)->get($url);

        if ($response->failed()) {
            throw new RuntimeException('Failed to fetch random quote');
        }

        $data = $response->json();

        return [
            'id' => $data['id'] ?? null,
            'quote' => $data['quote'] ?? '',
            'author' => $data['author'] ?? '',
        ];
    }
}
