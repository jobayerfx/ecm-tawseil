<?php

use Illuminate\Support\Facades\Route;
use Modules\ServerManager\Http\Controllers\ServerManagerController;
use Modules\ServerManager\Http\Controllers\HostingController;
use Modules\ServerManager\Http\Controllers\DomainController;
use Modules\ServerManager\Http\Controllers\ProviderController;

/*
|--------------------------------------------------------------------------
| Server Manager Module Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for the Server Manager module.
| These routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group.
|
*/

Route::group(['middleware' => 'auth', 'prefix' => 'account'], function () {

    Route::group(
        ['prefix' => 'server-manager'],
        function () {

            // Main Server Manager routes
            Route::get('/', [ServerManagerController::class, 'index'])->name('server-manager.index');
            Route::get('statistics', [ServerManagerController::class, 'getStatistics'])->name('server-manager.statistics');
            Route::get('activities', [ServerManagerController::class, 'getRecentActivities'])->name('server-manager.activities');

            // hosting routes
            Route::group(
                ['prefix' => 'hosting'],
                function () {
                    Route::get('export-all', [HostingController::class, 'exportAllHostings'])->name('server-manager.hosting.export_all');
                    Route::post('apply-quick-action', [HostingController::class, 'applyQuickAction'])->name('server-manager.hosting.apply_quick_action');
                    Route::post('change-status', [HostingController::class, 'changeStatus'])->name('server-manager.hosting.change_status');
                }
            );
            Route::resource('hosting', HostingController::class);

            // domain routes
            Route::group(
                ['prefix' => 'domain'],
                function () {
                    Route::get('export-all', [DomainController::class, 'exportAllDomains'])->name('server-manager.domain.export_all');
                    Route::post('apply-quick-action', [DomainController::class, 'applyQuickAction'])->name('server-manager.domain.apply_quick_action');
                    Route::post('change-status', [DomainController::class, 'changeStatus'])->name('server-manager.domain.change_status');

                    // DNS lookup routes
                    Route::get('{id}/dns-details', [DomainController::class, 'getDnsDetails'])->name('server-manager.domain.dns-details');
                    Route::get('{id}/dns-health', [DomainController::class, 'getDnsHealth'])->name('server-manager.domain.dns-health');

                }
            );
            Route::resource('domain', DomainController::class);

            // provider routes
            Route::group(
                ['prefix' => 'provider'],
                function () {
                    Route::get('export-all', [ProviderController::class, 'exportAllProviders'])->name('server-manager.provider.export_all');
                    Route::get('get-url', [ProviderController::class, 'getProviderUrl'])->name('server-manager.provider.get-url');
                    Route::post('apply-quick-action', [ProviderController::class, 'applyQuickAction'])->name('server-manager.provider.apply_quick_action');
                    Route::post('change-status', [ProviderController::class, 'changeStatus'])->name('server-manager.provider.change_status');
                }
            );
            Route::resource('provider', ProviderController::class);
        }
    );
});

