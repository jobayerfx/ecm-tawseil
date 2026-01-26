<?php

namespace Modules\Recruit\Entities;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasCompany;
use Modules\Recruit\Observers\RecruitCustomQuestionObserver;

class RecruitCustomQuestion extends BaseModel
{
    use HasFactory, HasCompany;

    protected $guarded = ['id'];

    protected $fillable = [];

    public static function boot()
    {
        parent::boot();
        static::observe(RecruitCustomQuestionObserver::class);
    }
}
