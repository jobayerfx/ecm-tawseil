<?php

namespace Modules\Onboarding\Views\Components;

use Illuminate\View\Component;
use App\Models\EmployeeDetails;
use Modules\Onboarding\Entities\OnboardingCompletedTask;

class StartOnboarding extends Component
{

    public $employee;
    public $onboard;
    public $onBoardCompleteTasks;
    public $offBoardCompleteTasks;
    public $totalOnboardTasks;
    public $totalOffboardTasks;
    public $viewonboardingPermission;
    public $viewoffboardingPermission;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($employee)
    {
        $this->viewonboardingPermission = user()->permission('manage_employee_onboarding');
        $this->viewoffboardingPermission = user()->permission('manage_employee_offboarding');

        $this->employee = $employee;
        $this->onBoardCompleteTasks = [];
        $this->offBoardCompleteTasks = [];

        $this->onboard = EmployeeDetails::where('user_id', $employee->id)
            ->where(function ($query) {
                $query->where('onboard_completed', 0)
                    ->orWhere('offboard_completed', 0);
            })->first();

        if ($this->onboard) {
            $boardingTasks = OnboardingCompletedTask::whereHas('onboardingTask')->where('employee_id', $employee->id)->with('onboardingTask')->get();

            $onboardTasks = $boardingTasks->filter(function ($task) {
                return $task->onboardingTask->type == 'onboard';
            });

            $this->totalOnboardTasks = $onboardTasks->count();

            $this->onBoardCompleteTasks = $onboardTasks->filter(function ($task) {
                return $task->status == 'completed';
            })->count();

            $offboardTasks = $boardingTasks->filter(function ($task) {
                return $task->onboardingTask->type == 'offboard';
            });

            $this->totalOffboardTasks = $offboardTasks->count();

            $this->offBoardCompleteTasks = $offboardTasks->filter(function ($task) {
                return $task->status == 'completed';
            })->count();
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('onboarding::components.start-onboarding', get_object_vars($this));
    }

}
