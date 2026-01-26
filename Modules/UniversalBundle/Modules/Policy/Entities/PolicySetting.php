<?php

namespace Modules\Policy\Entities;

use App\Models\ModuleSetting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Policy\Database\factories\PolicySettingFactory;

class PolicySetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];

    const MODULE_NAME = 'policy';

    protected $table = 'policy_settings';

    public static function addModuleSetting($company)
    {
        $roles = ['employee', 'admin'];
        ModuleSetting::createRoleSettingEntry(self::MODULE_NAME, $roles, $company);
    }
}
