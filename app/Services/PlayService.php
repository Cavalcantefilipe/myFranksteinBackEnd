<?php

namespace App\Services;

use App\Models\Battle;
use App\Services\Concerns\PicksMoves;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PlayService
{
    use PicksMoves;

    public function __construct(protected PokemonService $pokemon) {}

    public function create(array $blueTeam, array $redTeam): array
    {
        $bluePayload = array_map(fn ($p) => $this->buildEntry($p), $blueTeam);
        $redPayload = array_map(fn ($p) => $this->buildEntry($p), $redTeam);

        $response = $this->client()->post('/battles', [
            'format' => 'gen9customgame',
            'blueTeam' => $bluePayload,
            'redTeam' => $redPayload,
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Create battle failed: ' . $response->body());
        }

        $snapshot = $response->json();

        Battle::create([
            'external_id' => $snapshot['id'] ?? null,
            'blue_team' => $bluePayload,
            'red_team' => $redPayload,
            'winner' => $snapshot['finished'] ? ($snapshot['winner'] ?? null) : null,
            'turns' => 0,
            'log' => $snapshot['newEvents'] ?? [],
            'mode' => 'interactive',
        ]);

        return array_merge($snapshot, [
            'blueTeam' => $bluePayload,
            'redTeam' => $redPayload,
        ]);
    }

    public function choose(string $battleId, array $choice): array
    {
        $response = $this->client()->post("/battles/{$battleId}/choose", $choice);

        if ($response->status() === 404) {
            throw new RuntimeException('battle_not_found');
        }
        if ($response->failed()) {
            throw new RuntimeException('Choose failed: ' . $response->body());
        }

        $snapshot = $response->json();

        $battle = Battle::where('external_id', $snapshot['id'] ?? null)->first();
        if ($battle) {
            $mergedLog = array_merge($battle->log ?? [], $snapshot['newEvents'] ?? []);
            $update = ['log' => $mergedLog];
            if (!empty($snapshot['finished'])) {
                $update['winner'] = $snapshot['winner'] ?? null;
            }
            $battle->update($update);
        }

        return $snapshot;
    }

    private function client()
    {
        $url = rtrim(config('services.battle_sim.url'), '/');
        $token = config('services.battle_sim.token');

        $client = Http::timeout(30)->acceptJson()->baseUrl($url);
        if (!empty($token)) {
            $client = $client->withToken($token);
        }
        return $client;
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

}
