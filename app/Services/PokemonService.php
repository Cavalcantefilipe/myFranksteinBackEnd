<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PokemonService
{
    public function list(int $limit = 100000, int $offset = 0): array
    {
        $cacheKey = "pokemon:list:{$limit}:{$offset}";

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($limit, $offset) {
            $base = config('services.pokeapi.url');
            $response = Http::timeout(15)->get("{$base}/pokemon", [
                'limit' => $limit,
                'offset' => $offset,
            ]);

            if ($response->failed()) {
                throw new RuntimeException('Failed to fetch Pokemon list');
            }

            return $response->json('results') ?? [];
        });
    }

    public function details(string $nameOrId): array
    {
        $key = strtolower(trim($nameOrId));
        $cacheKey = "pokemon:details:{$key}";

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($key) {
            $base = config('services.pokeapi.url');
            $response = Http::timeout(15)->get("{$base}/pokemon/{$key}");

            if ($response->status() === 404) {
                throw new RuntimeException("Pokemon not found: {$key}");
            }

            if ($response->failed()) {
                throw new RuntimeException('Failed to fetch Pokemon details');
            }

            return $response->json();
        });
    }
}
