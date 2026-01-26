<?php

namespace Modules\Onboarding\Services;

use App\Models\EmployeeDetails;
use App\Models\User;
use Modules\Onboarding\Entities\OnboardingCompletedTask;
use Modules\Onboarding\Entities\OnboardingTask;
use Carbon\Carbon;

class OnboardingService
{
    /**
     * Get onboarding tasks for an employee
     */
    public function getOnboardingTasks($employeeId, $type = 'onboard')
    {
        return OnboardingTask::where('type', $type)
            ->orderBy('column_priority')
            ->get()
            ->map(function ($task) use ($employeeId) {
                $task->completedTask = OnboardingCompletedTask::where('onboarding_task_id', $task->id)
                    ->where('employee_id', $employeeId)
                    ->where('type', $task->type)
                    ->first();
                return $task;
            });
    }

    /**
     * Get onboarding progress for an employee
     */
    public function getOnboardingProgress($employeeId, $type = 'onboard')
    {
        $totalTasks = OnboardingTask::where('type', $type)->count();
        
        if ($totalTasks === 0) {
            return 0;
        }

        $completedTasks = OnboardingCompletedTask::where('employee_id', $employeeId)
            ->where('type', $type)
            ->where('status', 'completed')
            ->count();

        return round(($completedTasks / $totalTasks) * 100);
    }

    /**
     * Check if employee can start onboarding
     */
    public function canStartOnboarding($employeeId)
    {
        $employeeDetail = EmployeeDetails::where('user_id', $employeeId)->first();
        
        if (!$employeeDetail) {
            return false;
        }

        return $employeeDetail->onboarding_status === 'old' && 
               $employeeDetail->onboard_completed === 0 && 
               $employeeDetail->offboard_completed === 0;
    }

    /**
     * Check if employee can start offboarding
     */
    public function canStartOffboarding($employeeId)
    {
        $employeeDetail = EmployeeDetails::where('user_id', $employeeId)->first();
        
        if (!$employeeDetail) {
            return false;
        }

        return $employeeDetail->onboard_completed === 1 && 
               $employeeDetail->offboard_completed === 0 && 
               $employeeDetail->onboarding_status !== 'offboarding';
    }

    /**
     * Start onboarding for an employee
     */
    public function startOnboarding($employeeId)
    {
        $employeeDetail = EmployeeDetails::where('user_id', $employeeId)->first();
        
        if (!$employeeDetail) {
            return ['success' => false, 'message' => 'Employee not found'];
        }

        if (!$this->canStartOnboarding($employeeId)) {
            return ['success' => false, 'message' => 'Cannot start onboarding at this time'];
        }

        $onboardTasks = OnboardingTask::where('type', 'onboard')->get();

        if ($onboardTasks->isEmpty()) {
            return ['success' => false, 'message' => 'No onboarding tasks configured'];
        }

        foreach ($onboardTasks as $task) {
            OnboardingCompletedTask::updateOrCreate(
                [
                    'onboarding_task_id' => $task->id,
                    'employee_id' => $employeeId,
                    'type' => $task->type
                ],
                [
                    'status' => 'pending'
                ]
            );
        }

        $employeeDetail->update([
            'onboarding_status' => 'new',
            'onboard_completed' => 0
        ]);

        return ['success' => true, 'message' => 'Onboarding started successfully'];
    }

    /**
     * Start offboarding for an employee
     */
    public function startOffboarding($employeeId)
    {
        $employeeDetail = EmployeeDetails::where('user_id', $employeeId)->first();
        
        if (!$employeeDetail) {
            return ['success' => false, 'message' => 'Employee not found'];
        }

        if (!$this->canStartOffboarding($employeeId)) {
            return ['success' => false, 'message' => 'Cannot start offboarding at this time'];
        }

        $offboardTasks = OnboardingTask::where('type', 'offboard')->get();

        if ($offboardTasks->isEmpty()) {
            return ['success' => false, 'message' => 'No offboarding tasks configured'];
        }

        foreach ($offboardTasks as $task) {
            OnboardingCompletedTask::updateOrCreate(
                [
                    'onboarding_task_id' => $task->id,
                    'employee_id' => $employeeId,
                    'type' => $task->type
                ],
                [
                    'status' => 'pending'
                ]
            );
        }

        $employeeDetail->update([
            'onboarding_status' => 'offboarding',
            'offboard_completed' => 0
        ]);

        return ['success' => true, 'message' => 'Offboarding started successfully'];
    }

    /**
     * Complete a specific task
     */
    public function completeTask($taskId, $employeeId, $completedOn, $file = null, $userId = null)
    {
        $task = OnboardingTask::find($taskId);
        
        if (!$task) {
            return ['success' => false, 'message' => 'Task not found'];
        }

        $completedTask = OnboardingCompletedTask::where('onboarding_task_id', $taskId)
            ->where('employee_id', $employeeId)
            ->where('type', $task->type)
            ->first();

        if (!$completedTask) {
            $completedTask = new OnboardingCompletedTask();
            $completedTask->onboarding_task_id = $taskId;
            $completedTask->employee_id = $employeeId;
            $completedTask->type = $task->type;
        }

        $completedTask->status = 'completed';
        $completedTask->completed_on = $completedOn;
        $completedTask->user_id = $userId;
        
        if ($file) {
            $completedTask->file = $file;
        }

        $completedTask->save();

        $this->updateEmployeeStatus($employeeId);

        return ['success' => true, 'message' => 'Task completed successfully'];
    }

    /**
     * Complete all tasks of a specific type
     */
    public function completeAllTasks($employeeId, $type, $userId = null)
    {
        $tasks = OnboardingCompletedTask::where('employee_id', $employeeId)
            ->where('type', $type)
            ->where('status', 'pending');

        if ($tasks->count() === 0) {
            return ['success' => false, 'message' => 'No pending tasks found'];
        }

        $tasks->update([
            'status' => 'completed',
            'completed_on' => now(),
            'user_id' => $userId
        ]);

        $this->updateEmployeeStatus($employeeId);

        return ['success' => true, 'message' => 'All tasks completed successfully'];
    }

    /**
     * Cancel onboarding/offboarding for an employee
     */
    public function cancelOnboarding($employeeId, $type)
    {
        OnboardingCompletedTask::where('employee_id', $employeeId)
            ->where('type', $type)
            ->delete();

        $employeeDetail = EmployeeDetails::where('user_id', $employeeId)->first();
        
        if ($employeeDetail) {
            if ($type === 'onboard') {
                $employeeDetail->update([
                    'onboard_completed' => 0,
                    'offboard_completed' => 0,
                    'onboarding_status' => 'old'
                ]);
            } else {
                $employeeDetail->update([
                    'offboard_completed' => 0,
                    'onboarding_status' => 'old'
                ]);
            }
        }

        return ['success' => true, 'message' => 'Onboarding cancelled successfully'];
    }

    /**
     * Update employee onboarding status
     */
    private function updateEmployeeStatus($employeeId)
    {
        $employeeDetail = EmployeeDetails::where('user_id', $employeeId)->first();
        
        if (!$employeeDetail) {
            return;
        }

        $onboardTasks = OnboardingTask::where('type', 'onboard')->count();
        $completedOnboardTasks = OnboardingCompletedTask::where('employee_id', $employeeId)
            ->where('type', 'onboard')
            ->where('status', 'completed')
            ->count();

        $onboardCompleted = ($onboardTasks > 0 && $completedOnboardTasks == $onboardTasks) ? 1 : 0;

        $offboardTasks = OnboardingTask::where('type', 'offboard')->count();
        $completedOffboardTasks = OnboardingCompletedTask::where('employee_id', $employeeId)
            ->where('type', 'offboard')
            ->where('status', 'completed')
            ->count();

        $offboardCompleted = ($offboardTasks > 0 && $completedOffboardTasks == $offboardTasks) ? 1 : 0;

        $onboardingStatus = $this->determineOnboardingStatus($onboardTasks, $completedOnboardTasks, $offboardTasks, $completedOffboardTasks);

        $employeeDetail->update([
            'onboard_completed' => $onboardCompleted,
            'offboard_completed' => $offboardCompleted,
            'onboarding_status' => $onboardingStatus
        ]);
    }

    /**
     * Determine employee onboarding status
     */
    private function determineOnboardingStatus($totalOnboardTasks, $completedOnboardTasks, $totalOffboardTasks, $completedOffboardTasks)
    {
        if ($totalOnboardTasks > 0 && $completedOnboardTasks < $totalOnboardTasks) {
            return 'new';
        }

        if ($totalOnboardTasks > 0 && $completedOnboardTasks == $totalOnboardTasks) {
            if ($totalOffboardTasks == 0 || $completedOffboardTasks == $totalOffboardTasks) {
                return 'old';
            }
            return 'offboarding';
        }

        return 'old';
    }

    /**
     * Compute button visibility flags for start onboarding/offboarding (condensed).
     */
    public function getStartButtonsVisibility(int $employeeId, string $onboardingPermission, string $offboardingPermission): array
    {
        $m = $this->getVisibilityMetrics($employeeId);

        // Guard states first
        if ($m['offboardingCompleted'] || ($m['onboardingCompleted'] && $m['offboardingCompleted'])) {
            return $this->applyPermissionClamp(false, false, $onboardingPermission, $offboardingPermission);
        }
        if ($m['inProgress']) {
            return $this->applyPermissionClamp(false, false, $onboardingPermission, $offboardingPermission);
        }

        // Decide flags
        $showOnboarding = false;
        $showOffboarding = false;

        if ($m['onboardingCompleted'] && !$m['offboardingStarted'] && !$m['offboardingCompleted'] && $m['hasOffboardTasks']) {
            $showOffboarding = true;
        } elseif (!$m['onboardingStarted'] && !$m['offboardingStarted'] && !$m['onboardingCompleted'] && !$m['offboardingCompleted']) {
            $showOnboarding = $m['hasOnboardTasks'];
            $showOffboarding = $m['hasOffboardTasks'];
        }

        return $this->applyPermissionClamp($showOnboarding, $showOffboarding, $onboardingPermission, $offboardingPermission);
    }

    private function getVisibilityMetrics(int $employeeId): array
    {
        $totalOnboard = OnboardingTask::where('type', 'onboard')->count();
        $totalOffboard = OnboardingTask::where('type', 'offboard')->count();

        $completedOnboard = OnboardingCompletedTask::where('employee_id', $employeeId)
            ->where('type', 'onboard')
            ->where('status', 'completed')
            ->count();

        $completedOffboard = OnboardingCompletedTask::where('employee_id', $employeeId)
            ->where('type', 'offboard')
            ->where('status', 'completed')
            ->count();

        $onboardingStarted = OnboardingCompletedTask::where('employee_id', $employeeId)
            ->where('type', 'onboard')
            ->exists();

        $offboardingStarted = OnboardingCompletedTask::where('employee_id', $employeeId)
            ->where('type', 'offboard')
            ->exists();

        $onboardingCompleted = $totalOnboard > 0 && ($completedOnboard === $totalOnboard);
        $offboardingCompleted = $totalOffboard > 0 && ($completedOffboard === $totalOffboard);

        return [
            'hasOnboardTasks' => $totalOnboard > 0,
            'hasOffboardTasks' => $totalOffboard > 0,
            'onboardingStarted' => $onboardingStarted,
            'offboardingStarted' => $offboardingStarted,
            'onboardingCompleted' => $onboardingCompleted,
            'offboardingCompleted' => $offboardingCompleted,
            'inProgress' => ($onboardingStarted && !$onboardingCompleted) || ($offboardingStarted && !$offboardingCompleted),
        ];
    }

    private function applyPermissionClamp(bool $showOnboarding, bool $showOffboarding, string $onboardingPermission, string $offboardingPermission): array
    {
        if ($onboardingPermission !== 'all') {
            $showOnboarding = false;
        }
        if ($offboardingPermission !== 'all') {
            $showOffboarding = false;
        }

        return [
            'show_onboarding' => $showOnboarding,
            'show_offboarding' => $showOffboarding,
        ];
    }

    /**
     * Get employees by onboarding status
     */
    public function getEmployeesByStatus($status, $companyId = null)
    {
        $query = EmployeeDetails::with('user')
            ->where('onboarding_status', $status);

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query->get();
    }

    /**
     * Get onboarding statistics
     */
    public function getOnboardingStats($companyId = null)
    {
        $query = EmployeeDetails::query();

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $totalEmployees = $query->count();
        $newEmployees = $query->where('onboarding_status', 'new')->count();
        $oldEmployees = $query->where('onboarding_status', 'old')->count();
        $offboardingEmployees = $query->where('onboarding_status', 'offboarding')->count();

        return [
            'total' => $totalEmployees,
            'new' => $newEmployees,
            'old' => $oldEmployees,
            'offboarding' => $offboardingEmployees
        ];
    }
}
