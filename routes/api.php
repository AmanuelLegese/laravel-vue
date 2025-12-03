<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('/run_jobs', App\Http\Controllers\API\RunJobController::class);

Route::apiResource('/post_jobs', App\Http\Controllers\API\PostJobController::class);

Route::apiResource('/post', App\Http\Controllers\API\PostController::class);

Route::post('register', function (Request $request) {
    return (new AuthController())->register($request);
});

Route::post('login', function (Request $request) {
    return (new AuthController())->login($request);
});