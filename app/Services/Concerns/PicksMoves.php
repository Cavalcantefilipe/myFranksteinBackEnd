<?php

namespace App\Services\Concerns;

trait PicksMoves
{
    protected function pickMovesFromPokeApi(string $species, int $count = 10): array
    {
        $details = $this->pokemon->details($species);

        $allMoves = collect($details['moves'] ?? [])
            ->map(fn ($m) => str_replace('-', '', $m['move']['name'] ?? ''))
            ->filter()
            ->values();

        if ($allMoves->isEmpty()) {
            return ['tackle'];
        }

        return $allMoves->shuffle()->take($count)->values()->all();
    }
}
