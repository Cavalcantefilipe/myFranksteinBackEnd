<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PokemonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PokemonController extends Controller
{
    public function __construct(private PokemonService $service) {}

    public function index(Request $request): JsonResponse
    {
        $limit = (int) $request->query('limit', 100000);
        $offset = (int) $request->query('offset', 0);

        $results = $this->service->list($limit, $offset);

        return response()->json(['results' => $results]);
    }

    public function show(string $nameOrId): JsonResponse
    {
        $details = $this->service->details($nameOrId);
        return response()->json($details);
    }
}
