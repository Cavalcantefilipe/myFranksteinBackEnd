<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Battle;
use App\Services\BattleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BattleController extends Controller
{
    public function __construct(private BattleService $service) {}

    public function simulate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'blueTeam' => 'required|array|min:1|max:6',
            'redTeam' => 'required|array|min:1|max:6',
            'blueTeam.*' => 'required',
            'redTeam.*' => 'required',
        ]);

        $battle = $this->service->simulate(
            $validated['blueTeam'],
            $validated['redTeam'],
        );

        return response()->json([
            'id' => $battle->id,
            'winner' => $battle->winner,
            'turns' => $battle->turns,
            'log' => $battle->log,
        ]);
    }

    public function show(Battle $battle): JsonResponse
    {
        return response()->json($battle);
    }

    public function recent(): JsonResponse
    {
        $battles = Battle::query()
            ->latest()
            ->limit(20)
            ->get(['id', 'winner', 'turns', 'blue_team', 'red_team', 'created_at']);

        return response()->json(['results' => $battles]);
    }
}
