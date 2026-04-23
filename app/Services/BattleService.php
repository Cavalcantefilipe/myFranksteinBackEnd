<?php

namespace App\Services;

use App\Models\Battle;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class BattleService
{
    public function __construct(private PokemonService $pokemon) {}

    public function simulate(array $blueTeam, array $redTeam): Battle
    {
        $bluePayload = array_map(fn ($p) => $this->buildEntry($p), $blueTeam);
        $redPayload = array_map(fn ($p) => $this->buildEntry($p), $redTeam);

        $url = rtrim(config('services.battle_sim.url'), '/');
        $token = config('services.battle_sim.token');

        $request = Http::timeout(60)->acceptJson();
        if (!empty($token)) {
            $request = $request->withToken($token);
        }

        $response = $request->post("{$url}/simulate", [
            'format' => 'gen9customgame',
            'blueTeam' => $bluePayload,
            'redTeam' => $redPayload,
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Battle simulation failed: ' . $response->body());
        }

        $result = $response->json();

        return Battle::create([
            'blue_team' => $bluePayload,
            'red_team' => $redPayload,
            'winner' => $result['winner'] ?? null,
            'turns' => $result['turns'] ?? 0,
            'log' => $result['log'] ?? [],
        ]);
    }

    private function buildEntry(array|string $pokemon): array
    {
        if (is_string($pokemon)) {
            $pokemon = ['species' => $pokemon];
        }

        $species = strtolower(trim($pokemon['species'] ?? $pokemon['name'] ?? ''));
        if ($species === '') {
            throw new RuntimeException('Pokemon entry missing species');
        }

        $moves = $pokemon['moves'] ?? null;
        if (!$moves) {
            $moves = $this->pickMovesFromPokeApi($species);
        }

        return [
            'species' => $species,
            'moves' => $moves,
            'ability' => $pokemon['ability'] ?? null,
            'item' => $pokemon['item'] ?? null,
            'level' => $pokemon['level'] ?? 50,
            'nature' => $pokemon['nature'] ?? null,
        ];
    }

    private function pickMovesFromPokeApi(string $species): array
    {
        $details = $this->pokemon->details($species);

        $moves = collect($details['moves'] ?? [])
            ->map(fn ($m) => str_replace('-', '', $m['move']['name'] ?? ''))
            ->filter()
            ->take(4)
            ->values()
            ->all();

        return $moves ?: ['tackle'];
    }
}
