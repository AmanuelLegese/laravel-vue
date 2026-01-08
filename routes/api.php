<?php

use App\Events\TestEvent;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


    Route::post('register', AuthController::class . '@register');

    Route::post('login', AuthController::class . '@login');

    Route::post('logout', AuthController::class . '@logout');

    Route::get('/broadcast-test', function () {
        event(new TestEvent('Hello Vue + Reverb + larvel + cors!'));
        return 'Event broadcasted!';
    });

    /**
     * protected routes
     */
    Route::middleware('auth:api')->group(function () {
        
        Route::apiResource('/user', App\Http\Controllers\API\UserManagerController::class);
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
        
        /**
         * items
         */
        Route::apiResource('/items', App\Http\Controllers\API\ItemController::class);
        
        /**
         * categories
         */
        Route::apiResource('/categories', App\Http\Controllers\API\CategoryController::class);

        /**
         * manufacturers
         */
        Route::apiResource('/manufacturers', App\Http\Controllers\API\ManufacturerController::class);

        /**
         * inventories
         */
        Route::apiResource('/inventories', App\Http\Controllers\API\InventoryController::class);

        /**
         * transactions
         */
        Route::apiResource('/transactions', App\Http\Controllers\API\TransactionController::class);

    });



Route::apiResource('/colors', App\Http\Controllers\API\ColorController::class);

Route::apiResource('/prices', App\Http\Controllers\API\PriceController::class);

Route::apiResource('/sizes', App\Http\Controllers\API\SizeController::class);

Route::apiResource('/statuses', App\Http\Controllers\API\StatusController::class);

Route::apiResource('/units', App\Http\Controllers\API\UnitController::class);
