<?php

namespace Modules\Onboarding\Entities;

use App\Models\User;
use App\Scopes\ActiveScope;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OnboardingTask extends Model
{
    use HasFactory, HasCompany;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['title', 'task_for', 'employee_can_see', 'type', 'column_priority', 'added_by'];

    protected $table = 'onboarding_tasks';

    const MODULE_NAME = 'onboarding';

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by')->withoutGlobalScope(ActiveScope::class);
    }

    public function completedTask()
    {
        return $this->hasOne(OnboardingCompletedTask::class, 'onboarding_task_id');
    }

    public static function createDefaultTasks($company)
    {
        self::insert([
            [
                'title' => 'Assign Laptop/ Assets / Joining kit',
                'task_for' => 'company',
                'employee_can_see' => true,
                'type' => 'onboard',
                'column_priority' => 1,
                'company_id' => $company->id,
            ],
            [
                'title' => 'Collect document (marksheet/ pan card / Aadhar card/salary slip/ Bank statement)',
                'task_for' => 'company',
                'employee_can_see' => true,
                'type' => 'onboard',
                'column_priority' => 2,
                'company_id' => $company->id,
            ],
            [
                'title' => 'Sign Document (bond/ acceptance letter)',
                'task_for' => 'employee',
                'employee_can_see' => true,
                'type' => 'onboard',
                'column_priority' => 3,
                'company_id' => $company->id,
            ],
            [
                'title' => 'Provide Documents (Joining letter /welcome letter)',
                'task_for' => 'company',
                'employee_can_see' => true,
                'type' => 'onboard',
                'column_priority' => 4,
                'company_id' => $company->id,
            ],
            [
                'title' => 'Collect Assets',
                'task_for' => 'company',
                'employee_can_see' => true,
                'type' => 'offboard',
                'column_priority' => 1,
                'company_id' => $company->id,
            ],
            [
                'title' => 'Sign No Dues Certificate',
                'task_for' => 'employee',
                'employee_can_see' => true,
                'type' => 'offboard',
                'column_priority' => 2,
                'company_id' => $company->id,
            ],
            [
                'title' => 'Provide Experience Letter',
                'task_for' => 'company',
                'employee_can_see' => true,
                'type' => 'offboard',
                'column_priority' => 3,
                'company_id' => $company->id,
            ]
        ]);
    }

}
