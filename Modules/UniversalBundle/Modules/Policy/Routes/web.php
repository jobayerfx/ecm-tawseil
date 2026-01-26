<?php

use Illuminate\Support\Facades\Route;
use Modules\Policy\Http\Controllers\PolicyController;
use Modules\Policy\Http\Controllers\PolicyFileController;

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

Route::group(['middleware' => 'auth', 'prefix' => 'account'], function () {

    Route::get('policy-file/download/{id}', [PolicyFileController::class, 'download'])->name('policy-file.download');
    Route::resource('policy-file', PolicyFileController::class);

    Route::post('policy/send-reminder/{id}', [PolicyController::class, 'sendRemainder'])->name('policy.send_remainder');
    Route::post('policy/publish-pilocy/{id}', [PolicyController::class, 'publishPolicy'])->name('policy.publish');
    Route::post('policy/archive-delete/{id}', [PolicyController::class, 'archiveDestroy'])->name('policy.archive_delete');
    Route::post('policy/archive-restore/{id}', [PolicyController::class, 'archiveRestore'])->name('policy.archive_restore');
    Route::get('policy/archive', [PolicyController::class, 'archive'])->name('policy.archive');
    Route::get('policy-signature/{id}', [PolicyController::class, 'policySign'])->name('policy.sign');
    Route::get('policy/download/{id}/{userId}', [PolicyController::class, 'download'])->name('policy.download');
    Route::post('policy-signature/{id}', [PolicyController::class, 'policySignStore'])->name('policy.signStore');
    Route::post('policy-acknowledge/{id}', [PolicyController::class, 'policyAcknowledge'])->name('policy.acknowledge');
    Route::get('policy/download-file/{id}/{userId}', [PolicyController::class, 'downloadFile'])->name('policy.downloadFile');
    Route::resource('policy', PolicyController::class);

});
