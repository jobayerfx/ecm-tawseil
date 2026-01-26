<?php

$addOnOf = 'worksuite-new';
$product = $addOnOf . '-groupmessage-module';

return [
    'name' => 'GroupMessage',
    'verification_required' => true,
    'envato_item_id' => 61240155,
    'parent_envato_id' => 20052522,
    'parent_min_version' => '5.5.21',
    'script_name' => $product,
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\GroupMessage\Entities\GroupMessageGlobalSetting::class,
];
