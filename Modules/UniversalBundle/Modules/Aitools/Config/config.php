<?php

$addOnOf = 'worksuite-new';

return [
    'name' => 'Aitools',
    'verification_required' => true,
    'envato_item_id' => 61555903,
    'parent_envato_id' => 20052522,
    'parent_min_version' => '5.5.23',
    'script_name' => $addOnOf . '-aitools-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\Aitools\Entities\AiToolsGlobalSetting::class,
];
