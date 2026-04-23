<?php

use App\Http\Controllers\Api\BattleController;
use App\Http\Controllers\Api\PokemonController;
use App\Http\Controllers\Api\QuoteController;
use App\Http\Controllers\Api\TranslateController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['status' => 'ok']));

Route::middleware('throttle:60,1')->group(function () {
    Route::get('/quotes/random', [QuoteController::class, 'random']);

    Route::get('/pokemon', [PokemonController::class, 'index']);
    Route::get('/pokemon/{nameOrId}', [PokemonController::class, 'show']);

    Route::post('/translate', [TranslateController::class, 'translate']);

    Route::post('/battles/simulate', [BattleController::class, 'simulate']);
    Route::get('/battles/recent', [BattleController::class, 'recent']);
    Route::get('/battles/{battle}', [BattleController::class, 'show']);
});
