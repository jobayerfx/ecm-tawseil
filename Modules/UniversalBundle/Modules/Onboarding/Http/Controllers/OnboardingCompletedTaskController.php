<?php

namespace Modules\Onboarding\Http\Controllers;

use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\EmployeeDetails;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Modules\Onboarding\Entities\OnboardingCompletedTask;
use Modules\Onboarding\Notifications\TaskApprovalNotification;
use Modules\Onboarding\Notifications\TaskSubmissionNotification;
use Modules\Onboarding\Entities\OnboardingTask;
use Modules\Onboarding\Http\Requests\CreateOnboardingDashboardRequest;
use Modules\Onboarding\Notifications\OffboardingNotification;
use Modules\Onboarding\Notifications\OnboardingNotification;
use App\Scopes\ActiveScope;

class OnboardingCompletedTaskController extends AccountBaseController
{
    /**
     * Display a listing of the resource.
     */

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'onboarding::clan.onboarding';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('onboarding', $this->user->modules));
            return $next($request);
        });
    }

    public function index()
    {
        $this->tasks = OnboardingTask::all();

        return view('dashboard.employee.widgets.onboarding_tasks', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->tasks = OnboardingTask::all();
        $taskId = $request->input('task_id');
        $taskName = OnboardingTask::findOrFail($taskId)->title; // Fetch task name

        return view('onboarding::onboarding-dashboard.ajax.create-modal', ['empId' => $request->empId, 'taskId' => $taskId, 'taskName' => $taskName]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateOnboardingDashboardRequest $request)
    {
        $completedOn = Carbon::parse($request->completed_on);
        $user = User::withoutGlobalScope(ActiveScope::class)->findOrFail($request->empId);
        $onboardingTask = OnboardingTask::findOrFail($request->onboarding_task_id);

        // Check if the task is already completed by the user
        $task = OnboardingCompletedTask::where('employee_id', $user->id)
            ->where('onboarding_task_id', $onboardingTask->id)
            ->where('type', $onboardingTask->type)
            ->first();

        if ($task) {
            // Update existing entry
            $task->user_id = user()->id;
            $task->type = $onboardingTask->type; // Ensure type is set correctly

            // If task was rejected, reset rejection status
            if ($task->submission_status === 'rejected') {
                $task->submission_status = 'pending';
                $task->rejection_reason = null;
                $task->rejected_by = null;
                $task->rejected_on = null;
            }

            if ($request->hasFile('file')) {
                // Handle file upload
                Files::deleteFile($task->file, 'onboarding-files');
                $task->file = Files::uploadLocalOrS3($request->file, 'onboarding-files');
            }

            $task->save();
        }
        else {
            // Create a new instance of OnboardingCompletedTask
            $task = new OnboardingCompletedTask();
            $task->onboarding_task_id = $request->onboarding_task_id;
            $task->employee_id = $user->id;
            $task->user_id = user()->id;
            $task->type = $onboardingTask->type;
            $task->status = 'pending';
            $task->submission_status = 'pending';

            if ($request->hasFile('file')) {
                $task->file = Files::uploadLocalOrS3($request->file, 'onboarding-files');
            }

            $task->save();
        }

        // If employee is submitting, change status to submitted
        if ($task->employee_id == user()->id) {
            $task->update([
                'submission_status' => 'submitted',
                'submitted_on' => now(),
                'completed_on' => $completedOn
            ]);
            
            // Send notification to admins and users with manage permission
            $this->notifyTaskSubmission($task);
        }
        // If admin/permission user is completing a company task, mark it as completed instantly
        elseif (in_array('admin', user_roles()) || $this->hasManagePermission($onboardingTask->type)) {
            $task->update([
                'status' => 'completed',
                'submission_status' => 'approved',
                'approved_by' => user()->id,
                'approved_on' => now(),
                'completed_on' => $completedOn
            ]);
        }

        // Update employee onboarding/offboarding status
        $this->updateEmployeeOnboardingStatus($user);

        session()->forget('user');

        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * Update employee onboarding and offboarding completion status
     */
    private function updateEmployeeOnboardingStatus(User $user)
    {
        $employeeDetail = $user->employeeDetail;
        
        if (!$employeeDetail) {
            return;
        }

        // Check onboarding completion
        $onboardTasks = OnboardingCompletedTask::where('employee_id', $user->id)
            ->where('type', 'onboard');

        $totalOnboardTasks = OnboardingTask::where('type', 'onboard')->count();
        $completedOnboardTasks = $onboardTasks->where('status', 'completed')->count();

        $onboardCompleted = ($totalOnboardTasks > 0 && $completedOnboardTasks == $totalOnboardTasks) ? 1 : 0;

        // Check offboarding completion
        $offboardTasks = OnboardingCompletedTask::where('employee_id', $user->id)
            ->where('type', 'offboard');

        $totalOffboardTasks = OnboardingTask::where('type', 'offboard')->count();
        $completedOffboardTasks = $offboardTasks->where('status', 'completed')->count();

        $offboardCompleted = ($totalOffboardTasks > 0 && $completedOffboardTasks == $totalOffboardTasks) ? 1 : 0;

        // Update onboarding status (old/new employee logic)
        $this->determineEmployeeOnboardingStatus($employeeDetail, $totalOnboardTasks, $completedOnboardTasks, $totalOffboardTasks, $completedOffboardTasks);

        $employeeDetail->update([
            'onboard_completed' => $onboardCompleted,
            'offboard_completed' => $offboardCompleted
        ]);
    }

    /**
     * Determine if employee is old or new based on onboarding status
     */
    private function determineEmployeeOnboardingStatus($employeeDetail, $totalOnboardTasks, $completedOnboardTasks, $totalOffboardTasks, $completedOffboardTasks)
    {
        // New employee logic: Has pending onboarding tasks
        if ($totalOnboardTasks > 0 && $completedOnboardTasks < $totalOnboardTasks) {
            $employeeDetail->onboarding_status = 'new';
            return;
        }

        // Old employee logic: All onboarding completed and no pending offboarding
        if ($totalOnboardTasks > 0 && $completedOnboardTasks == $totalOnboardTasks) {
            if ($totalOffboardTasks == 0 || $completedOffboardTasks == $totalOffboardTasks) {
                $employeeDetail->onboarding_status = 'old';
            } else {
                $employeeDetail->onboarding_status = 'offboarding';
            }
            return;
        }

        // Default to old if no onboarding tasks exist
        $employeeDetail->onboarding_status = 'old';
    }

    public function startOnboarding(Request $request)
    {
        $userId = $request->id;
        $employeeDetail = EmployeeDetails::where('user_id', $userId)->first();

        if (!$employeeDetail) {
            return Reply::error(__('onboarding::messages.employeeNotFound'));
        }

        // Check if onboarding is already in progress
        if ($employeeDetail->onboard_completed == 0 && $this->hasPendingOnboardingTasks($userId)) {
            return Reply::error(__('onboarding::messages.onboardingAlreadyInProgress'));
        }

        $onboardTasks = OnboardingTask::where('type', 'onboard')->get();

        if ($onboardTasks->isEmpty()) {
            return Reply::error(__('onboarding::messages.noOnboardingTasksConfigured'));
        }

        foreach ($onboardTasks as $task) {
            $completedTask = OnboardingCompletedTask::where('onboarding_task_id', $task->id)
                ->where('type', 'onboard')
                ->where('employee_id', $userId)
                ->firstOrNew();
                
            $completedTask->onboarding_task_id = $task->id;
            $completedTask->type = $task->type;
            $completedTask->employee_id = $userId;
            $completedTask->status = 'pending';
            $completedTask->save();
        }

        // Update employee status
        $employeeDetail->update([
            'onboarding_status' => 'new',
            'onboard_completed' => 0
        ]);

        // Onboarding start Notification to the user
        Notification::send($employeeDetail->user, new OnboardingNotification($employeeDetail->user));

        return Reply::success(__('onboarding::messages.onboardingStarted'));
    }

    public function startOffboarding(Request $request)
    {
        $employeeId = $request->employee_id;
        $employeeDetail = EmployeeDetails::where('user_id', $employeeId)->first();

        if (!$employeeDetail) {
            return Reply::error(__('onboarding::messages.employeeNotFound'));
        }

        // Check if onboarding is completed before starting offboarding
        if ($employeeDetail->onboard_completed == 0 && $employeeDetail->onboarding_status == 'new') {
            return Reply::error(__('onboarding::messages.completeOnboardingFirst'));
        }

        // Check if offboarding is already in progress
        if ($this->hasPendingOffboardingTasks($employeeId)) {
            return Reply::error(__('onboarding::messages.offboardingAlreadyInProgress'));
        }

        $offboardTasks = OnboardingTask::where('type', 'offboard')->get();

        if ($offboardTasks->isEmpty()) {
            return Reply::error(__('onboarding::messages.noOffboardingTasksConfigured'));
        }

        foreach ($offboardTasks as $task) {
            $completedTask = OnboardingCompletedTask::where('onboarding_task_id', $task->id)
                ->where('type', 'offboard')
                ->where('employee_id', $employeeId)
                ->firstOrNew();
                
            $completedTask->onboarding_task_id = $task->id;
            $completedTask->type = $task->type;
            $completedTask->employee_id = $employeeId;
            $completedTask->status = 'pending';
            $completedTask->save();
        }

        // Update employee status
        $employeeDetail->update([
            'onboarding_status' => 'offboarding',
            'offboard_completed' => 0
        ]);

        Notification::send($employeeDetail->user, new OffboardingNotification($employeeDetail->user));

        return Reply::success(__('onboarding::messages.offboardingStarted'));
    }

    /**
     * Check if employee has pending onboarding tasks
     */
    private function hasPendingOnboardingTasks($userId)
    {
        return OnboardingCompletedTask::where('employee_id', $userId)
            ->where('type', 'onboard')
            ->where('status', 'pending')
            ->exists();
    }

    /**
     * Check if employee has pending offboarding tasks
     */
    private function hasPendingOffboardingTasks($userId)
    {
        return OnboardingCompletedTask::where('employee_id', $userId)
            ->where('type', 'offboard')
            ->where('status', 'pending')
            ->exists();
    }

    public function viewFile($file)
    {
        $filePath = public_path('user-uploads/onboarding-files/' . $file);

        // Check if the file exists
        if (file_exists($filePath)) {
            // Get the file's MIME type
            $mimeType = mime_content_type($filePath);

            // Return the file with appropriate MIME type
            return response()->file($filePath, ['Content-Type' => $mimeType]);
        } else {
            // Handle file not found
            abort(404);
        }
    }

    public function cancelRequest(Request $request)
    {
        $employeeId = $request->empId;
        $type = $request->type;
        
        // Get all tasks with files before deleting
        $tasksToDelete = OnboardingCompletedTask::where('type', $type)
            ->where('employee_id', $employeeId)
            ->get();

        // Delete all uploaded files first
        foreach ($tasksToDelete as $task) {
            if ($task->file) {
                Files::deleteFile($task->file, 'onboarding-files');
            }
        }

        // Delete all tasks of the specified type for this employee
        OnboardingCompletedTask::where('type', $type)
            ->where('employee_id', $employeeId)
            ->delete();

        $employeeDetail = EmployeeDetails::where('user_id', $employeeId)->first();
        
        if (!$employeeDetail) {
            return Reply::error(__('onboarding::messages.employeeNotFound'));
        }

        if ($type == 'onboard') {
            // Reset onboarding status
            $employeeDetail->onboard_completed = 0;
            $employeeDetail->onboarding_status = 'old';
            
            // If offboarding was completed, also reset it since onboarding is being restarted
            if ($employeeDetail->offboard_completed == 1) {
                $employeeDetail->offboard_completed = 0;
            }
        } else {
            // Reset offboarding status
            $employeeDetail->offboard_completed = 0;
            $employeeDetail->onboarding_status = 'old';
        }

        $employeeDetail->save();

        return Reply::success(__('onboarding::messages.requestCanceled'));
    }

    public function completeAllRequest(Request $request)
    {
        $employeeId = $request->empId;
        $type = $request->type;
        
        // Build query for tasks to complete
        $taskQuery = OnboardingCompletedTask::where('type', $type)
            ->where('employee_id', $employeeId);

        // Apply role-based filtering
        if (in_array('admin', user_roles())) {
            // Admins can complete all tasks (company + employee)
            // No additional filtering needed
        } else {
            // Employees can only complete employee tasks
            // Get only the IDs of employee tasks
            $employeeTaskIds = OnboardingCompletedTask::where('type', $type)
                ->where('employee_id', $employeeId)
                ->whereHas('onboardingTask', function($query) {
                    $query->where('task_for', 'employee');
                })
                ->pluck('id');
            
            // Filter the query to only include employee tasks
            $taskQuery->whereIn('id', $employeeTaskIds);
        }
        
        // Complete the filtered tasks
        $taskQuery->update([
            'status' => 'completed', 
            'completed_on' => now(), 
            'user_id' => user()->id
        ]);

        // Check if ALL tasks of this type are now completed
        $totalTasks = OnboardingCompletedTask::where('type', $type)
            ->where('employee_id', $employeeId)
            ->count();
            
        $completedTasks = OnboardingCompletedTask::where('type', $type)
            ->where('employee_id', $employeeId)
            ->where('status', 'completed')
            ->count();

        // Update employee status only if ALL tasks are completed
        $employeeDetail = EmployeeDetails::where('user_id', $employeeId)->first();
        
        if ($employeeDetail && $totalTasks > 0 && $completedTasks == $totalTasks) {
            if ($type == 'onboard') {
                $employeeDetail->onboard_completed = 1;
            } else {
                $employeeDetail->offboard_completed = 1;
            }
            
            // Get task counts for proper status determination
            $onboardTasks = OnboardingCompletedTask::where('type', 'onboard')
                ->where('employee_id', $employeeId);
            $totalOnboardTasks = OnboardingTask::where('type', 'onboard')->count();
            $completedOnboardTasks = $onboardTasks->where('status', 'completed')->count();

            $offboardTasks = OnboardingCompletedTask::where('type', 'offboard')
                ->where('employee_id', $employeeId);
            $totalOffboardTasks = OnboardingTask::where('type', 'offboard')->count();
            $completedOffboardTasks = $offboardTasks->where('status', 'completed')->count();
            
            // Determine the correct onboarding status based on actual task completion
            $this->determineEmployeeOnboardingStatus($employeeDetail, $totalOnboardTasks, $completedOnboardTasks, $totalOffboardTasks, $completedOffboardTasks);
            $employeeDetail->save();
        }

        return Reply::success(__('messages.updateSuccess'));
    }

    public function completeAllOffboardingRequest(Request $request)
    {
        $employeeId = $request->empId;
        $type = $request->type ?? 'offboard'; // Default to 'offboard' if not provided

        // Build query for tasks to complete
        $taskQuery = OnboardingCompletedTask::where('type', $type)
            ->where('employee_id', $employeeId);

        // Apply role-based filtering
        if (in_array('admin', user_roles())) {
        $employeeTaskIds = OnboardingCompletedTask::where('type', $type)
                ->where('employee_id', $employeeId);
        } else {
            // Employees can only complete employee tasks
            // Get only the IDs of employee tasks
            $employeeTaskIds = OnboardingCompletedTask::where('type', $type)
                ->where('employee_id', $employeeId)
                ->whereHas('onboardingTask', function($query) {
                    $query->where('task_for', 'employee');
                })
                ->pluck('id');
            
            // Filter the query to only include employee tasks
            $taskQuery->whereIn('id', $employeeTaskIds);
        }

        // Complete the filtered tasks
        $taskQuery->update([
            'status' => 'completed', 
            'completed_on' => now(), 
            'user_id' => user()->id
        ]);

        // Check if ALL tasks of this type are now completed
        $totalTasks = OnboardingCompletedTask::where('type', $type)
            ->where('employee_id', $employeeId)
            ->count();
            
        $completedTasks = OnboardingCompletedTask::where('type', $type)
            ->where('employee_id', $employeeId)
            ->where('status', 'completed')
            ->count();

        // Update employee status only if ALL tasks are completed
        $employeeDetail = EmployeeDetails::where('user_id', $employeeId)->first();
        
        if ($employeeDetail && $totalTasks > 0 && $completedTasks == $totalTasks) {
            $employeeDetail->offboard_completed = 1;
            
            // Get task counts for proper status determination
            $onboardTasks = OnboardingCompletedTask::where('type', 'onboard')
                ->where('employee_id', $employeeId);
            $totalOnboardTasks = OnboardingTask::where('type', 'onboard')->count();
            $completedOnboardTasks = $onboardTasks->where('status', 'completed')->count();

            $offboardTasks = OnboardingCompletedTask::where('type', 'offboard')
                ->where('employee_id', $employeeId);
            $totalOffboardTasks = OnboardingTask::where('type', 'offboard')->count();
            $completedOffboardTasks = $offboardTasks->where('status', 'completed')->count();
            
            // Determine the correct onboarding status based on actual task completion
            $this->determineEmployeeOnboardingStatus($employeeDetail, $totalOnboardTasks, $completedOnboardTasks, $totalOffboardTasks, $completedOffboardTasks);
            $employeeDetail->save();
        }

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Submit task for approval
     */
    public function submitTask(Request $request)
    {
        $taskId = $request->task_id;
        $task = OnboardingCompletedTask::findOrFail($taskId);

        // Check if user has permission to submit this task
        if ($task->employee_id != user()->id && !in_array('admin', user_roles())) {
            return Reply::error(__('messages.permissionDenied'));
        }

        // Update task to submitted status
        $task->update([
            'submission_status' => 'submitted',
            'submitted_on' => now()
        ]);

        // Send notification to admins and users with manage permission
        $this->notifyTaskSubmission($task);

        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * Approve a submitted task
     */
    public function approveTask(Request $request)
    {
        $taskId = $request->task_id;
        $task = OnboardingCompletedTask::findOrFail($taskId);

        // Check if user has permission to approve
        $permission = $task->type == 'onboard' ? 'manage_employee_onboarding' : 'manage_employee_offboarding';
        if (user()->permission($permission) !== 'all' && !in_array('admin', user_roles())) {
            return Reply::error(__('messages.permissionDenied'));
        }

        // Update task to approved status
        $task->update([
            'submission_status' => 'approved',
            'status' => 'completed',
            'approved_by' => user()->id,
            'approved_on' => now(),
        ]);

        // Update employee onboarding status
        $this->updateEmployeeOnboardingStatus($task->employee);

        // Send notification to employee
        $this->notifyTaskApproval($task, 'approved');

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Reject a submitted task
     */
    public function rejectTask(Request $request)
    {
        $taskId = $request->task_id;
        $rejectionReason = $request->rejection_reason;
        $task = OnboardingCompletedTask::findOrFail($taskId);

        // Check if user has permission to reject
        $permission = $task->type == 'onboard' ? 'manage_employee_onboarding' : 'manage_employee_offboarding';
        if (user()->permission($permission) !== 'all' && !in_array('admin', user_roles())) {
            return Reply::error(__('messages.permissionDenied'));
        }

        // Update task to rejected status and reset to pending
        $task->update([
            'submission_status' => 'rejected',
            'status' => 'pending',
            'rejected_by' => user()->id,
            'rejected_on' => now(),
            'rejection_reason' => $rejectionReason
        ]);

        // Send notification to employee
        $this->notifyTaskApproval($task, 'rejected', $rejectionReason);

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Cancel a completed task (admin only)
     */
    public function cancelTask(Request $request)
    {
        $taskId = $request->task_id;
        $task = OnboardingCompletedTask::findOrFail($taskId);

        // Check if user has permission to cancel
        $permission = $task->type == 'onboard' ? 'manage_employee_onboarding' : 'manage_employee_offboarding';
        if (user()->permission($permission) !== 'all' && !in_array('admin', user_roles())) {
            return Reply::error(__('messages.permissionDenied'));
        }

        // Delete the uploaded file if it exists
        if ($task->file) {
            Files::deleteFile($task->file, 'onboarding-files');
        }

        // Completely reset task to initial state (like canceling entire onboarding/offboarding)
        $task->update([
            'status' => 'pending',
            'submission_status' => 'pending',
            'user_id' => null,
            'file' => null,
            'completed_on' => null,
            'submitted_on' => null,
            'approved_by' => null,
            'approved_on' => null,
            'rejection_reason' => null,
            'rejected_by' => null,
            'rejected_on' => null
        ]);

        // Update employee onboarding status
        $this->updateEmployeeOnboardingStatus($task->employee);

        return Reply::success(__('onboarding::messages.requestCanceled'));
    }

    /**
     * Notify admins and users with manage permission about task submission
     */
    private function notifyTaskSubmission($task)
    {
        // Get users with manage permission
        $permission = $task->type == 'onboard' ? 'manage_employee_onboarding' : 'manage_employee_offboarding';
        
        $usersToNotify = User::whereHas('roles.permissions', function($query) use ($permission) {
            $query->where('name', $permission)->where('permission_type_id', 1);
        })->orWhereHas('roles', function($query) {
            $query->where('name', 'admin');
        })->get();

        // Send notification to all users with permission
        foreach ($usersToNotify as $user) {
            if ($user->id != $task->employee_id) { // Don't notify the employee who submitted
                $user->notify(new TaskSubmissionNotification($task, $task->employee, company()));
            }
        }
    }

    /**
     * Notify employee about task approval/rejection
     */
    private function notifyTaskApproval($task, $status, $rejectionReason = null)
    {
        $employee = $task->employee;
        $company = $employee->company;
        
        // Send notification to the employee
        $employee->notify(new TaskApprovalNotification($task, $status, user(), $rejectionReason, $company));
    }

    /**
     * Check if user has manage permission for onboarding/offboarding
     */
    private function hasManagePermission($type)
    {
        $permission = $type == 'onboard' ? 'manage_employee_onboarding' : 'manage_employee_offboarding';
        return user()->permission($permission) === 'all';
    }
}

