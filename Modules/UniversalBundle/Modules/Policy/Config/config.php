<?php

$addOnOf = 'worksuite-new';
$product = $addOnOf . '-policy-module';

return [
    'name' => 'Policy',
    'verification_required' => true,
    'envato_item_id' => '61118284',
    'parent_envato_id' => 20052522,
    'parent_min_version' => '5.2.21',
    'script_name' => $product,
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\Policy\Entities\PolicySetting::class,
];
