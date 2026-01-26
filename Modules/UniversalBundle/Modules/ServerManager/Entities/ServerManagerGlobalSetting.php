<?php

namespace Modules\ServerManager\Entities;

use Illuminate\Database\Eloquent\Model;

class ServerManagerGlobalSetting extends Model
{
    const MODULE_NAME = 'servermanager';

    protected $table = 'server_manager_global_settings';

    protected $guarded = ['id'];
}
