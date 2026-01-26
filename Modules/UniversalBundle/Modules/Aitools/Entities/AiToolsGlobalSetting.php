<?php

namespace Modules\Aitools\Entities;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiToolsGlobalSetting extends BaseModel
{
    use HasFactory;

    protected $guarded = ['id'];

    const MODULE_NAME = 'aitools';

    protected $table = 'aitools_global_settings';
}
