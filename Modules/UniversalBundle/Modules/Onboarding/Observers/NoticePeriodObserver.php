<?php

namespace Modules\Onboarding\Observers;

use App\Models\EmployeeDetails;
use Modules\Onboarding\Entities\OnboardingCompletedTask;
use Modules\Onboarding\Entities\OnboardingTask;
use Modules\Onboarding\Events\NoticePeriodEvent;

class NoticePeriodObserver
{

    public function updating(EmployeeDetails $model)
    {
        if(module_enabled('Onboarding')) {
            if (!empty($model->notice_period_start_date) && $model->isDirty('notice_period_start_date')) {
                // Set offboard_completed to 0 when notice period starts
                $model->offboard_completed = 0;
                $model->onboard_completed = 1;

                event(new NoticePeriodEvent($model->user, session('auth_pass'), $model->notice_period_start_date));
            }
            else {
                // Clearing notice period, update offboard_completed to 1
                // $model->offboard_completed = 1;
            }
            
        }
    }

    public function creating(EmployeeDetails $model)
    {
        if (module_enabled('Onboarding')) {

            if (!isRunningInConsoleOrSeeding()) {
                if ($model->user->created_at->lt(now()->subDays(30))) {
                    $model->onboarding_status = 'old';
                }
                else {
                    $model->onboarding_status = 'new';
                    $model->onboard_completed = 0;

                }
            } else {
                $model->onboarding_status = 'old';
            }
        }
    }

    public function saving(EmployeeDetails $model)
    {
        if (module_enabled('Onboarding')) {
            // Fetch all onboarding tasks count
            $onboardTasksCount = OnboardingTask::where('type', 'onboard')->count();
            
            // Fetch completed onboard tasks count for the user
            $completedOnboardTasksCount = OnboardingCompletedTask::where('user_id', $model->user_id)
                ->whereIn('onboarding_task_id', function ($query) {
                    $query->select('id')->from('onboarding_tasks')->where('type', 'onboard');
                })
                ->where('status', 'completed')
                ->count();
            
            // Check if all onboarding tasks are completed
            if ($completedOnboardTasksCount == $onboardTasksCount) {
                // Set onboard_completed to 1 when all onboarding tasks are completed
                $model->onboard_completed = 1;
            }
        }
    }

}
