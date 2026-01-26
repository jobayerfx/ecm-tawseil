<?php

use Illuminate\Support\Facades\Route;
use Modules\Aitools\Http\Controllers\AiToolsSettingController;
use Modules\Aitools\Http\Controllers\AiRephraseController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => 'auth', 'prefix' => 'account/settings'], function () {
    Route::resource('ai-tools-settings', AiToolsSettingController::class)->only(['index', 'update']);
    Route::post('ai-tools-settings/test-chat', [AiToolsSettingController::class, 'testChat'])->name('ai-tools-settings.test-chat');
    Route::post('ai-tools-settings/refresh-usage', [AiToolsSettingController::class, 'refreshUsage'])->name('ai-tools-settings.refresh-usage');
    Route::post('ai-tools-settings/reset-usage', [AiToolsSettingController::class, 'resetUsage'])->name('ai-tools-settings.reset-usage');
    
    // Rephrase text route - available under projects prefix for backward compatibility
    Route::group(['prefix' => 'projects'], function () {
        Route::post('rephrase-text', [AiRephraseController::class, 'rephraseText'])->name('projects.rephrase-text');
    });
});

