<?php

namespace Modules\Recruit\Entities;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RecruitCustomFieldData extends BaseModel
{
    use HasFactory;

    protected $table = 'recruit_custom_fields_data';


}