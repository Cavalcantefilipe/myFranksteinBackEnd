<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\QuoteService;
use Illuminate\Http\JsonResponse;

class QuoteController extends Controller
{
    public function __construct(private QuoteService $service) {}

    public function random(): JsonResponse
    {
        $quote = $this->service->random();
        return response()->json($quote);
    }
}
