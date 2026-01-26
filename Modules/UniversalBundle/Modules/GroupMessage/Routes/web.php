<?php

use Illuminate\Support\Facades\Route;
use Modules\GroupMessage\Http\Controllers\ChannelController;
use Modules\GroupMessage\Http\Controllers\GroupController;
use Modules\GroupMessage\Http\Controllers\GroupMessagingController;

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

    // Group routes
    Route::get('group-messages/fetch-group-members/{id}', [GroupController::class, 'fetchGroupMembers'])->name('group-messages.fetch-group-members');
    Route::get('group-messages/fetch-group-list', [GroupController::class, 'fetchGroupListView'])->name('group-messages.fetch-groups-list');
    Route::post('group-messages/fetch-group-messages/{id}', [GroupController::class, 'fetchGroupMessages'])->name('group-messages.fetch-group-messages');
    Route::get('group-messages/show-group-chat/{id}', [GroupController::class, 'showGroupChat'])->name('group-messages.show-group-chat');
    Route::post('group-messages/store-group-message', [GroupController::class, 'storeGroupMessage'])->name('group-messages.store-group-message');
    Route::post('group-messages/remove-group-member/{id}', [GroupController::class, 'removeMember'])->name('group-messages.remove-group-member');
    Route::resource('group-messages', GroupController::class);

    // Channelroutes
    Route::get('channel-messages/show-channel-chat/{id}', [ChannelController::class, 'showChannelChat'])->name('channel-messages.show-channel-chat');
    Route::post('channel-messages/fetch-channel-messages/{id}', [ChannelController::class, 'fetchChannelMessages'])->name('channel-messages.fetch-channel-messages');
    Route::get('channel-messages/fetch-channel-list', [ChannelController::class, 'fetchChannelListView'])->name('channel-messages.fetch-channel-list');
    Route::post('channel-messages/store-channel-messages', [ChannelController::class, 'storeChannelMessage'])->name('channel-messages.store-channel-message');
    Route::resource('channel-messages', ChannelController::class);

    // Group-messaging routes
    Route::get('group-messaging/fetch-user-list', [GroupMessagingController::class, 'fetchUserListView'])->name('group-messaging.fetch-user-list');
    // Route::post('group-messaging/check_messages', [GroupMessagingController::class, 'checkNewMessages'])->name('messages.check_new_message');
    Route::post('group-messaging/fetch-user-messages/{id}', [GroupMessagingController::class, 'fetchUserMessages'])->name('group-messaging.fetch-user-messages');
    Route::resource('group-messaging', GroupMessagingController::class);

});

