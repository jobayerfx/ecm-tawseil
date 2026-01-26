<?php

use Illuminate\Support\Facades\Route;
use Modules\Onboarding\Entities\OnboardingNotificationSetting;
use Modules\Onboarding\Http\Controllers\OffboardingSettingController;
use Modules\Onboarding\Http\Controllers\OnboardingCompletedTaskController;
use Modules\Onboarding\Http\Controllers\OnboardingCompletedTaskFileController;
use Modules\Onboarding\Http\Controllers\OnboardingDashboardController;
use Modules\Onboarding\Http\Controllers\OnboardingSettingController;

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

Route::group(['middleware' => 'auth'], function () {
    Route::post('onboarding-dashboard/start-onboarding', [OnboardingCompletedTaskController::class, 'startOnboarding'])->name('start.onboarding');
    Route::post('onboarding-dashboard/start-offboarding', [OnboardingCompletedTaskController::class, 'startOffboarding'])->name('start.offboarding');
    Route::get('onboarding-dashboard/view-file/{file}', [OnboardingCompletedTaskController::class, 'viewFile'])->name('view.file');
    Route::post('onboarding-cancel-request', [OnboardingCompletedTaskController::class, 'cancelRequest'])->name('onboarding-cancel-request');
    Route::post('onboarding-completeall-request', [OnboardingCompletedTaskController::class, 'completeAllRequest'])->name('onboarding-completeall-request');
    Route::post('onboarding-completealloffboarding-request', [OnboardingCompletedTaskController::class, 'completeAllOffboardingRequest'])->name('onboarding-completealloffboarding-request');
    
    // New approval/rejection routes
    Route::post('onboarding-submit-task', [OnboardingCompletedTaskController::class, 'submitTask'])->name('onboarding-submit-task');
    Route::post('onboarding-approve-task', [OnboardingCompletedTaskController::class, 'approveTask'])->name('onboarding-approve-task');
    Route::post('onboarding-reject-task', [OnboardingCompletedTaskController::class, 'rejectTask'])->name('onboarding-reject-task');
    Route::post('onboarding-cancel-task', [OnboardingCompletedTaskController::class, 'cancelTask'])->name('onboarding-cancel-task');
    
    Route::resource('onboarding-dashboard', OnboardingCompletedTaskController::class);

    Route::post('onboarding-settings/onboarding-settings/priority', 'OnboardingSettingController@addPriority')->name('onboarding-settings.priority');
    Route::post('onboarding-settings-notification/{id}', 'OnboardingSettingController@updateNotification')->name('onboarding-settings-notification');
    Route::resource('onboarding-settings', OnboardingSettingController::class);
});
