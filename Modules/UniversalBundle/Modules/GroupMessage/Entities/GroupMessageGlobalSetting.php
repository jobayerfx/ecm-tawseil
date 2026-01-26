<?php

namespace Modules\GroupMessage\Entities;

use App\Models\BaseModel;
use App\Models\ModuleSetting;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GroupMessageGlobalSetting extends BaseModel
{

    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];

    const MODULE_NAME = 'groupmessage';

    protected $table = 'group_message_global_settings';

    public static function addModuleSetting($company)
    {
        $roles = ['employee', 'admin', 'client'];

        try {
            ModuleSetting::createRoleSettingEntry(self::MODULE_NAME, $roles, $company);
        } catch (\Exception $e) {
            logger()->error('Error adding group message module setting: ' . $e->getMessage());
        }
    }
}
