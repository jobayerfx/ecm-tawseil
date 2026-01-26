<?php

$addOnOf = 'worksuite-new';

return [
    'name' => 'LanguagePack',
    'verification_required' => true,
    'envato_item_id' => '48773763',
    'parent_envato_id' => 20052522,
    'parent_min_version' => '5.3.5',
    'script_name' => $addOnOf . '-languagepack-module',
    'parent_product_name' => $addOnOf,
    'setting' => \Modules\LanguagePack\Entities\LanguagePackSetting::class,
];
