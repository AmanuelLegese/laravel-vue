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
     *
     * staff (admin or pharmacist): read catalog and stock, sell, reports
     * admin only: users, roles, permissions, catalog/price/stock changes, adjustments, voids
     */
    Route::middleware('auth:api')->group(function () {

        $catalog = [
            'items' => App\Http\Controllers\API\ItemController::class,
            'categories' => App\Http\Controllers\API\CategoryController::class,
            'manufacturers' => App\Http\Controllers\API\ManufacturerController::class,
            'inventories' => App\Http\Controllers\API\InventoryController::class,
            'prices' => App\Http\Controllers\API\PriceController::class,
            'statuses' => App\Http\Controllers\API\StatusController::class,
            'units' => App\Http\Controllers\API\UnitController::class,
        ];

        Route::middleware('role:admin|pharmacist')->group(function () use ($catalog) {
            foreach ($catalog as $name => $controller) {
                Route::apiResource("/$name", $controller)->only(['index', 'show']);
            }

            Route::get('/reports/low-stock', [App\Http\Controllers\API\ReportController::class, 'lowStock']);
            Route::get('/reports/expiring', [App\Http\Controllers\API\ReportController::class, 'expiring']);
            Route::get('/stock-movements', [App\Http\Controllers\API\StockController::class, 'movements']);

            Route::apiResource('/invoices', App\Http\Controllers\API\InvoiceController::class)->only(['index', 'show']);
            Route::apiResource('/transactions', App\Http\Controllers\API\TransactionController::class)
                ->only(['index', 'show', 'store']);
        });

        Route::middleware('role:admin')->group(function () use ($catalog) {
            foreach ($catalog as $name => $controller) {
                Route::apiResource("/$name", $controller)->except(['index', 'show']);
            }

            Route::post('/stock-adjustments', [App\Http\Controllers\API\StockController::class, 'adjust']);
            Route::delete('/transactions/{transaction}', [App\Http\Controllers\API\TransactionController::class, 'destroy']);

            Route::apiResource('/user', App\Http\Controllers\API\UserManagerController::class);

            Route::get('/permission', [App\Http\Controllers\API\ManagePermission::class, 'getPermission']);
            Route::post('/grant', [App\Http\Controllers\API\ManagePermission::class, 'grantPermission']);
            Route::post('/revoke', [App\Http\Controllers\API\ManagePermission::class, 'revokePermission']);
            Route::post('/massgrant', [App\Http\Controllers\API\ManagePermission::class, 'massgrantPermission']);
            Route::post('/massrevoke', [App\Http\Controllers\API\ManagePermission::class, 'massrevokePermission']);

            Route::get('/roles', App\Http\Controllers\API\ManageRole::class);
            Route::post('/roles', [App\Http\Controllers\API\ManageRole::class, 'createRole']);
            Route::delete('/roles', [App\Http\Controllers\API\ManageRole::class, 'deleteRole']);
            Route::post('/assign-permissions-roles', [App\Http\Controllers\API\ManageRole::class, 'assignPermissionsToRole']);
            Route::post('/revoke-permissions-roles', [App\Http\Controllers\API\ManageRole::class, 'revokePermissionsFromRole']);
            Route::post('/assign-user-role', [App\Http\Controllers\API\ManageRole::class, 'assignUserToRole']);
            Route::post('/revoke-user-role', [App\Http\Controllers\API\ManageRole::class, 'revokeUserFromRole']);
        });
    });
