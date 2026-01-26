<?php

use Illuminate\Support\Facades\Route;
use Modules\ServerManager\Http\Controllers\ServerManagerController;
use Modules\ServerManager\Http\Controllers\HostingController;
use Modules\ServerManager\Http\Controllers\DomainController;

Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'server-manager'], function () {
    
    Route::get('/statistics', [ServerManagerController::class, 'getStatistics']);
    Route::get('/activities', [ServerManagerController::class, 'getRecentActivities']);

    Route::group(['prefix' => 'hosting'], function () {
        Route::get('/', [HostingController::class, 'index']);
        Route::post('/', [HostingController::class, 'store']);
        Route::get('/{id}', [HostingController::class, 'show']);
        Route::put('/{id}', [HostingController::class, 'update']);
        Route::delete('/{id}', [HostingController::class, 'destroy']);
    });

    Route::group(['prefix' => 'domain'], function () {
        Route::get('/', [DomainController::class, 'index']);
        Route::post('/', [DomainController::class, 'store']);
        Route::get('/{id}', [DomainController::class, 'show']);
        Route::put('/{id}', [DomainController::class, 'update']);
        Route::delete('/{id}', [DomainController::class, 'destroy']);
    });
}); 