<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

    Route::post('register', AuthController::class . '@register');

    Route::post('login', AuthController::class . '@login');


    /**
     * protected routes
     */
    Route::middleware('auth:api')->group(function () {
        Route::apiResource('/items', App\Http\Controllers\API\ItemController::class);
    });
