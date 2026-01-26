<?php

namespace Modules\Onboarding\Entities;

use App\Models\BaseModel;
use App\Models\ModuleSetting;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OnboardingSetting extends BaseModel
{
    use HasFactory;

    const MODULE_NAME = 'onboarding';

    public static function addModuleSetting($company)
    {
        $roles = ['employee', 'admin'];
        ModuleSetting::createRoleSettingEntry(self::MODULE_NAME, $roles, $company);
    }
}
