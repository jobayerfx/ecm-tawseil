<?php

namespace Modules\GroupMessage\Entities;

use App\Models\User;
use App\Models\BaseModel;
use App\Traits\HasCompany;


class GroupMessageSetting extends BaseModel
{
    use HasCompany;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];
}
