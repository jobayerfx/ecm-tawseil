<?php

namespace Modules\Onboarding\Listeners;

use App\Events\NewUserEvent;
use App\Models\EmployeeDetails;
use Modules\Onboarding\Entities\OnboardingCompletedTask;
use Modules\Onboarding\Entities\OnboardingTask;
use Modules\Recruit\Events\OnboardingStartEvent;

class OnboardingStartListener
{
    /**
     * Create the event listener.
     */

    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OnboardingStartEvent $event)
    {
        $userId = $event->user->id;
        $type = $event->type;

        $onboardTasks = OnboardingTask::where('type', 'onboard')->get();

        $allTasks = [];

        foreach ($onboardTasks as $task) {
            $allTasks[] = [
                'onboarding_task_id' => $task->id,
                'type' => 'onboard',
                'employee_id' => $userId,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        OnboardingCompletedTask::insert($allTasks);
    }

}
