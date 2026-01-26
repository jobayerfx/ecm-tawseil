<?php

namespace Modules\Onboarding\Entities;

use App\Models\User;
use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingCompletedTask extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'onboarding_task_id', 
        'user_id', 
        'completed_on', 
        'file',
        'type',
        'employee_id',
        'status',
        'submission_status',
        'submitted_on',
        'approved_by',
        'approved_on',
        'rejection_reason',
        'rejected_by',
        'rejected_on'
    ];

    protected $table = 'onboarding_completed_task';

    const FILE_PATH = 'onboarding-files';

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScope(ActiveScope::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScope(ActiveScope::class);
    }

    public function onboardingTask()
    {
        return $this->belongsTo(OnboardingTask::class, 'onboarding_task_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by')->withoutGlobalScope(ActiveScope::class);
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by')->withoutGlobalScope(ActiveScope::class);
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id')->withoutGlobalScope(ActiveScope::class);
    }

    public function getFileUrlAttribute()
    {
        return (! is_null($this->external_link)) ? $this->external_link : asset_url_local_s3('onboarding-files/'.$this->onboarding_task_id.'/'.$this->hashname);
    }

    public function getLogoUrlAttribute()
    {
        return (is_null($this->file)) ? $this->company->logo_url : asset_url_local_s3('onboarding-files/' . $this->file);
    }

    public function getAuthorisedSignatorySignatureUrlAttribute()
    {
        return (is_null($this->file)) ? '' : asset_url_local_s3('onboarding-files/' . $this->file);
    }

}
