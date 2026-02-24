<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

//Routes publiques

Route::post("/register", [AuthController::class, 'register'] );
Route::post("/login", [AuthController::class, 'login'] );


//Routes Protégées

Route::middleware('auth:sanctum')->group(function (){

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/me', [AuthController::class, 'me']);

    Route::get('/applications/stats',      [ApplicationController::class, 'stats']);
    Route::get('/applications/follow-ups', [ApplicationController::class, 'followUps']);

    Route::get('/applications',                        [ApplicationController::class, 'index']);
    Route::post('/applications',                       [ApplicationController::class, 'store']);
    Route::get('/applications/{application}',          [ApplicationController::class, 'show']);
    Route::put('/applications/{application}',          [ApplicationController::class, 'update']);
    Route::delete('/applications/{application}',       [ApplicationController::class, 'destroy']);
    Route::patch('/applications/{application}/status', [ApplicationController::class, 'updateStatus']);
});