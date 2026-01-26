<?php

namespace Modules\Aitools\Entities;

use App\Models\BaseModel;
use App\Models\ModuleSetting;
use App\Traits\HasCompany;

/**
 * Modules\Aitools\Entities\AiToolsSetting
 *
 * @property int $id
 * @property int|null $company_id
 * @property string|null $chatgpt_api_key
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @mixin \Eloquent
 */
class AiToolsSetting extends BaseModel
{
    use HasCompany;

    protected $guarded = ['id'];

    protected $casts = [
        'chatgpt_api_key' => 'encrypted',
    ];

    const MODULE_NAME = 'aitools';

    public static function addModuleSetting($company)
    {
        $roles = ['employee', 'admin', 'client'];
        ModuleSetting::createRoleSettingEntry(self::MODULE_NAME, $roles, $company);
    }
}

