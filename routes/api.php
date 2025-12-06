<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

    Route::post('register', AuthController::class . '@register');

    Route::post('login', AuthController::class . '@login');

    Route::post('logout', AuthController::class . '@logout');

    /**
     * protected routes
     */
    Route::middleware('auth:api')->group(function () {
        Route::apiResource('/items', App\Http\Controllers\API\ItemController::class);
        /**
         * Permission Management
         */
        Route::get('/permission', [App\Http\Controllers\API\ManagePermission::class, 'getPermission']);
        Route::post('/grant', [App\Http\Controllers\API\ManagePermission::class, 'grantPermission']);
        Route::post('/revoke', [App\Http\Controllers\API\ManagePermission::class, 'revokePermission']);
        Route::post('/massgrant', [App\Http\Controllers\API\ManagePermission::class, 'massgrantPermission']);
        Route::post('/massrevoke', [App\Http\Controllers\API\ManagePermission::class, 'massrevokePermission']);
        /**
         * Role Management
         */
        Route::get('/roles', App\Http\Controllers\API\ManageRole::class);
        Route::post('/roles', [App\Http\Controllers\API\ManageRole::class, 'createRole']);
        Route::delete('/roles', [App\Http\Controllers\API\ManageRole::class, 'deleteRole']);
        Route::post('/assign-permissions-roles', [App\Http\Controllers\API\ManageRole::class, 'assignPermissionsToRole']);
        Route::post('/revoke-permissions-roles', [App\Http\Controllers\API\ManageRole::class, 'revokePermissionsFromRole']);
        Route::post('/assign-user-role', [App\Http\Controllers\API\ManageRole::class, 'assignUserToRole']);
        Route::post('/revoke-user-role', [App\Http\Controllers\API\ManageRole::class, 'revokeUserFromRole']);
    });
