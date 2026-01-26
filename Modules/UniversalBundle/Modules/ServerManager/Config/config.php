<?php

$addOnOf = 'worksuite-new';

return [
    /*
    |--------------------------------------------------------------------------
    | Server Manager Module Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the Server Manager module
    | which handles both hosting and domain management.
    |
    */

    'name' => 'ServerManager',
    'verification_required' => true,
    'envato_item_id' => 59595964,
    'parent_envato_id' => 20052522,
    'parent_min_version' => '5.2.3',
    'script_name' => $addOnOf . '-servermanager-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\ServerManager\Entities\ServerManagerGlobalSetting::class,

    /*
    |--------------------------------------------------------------------------
    | Hosting Management Settings
    |--------------------------------------------------------------------------
    */
    'hosting' => [
        'enabled' => true,
        'default_status' => 'active',
        'auto_renewal_reminder_days' => 30,
        'ssl_reminder_days' => 60,
        'backup_reminder_days' => 7,
    ],

    /*
    |--------------------------------------------------------------------------
    | Domain Management Settings
    |--------------------------------------------------------------------------
    */
    'domain' => [
        'enabled' => true,
        'default_status' => 'active',
        'expiry_reminder_days' => 30,
        'auto_renewal_reminder_days' => 45,
        'dns_check_interval' => 24, // hours
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'email_enabled' => true,
        'sms_enabled' => false,
        'in_app_enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | API Settings
    |--------------------------------------------------------------------------
    */
    'api' => [
        'enabled' => true,
        'rate_limit' => 60, // requests per minute
    ],
];
