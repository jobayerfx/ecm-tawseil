<?php

namespace Modules\Onboarding\Views\Components;

use Illuminate\View\Component;
use App\Models\EmployeeDetails;
use Modules\Onboarding\Entities\OnboardingTask;

class EmployeeOffboarding extends Component
{

    public $type;
    public $onboard;
    public $offboardingTasks;
    public $totalOffboardTasks;
    public $offBoardCompleteTasks;
    public $manageOffboardingPermission;

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
        $this->manageOffboardingPermission = user()->permission('manage_employee_offboarding');

        $this->initializeProperties();

        $this->onboard = EmployeeDetails::where('user_id', $empId)
            ->where('offboard_completed', 0)
            ->first();

        if ($this->onboard || ((user()->hasRole('admin') && $type == 'profile') || user()->hasRole('employee') && $type == 'profile')) {
            $this->fetchOffboardingTasks($empId);
        }
    }

    /**
     * Initialize component properties.
     */
    protected function initializeProperties()
    {
        $this->totalOffboardTasks = 0;
        $this->offBoardCompleteTasks = 0;
        $this->offboardingTasks = collect();
    }

    /**
     * Fetch onboarding tasks for the employee.
     *
     * @param int $empId
     */
    protected function fetchOffboardingTasks($empId)
    {
        $this->offboardingTasks = OnboardingTask::where('type', 'offboard')
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

        $this->offBoardCompleteTasks = $this->offboardingTasks->sum('completed_tasks_count');
        $this->totalOffboardTasks = $this->offboardingTasks->count();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('onboarding::components.employee-offboarding', get_object_vars($this));
    }

}
