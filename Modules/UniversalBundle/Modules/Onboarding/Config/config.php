<?php

$addOnOf = 'worksuite-new';
$product = $addOnOf . '-onboarding-module';

return [
    'name' => 'Onboarding',
    'verification_required' => true,
    'envato_item_id' => 61235480,
    'parent_envato_id' => 20052522,
    'parent_min_version' => '5.5.21',
    'script_name' => $product,
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\Onboarding\Entities\OnboardingSetting::class,
];
