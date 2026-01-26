<?php

namespace Modules\GroupMessage\Entities;

use App\Models\User;
use App\Models\BaseModel;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Channel extends BaseModel
{
    use HasCompany;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['company_id', 'owner_id', 'name', 'description'];

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

}
