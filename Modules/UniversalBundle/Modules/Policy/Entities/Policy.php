<?php

namespace Modules\Policy\Entities;

use App\Models\Team;
use App\Models\BaseModel;
use App\Traits\HasCompany;
use App\Models\Designation;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Scopes\ActiveScope;

class Policy extends BaseModel
{
    use HasCompany, SoftDeletes;

    protected $casts = [
        'publish_date' => 'date',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $dates = ['date'];

    protected $appends = ['file_url'];

    const FILE_PATH = 'policy/file';

    public function getFileUrlAttribute()
    {
        if($this->external_link){
            return str($this->external_link)->contains('http') ? $this->external_link : asset_url_local_s3($this->external_link);
        }

        return asset_url_local_s3(Policy::FILE_PATH . '/' . $this->filename);
    }

    public function employeeAcknowledge(): HasMany
    {
        return $this->hasMany(PolicyEmployeeAcknowledged::class, 'policy_id');
    }

    public function isAcknowledge(): HasMany
    {
        return $this->hasMany(PolicyEmployeeAcknowledged::class, 'policy_id')->where('user_id', user()->id);
    }

    public static function department($ids)
    {
        $department = null;

        if ($ids != null) {
            $department = Team::whereIn('id', $ids)->pluck('team_name')->toArray();
        }

        return $department;
    }

    public static function designation($ids)
    {
        $designation = null;

        if ($ids != null) {
            $designation = Designation::whereIn('id', $ids)->pluck('name')->toArray();
        }

        return $designation;
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by')->withoutGlobalScope(ActiveScope::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->withoutGlobalScope(ActiveScope::class);
    }

}
