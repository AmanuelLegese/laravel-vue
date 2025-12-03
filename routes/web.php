<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/', function () {
    return response()->json(['message'=>'welcome']);
});
