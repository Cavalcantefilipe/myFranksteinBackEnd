<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PlayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlayController extends Controller
{
    public function __construct(private PlayService $service) {}

    public function start(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'blueTeam' => 'required|array|min:1|max:6',
            'redTeam' => 'required|array|min:1|max:6',
            'blueTeam.*' => 'required',
            'redTeam.*' => 'required',
        ]);

        $snapshot = $this->service->create(
            $validated['blueTeam'],
            $validated['redTeam'],
        );

        return response()->json($snapshot);
    }

    public function choose(string $battleId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'move' => 'nullable|integer|min:1|max:4',
            'switch' => 'nullable|integer|min:1|max:6',
        ]);

        $snapshot = $this->service->choose($battleId, $validated);
        return response()->json($snapshot);
    }
}
