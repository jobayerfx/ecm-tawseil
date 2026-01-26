<?php

namespace Modules\Onboarding\Views\Components;

use Illuminate\View\Component;
use App\Models\EmployeeDetails;
use Modules\Onboarding\Entities\OnboardingTask;

class EmployeeOnboarding extends Component
{

    public $type;
    public $onboard;
    public $onboardingTasks;
    public $totalOnboardTasks;
    public $onBoardCompleteTasks;
    public $manageOnboardingPermission;

    /**
     * Create a new component instance.
     *
     * @param $employee
     * @param $type
     * @return void
     */
    public function __construct($employee, $type = null)
    {
        $this->type = $type;
        $empId = $employee->id;
        $this->employee = $employee;
        $this->manageOnboardingPermission = user()->permission('manage_employee_onboarding');

        $this->initializeProperties();

        $this->onboard = EmployeeDetails::where('user_id', $empId)
            ->where('onboard_completed', 0)
            ->first();

        if ($this->onboard || ((user()->hasRole('admin') && $type == 'profile') || user()->hasRole('employee') && $type == 'profile')) {
            $this->fetchOnboardingTasks($empId);
        }
    }

    /**
     * Initialize component properties.
     */
    protected function initializeProperties()
    {
        $this->totalOnboardTasks = 0;
        $this->onBoardCompleteTasks = 0;
        $this->onboardingTasks = collect();
    }

    /**
     * Fetch onboarding tasks for the employee.
     *
     * @param int $empId
     */
    protected function fetchOnboardingTasks($empId)
    {
        $this->onboardingTasks = OnboardingTask::where('type', 'onboard')
            ->whereHas('completedTask', function ($query) use ($empId) {
                $query->where('employee_id', $empId);
            })
            ->with(['completedTask' => function ($query) use ($empId) {
                $query->where('employee_id', $empId);
            }])
            ->withCount(['completedTask as completed_tasks_count' => function ($query) use ($empId) {
                $query->where('employee_id', $empId)->where('status', 'completed');
            }])
            ->orderBy('column_priority', 'asc')->get();

        $this->onBoardCompleteTasks = $this->onboardingTasks->sum('completed_tasks_count');
        $this->totalOnboardTasks = $this->onboardingTasks->count();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('onboarding::components.employee-onboarding', get_object_vars($this));
    }

}
