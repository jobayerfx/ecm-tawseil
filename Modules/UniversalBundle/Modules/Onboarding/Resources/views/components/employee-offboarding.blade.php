@if (count($offboardingTasks) > 0)

    @push('styles')
    <style>
        .complete-task {
            color: #1d82f5;
        }

        /* CSS For vertical line of circle in onbarding and offboarding */
        .ps-2 {
            padding-inline-start: 0.5rem;
        }

        .gap-x-3 {
            -moz-column-gap: 0.75rem;
            column-gap: 0.75rem;
        }

        .after\:bg-gray-200::after {
            content: '';
            --tw-bg-opacity: 1;
            background-color: rgb(229 231 235 / var(--tw-bg-opacity));
        }

        .after\:-translate-x-\[0\.5px\]::after {
            --tw-translate-x: -0.5px;
            transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
        }

        .after\:w-px::after {
            width: 1px;
        }

        .after\:top-7::after {
            top: 1.75rem;
        }

        .after\:start-3\.5::after {
            inset-inline-start: 0.875rem;
        }

        .after\:bottom-0::after {
            bottom: 0px;
        }

        .after\:absolute::after {
            position: absolute;
        }

        .flex-shrink-0 {
            flex-shrink: 0;
        }

        .size-7 {
            width: 1.75rem;
            height: 1.75rem;
        }

        .z-1 {
            z-index: 1;
        }

        .size-4 {
            width: 1rem;
            height: 1rem;
        }

        .gap-x-1\.5 {
            -moz-column-gap: 0.375rem;
            column-gap: 0.375rem;
        }

        .shift-right {
            padding-left: 20px;
        }

        /* CSS end For vertical line of circle in onbarding and offboarding */

        .onboarding-card {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .task-item {
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
            background: white;
            padding: 16px;
            border-radius: 6px;
        }
        
        .task-item:hover {
            border-left-color: var(--primary-color);
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .task-status-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        .task-status-icon.completed {
            background-color: #28a745;
            color: white;
        }

        .task-status-icon.pending {
            background-color: #ffc107;
            color: #212529;
        }

        .task-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }

        .task-meta {
            color: #666;
            font-size: 12px;
        }

        .view-file-action {
            color: #007bff;
            text-decoration: none;
            font-size: 12px;
        }

        .view-file-action:hover {
            text-decoration: underline;
        }

        /* Custom action button styles */
        .action-button {
            padding: 2px 6px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 11px;
            margin-left: 2px;
            transition: all 0.2s ease;
            border: none;
            display: inline-block;
            line-height: 1.2;
        }

        .submit-action {
            background-color: #6bc2d1;
            color: white;
            border-color: #6bc2d1;
        }

        .submit-action:hover {
            background-color: #17a2b8;
            color: white;
            border-color: #17a2b8;
        }

        .approve-action {
            background: #ebf7ee;
            color: #408140;
        }

        .approve-action:hover {
            background: #408140;
            color: white !important;
        }

        .reject-action {
            background-color: #dc3545;
            color: white;
        }

        .reject-action:hover {
            background-color: #051937;
            color: white;
        }

        .cancel-action {
            background-color: #dc3545;
            color: white;
        }

        .cancel-action:hover {
            background-color: #051937;
            color: white;
        }

        .complete-action {
            background: #ebf7ee;
            color: #408140;
        }

        .complete-action:hover {
            background: #408140;
            color: white !important;
        }

        .view-file-action {
            background: #e3f2fd;
            color: #1d82f5;
        }

        .view-file-action:hover {
            background: #1d82f5;
            color: white;
        }

        /* Adjust badge positioning for better alignment */
        .status-badge {
            vertical-align: middle;
            display: inline-block;
        }
        
        .task-title {
            font-size: 14px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .task-meta {
            font-size: 12px;
            color: #6c757d;
        }
        
        .action-button {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        /* Responsive layout for task items */
        .task-status-icon {
            flex-shrink: 0; /* Prevent icon from shrinking */
            min-width: 32px; /* Ensure minimum width */
        }

        .task-actions {
            flex-shrink: 0; /* Prevent action buttons from shrinking */
            min-width: fit-content; /* Ensure buttons don't wrap */
        }

        .task-content {
            min-width: 0; /* Allow content to shrink */
            flex: 1; /* Take remaining space */
        }

        .task-title {
            word-wrap: break-word; /* Allow title to wrap */
            overflow-wrap: break-word;
        }

        .task-meta {
            word-wrap: break-word; /* Allow meta text to wrap */
            overflow-wrap: break-word;
        }

        /* Adjust badge positioning for better alignment */
        .status-badge {
            vertical-align: middle;
            display: inline-block;
        }
    </style>
    @endpush

    @php
        $showBoarding = false;
        $manageOffboardingPermission = user()->permission('manage_employee_offboarding');

        // Improved logic for determining when to show offboarding
        if ($employee->employeeDetail) {
            $onboardingStatus = $employee->employeeDetail->onboarding_status;
            $offboardCompleted = $employee->employeeDetail->offboard_completed;
            
            // Show offboarding if:
            // 1. Employee is in offboarding status (onboarding_status = 'offboarding')
            // 2. Offboarding is not completed (offboard_completed = 0)
            // 3. Admin viewing profile page
            // 4. User has manage permission
            if ($onboardingStatus === 'offboarding' || $offboardCompleted === 0 || 
                (in_array('admin', user_roles()) && $type === 'profile') || 
                $manageOffboardingPermission === 'all') {
                $showBoarding = true;
            }
        }

        // Count tasks for progress tracking
        $totalOffboardTasks = count($offboardingTasks);
        $offBoardCompleteTasks = 0;
        
        if ($employee->employeeDetail) {
            $offBoardCompleteTasks = \Modules\Onboarding\Entities\OnboardingCompletedTask::where('employee_id', $employee->id)
                ->where('type', 'offboard')
                ->where('status', 'completed')
                ->count();
        }
    @endphp

    @if ($showBoarding && $totalOffboardTasks > 0)
        @php
            $action = '';

            // Calculate pending counts
            $pendingEmployeeOffboard = \Modules\Onboarding\Entities\OnboardingCompletedTask::where('employee_id', $employee->id)
                ->where('type', 'offboard')
                ->where('status', 'pending')
                ->whereHas('onboardingTask', function($q){ $q->where('task_for','employee'); })
                ->count();

            $pendingAnyOffboard = \Modules\Onboarding\Entities\OnboardingCompletedTask::where('employee_id', $employee->id)
                ->where('type', 'offboard')
                ->where('status', 'pending')
                ->count();

            // Show action buttons only if offboarding is in progress
            if ($employee->employeeDetail && $employee->employeeDetail->offboard_completed == 0) {
                $showMarkAll = in_array('admin', user_roles()) ? ($pendingAnyOffboard > 0) : ($pendingEmployeeOffboard > 0);

                if ($showMarkAll) {
                    $action = '<button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center mr-2" id="completeAllOffBoarding"> <i class="fa fa-check-circle mr-1"></i> ' . __('onboarding::messages.markAllAsComplete') . ' </button> ';
                }
            }
            
            // Show cancel button if user has manage permission OR if admin (even when completed)
            if ($manageOffboardingPermission === 'all' || in_array('admin', user_roles())) {
                $action .= '<button type="button" class="btn btn-sm btn-danger d-inline-flex align-items-center" id="closeOffBoarding"> <i class="fa fa-undo mr-1"></i> ' . __('onboarding::messages.cancelOffBoarding') . ' </button>';
            }
        @endphp
        <x-cards.data class="mb-4 rounded shadow-sm break-inside-avoid dashboard-widget" :title="__('onboarding::clan.menu.offboardingTask')" padding="false"
         action="<div class='d-flex'>{!! $action !!}</div>"
            otherClasses="h-200 p-activity-detail cal-information">
            <!-- Timeline -->
            <div class="px-3">
                @foreach ($offboardingTasks as $task)
                @php
                // Determine task visibility and status
                $hideTask = false;
                
                // Hide company tasks from employees unless they can see them
                if (in_array('employee', user_roles()) && $task->task_for == 'company' && !$task->employee_can_see) {
                    $hideTask = true;
                }

                // Admins can see all tasks on profile page
                if (in_array('admin', user_roles()) && $type === 'profile') {
                    $hideTask = false;
                }

                // Users with manage permission can see all tasks
                if ($manageOffboardingPermission === 'all') {
                    $hideTask = false;
                }

                // Determine if task is disabled for completion
                $isDisabled = false;
                
                if ($task->completedTask && $task->completedTask->status === 'completed') {
                    $isDisabled = true; // Already completed
                } elseif ($task->task_for === 'company' && $task->employee_can_see && !in_array('admin', user_roles())) {
                    $isDisabled = true; // Company task, employee can't complete
                } elseif ($task->task_for === 'employee' && $task->employee_can_see && in_array('admin', user_roles())) {
                    $isDisabled = true; // Employee task, admin can't complete
                }

                // Override for users with manage permission
                if ($manageOffboardingPermission === 'all') {
                    $isDisabled = false;
                }

                $isChecked = $task->completedTask && $task->completedTask->status === 'completed';
                @endphp

                @if (!$hideTask)
                <div class="task-item d-flex align-items-start">
                    <div class="task-status-icon {{ $isChecked ? 'completed' : 'pending' }} mr-3">
                        <i class="fa {{ $isChecked ? 'fa-check' : 'fa-clock' }}"></i>
                    </div>
                    
                    <div class="task-content flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="task-title mb-0 fw-semibold text-dark">{{ $task['title'] }}</h6>
                            <div class="task-actions d-flex gap-1">
                                @if ($task->completedTask)
                                    @php
                                        $submissionStatus = $task->completedTask->submission_status ?? 'pending';
                                        $isSubmitted = $submissionStatus === 'submitted';
                                        $isApproved = $submissionStatus === 'approved';
                                        $isRejected = $submissionStatus === 'rejected';
                                        $isPending = $submissionStatus === 'pending';
                                    @endphp

                                    @if ($task->completedTask->status === 'pending')
                                        @if ($task->task_for === 'company' && $manageOffboardingPermission !== 'all' && !in_array('admin', user_roles()))
                                            <span class="badge badge-info">Company Task</span>
                                        @elseif ($manageOffboardingPermission === 'all' || in_array('admin', user_roles()) || (!$isDisabled && $task->task_for === 'employee'))
                                            @if ($isPending)
                                                {{-- Employee completes via modal (same flow as admin) --}}
                                                @if ($task->completedTask->employee_id == user()->id)
                                                    <a href="javascript:;" 
                                                        class="action-button complete-action complete-offboard-task"
                                                        data-task-id="{{ $task->id }}"
                                                        data-emp-id="{{ $employee->id }}"
                                                        data-task-type="{{ $task->type }}">
                                                        <i class="fa fa-check mr-1"></i>
                                                        Complete Task
                                                    </a>
                                                @endif
                                                
                                                {{-- Admin/Manager can mark as complete directly --}}
                                                @if ($manageOffboardingPermission === 'all' || in_array('admin', user_roles()))
                                                    <a href="javascript:;" 
                                                        class="action-button complete-action complete-offboard-task"
                                                        data-task-id="{{ $task->id }}" 
                                                        data-emp-id="{{ $employee->id }}"
                                                        data-task-type="{{ $task->type }}"
                                                        id="offboardTask{{ $task->id }}">
                                                        <i class="fa fa-check mr-1"></i>
                                                        @lang('modules.tasks.markComplete')
                                                    </a>
                                                @endif
                                            @elseif ($isSubmitted)
                                                {{-- Admin/Manager can approve/reject --}}
                                                @if ($manageOffboardingPermission === 'all' || in_array('admin', user_roles()))
                                                    <a href="javascript:;" 
                                                        class="action-button approve-action approve-offboard-task"
                                                        data-task-id="{{ $task->completedTask->id }}">
                                                        <i class="fa fa-check mr-1"></i>
                                                        Approve
                                                    </a>
                                                    <a href="javascript:;" 
                                                        class="action-button reject-action reject-offboard-task"
                                                        data-task-id="{{ $task->completedTask->id }}">
                                                        <i class="fa fa-times mr-1"></i>
                                                        Reject
                                                    </a>
                                                @else
                                                    <span class="badge badge-warning pt-2">Pending Approval</span>
                                                @endif
                                            @elseif ($isApproved)
                                                <span class="badge badge-success">Approved</span>
                                                @if ($manageOffboardingPermission === 'all' || in_array('admin', user_roles()))
                                                    <a href="javascript:;" 
                                                        class="action-button cancel-action cancel-offboard-task"
                                                        data-task-id="{{ $task->completedTask->id }}">
                                                        <i class="fa fa-undo mr-1"></i>
                                                        Cancel
                                                    </a>
                                                @endif
                                            @elseif ($isRejected)
                                                @if ($task->completedTask->rejection_reason)
                                                    <span class="text-muted ms-2 rejection-reason-tooltip" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $task->completedTask->rejection_reason }}">
                                                        <i class="fa fa-info-circle"></i>
                                                    </span>
                                                @endif
                                                {{-- Show Complete Task button for rejected tasks --}}
                                                @if ($task->completedTask->employee_id == user()->id)
                                                    <a href="javascript:;"
                                                        class="action-button complete-action complete-offboard-task"
                                                        data-task-id="{{ $task->id }}"
                                                        data-emp-id="{{ $employee->id }}"
                                                        data-task-type="{{ $task->type }}"
                                                        id="offboardTask{{ $task->id }}">
                                                        <i class="fa fa-check mr-1"></i>
                                                        Complete Task
                                                    </a>
                                                @endif
                                                @if ($manageOffboardingPermission === 'all' || in_array('admin', user_roles()))
                                                    <a href="javascript:;" 
                                                        class="action-button cancel-action cancel-offboard-task"
                                                        data-task-id="{{ $task->completedTask->id }}">
                                                        <i class="fa fa-undo mr-1"></i>
                                                        Cancel
                                                    </a>
                                                @endif
                                            @endif
                                        @endif
                                    @endif
                                @endif

                                {{-- Show Cancel button for completed tasks (not in approval workflow) --}}
                                @if ($task->completedTask && $task->completedTask->status === 'completed' &&
                                    ($manageOffboardingPermission === 'all' || in_array('admin', user_roles())))
                                    <a href="javascript:;" 
                                        class="action-button cancel-action cancel-offboard-task"
                                        data-task-id="{{ $task->completedTask->id }}">
                                        <i class="fa fa-undo mr-1"></i>
                                        Cancel
                                    </a>
                                @endif

                                {{-- Show View File button for any task with a file --}}
                                @if ($task->completedTask && $task->completedTask->file)
                                    {{-- Admin/Manager can view any file --}}
                                    @if (in_array('admin', user_roles()) || $manageOffboardingPermission === 'all')
                                        <a class="action-button view-file-action" 
                                            href="{{ route('view.file', $task->completedTask->file) }}"
                                            target="_blank">
                                            <i class="fa fa-file mr-1"></i>
                                            {{ __('onboarding::clan.viewFile') }}
                                        </a>
                                    {{-- Employee can view their own submitted files or company files with employee_can_see --}}
                                    @elseif ($task->completedTask->employee_id == user()->id || 
                                            ($task->task_for == 'company' && $task->employee_can_see))
                                        <a class="action-button view-file-action" 
                                            href="{{ route('view.file', $task->completedTask->file) }}"
                                            target="_blank">
                                            <i class="fa fa-file mr-1"></i>
                                            {{ __('onboarding::clan.viewFile') }}
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </div>
                        
                        <div class="task-meta text-muted small">
                            @if ($task->completedTask && $task->completedTask['user_id'])
                                @if ($task->completedTask->submission_status === 'submitted')
                                    <i class="fa fa-clock mr-1"></i>
                                    @lang('onboarding::clan.submittedOn') @lang('app.on')
                                    @if ($task->completedTask['submitted_on'])
                                        {{ \Carbon\Carbon::parse($task->completedTask['submitted_on'])->translatedFormat(company()->date_format) }}
                                    @elseif ($task->completedTask['completed_on'])
                                        {{ \Carbon\Carbon::parse($task->completedTask['completed_on'])->translatedFormat(company()->date_format) }}
                                    @else
                                        {{ \Carbon\Carbon::parse($task->completedTask['updated_at'])->translatedFormat(company()->date_format) }}
                                    @endif
                                    <i class="fa fa-exclamation-triangle ms-2"></i> Pending Approval
                                @elseif ($task->completedTask->submission_status === 'rejected')
                                    <i class="fa fa-times-circle mr-1"></i>
                                    @lang('app.rejected') @lang('app.on') 
                                    @if ($task->completedTask['rejected_on'])
                                        {{ \Carbon\Carbon::parse($task->completedTask['rejected_on'])->translatedFormat(company()->date_format) }}
                                    @elseif ($task->completedTask['updated_at'])
                                        {{ \Carbon\Carbon::parse($task->completedTask['updated_at'])->translatedFormat(company()->date_format) }}
                                    @endif
                                    @lang('app.by') <strong>{{ $task->completedTask->rejectedBy->name ?? 'Admin' }}</strong>
                                @elseif ($task->completedTask['completed_on'] || $task->completedTask->status === 'completed')
                                    <i class="fa fa-check-circle mr-1"></i>
                                    @lang('app.completed') @lang('app.on') 
                                    @if ($task->completedTask['completed_on'])
                                        {{ \Carbon\Carbon::parse($task->completedTask['completed_on'])->translatedFormat(company()->date_format) }}
                                    @elseif ($task->completedTask['approved_on'])
                                        {{ \Carbon\Carbon::parse($task->completedTask['approved_on'])->translatedFormat(company()->date_format) }}
                                    @else
                                        {{ \Carbon\Carbon::parse($task->completedTask['updated_at'])->translatedFormat(company()->date_format) }}
                                    @endif
                                    @lang('app.by') <strong>{{ $task->completedTask->user->name }}</strong>
                                @elseif ($task->completedTask['updated_at'])
                                    <i class="fa fa-clock mr-1"></i>
                                    @lang('app.added') @lang('app.on')
                                    <strong>{{ \Carbon\Carbon::parse($task->completedTask['updated_at'])->translatedFormat(company()->date_format) }}</strong> <i class="fa fa-times-circle ml-2"></i>
                                    @lang('app.pending')
                                @endif
                            @elseif ($task->completedTask && $task->completedTask['updated_at'])
                                <i class="fa fa-clock mr-1"></i>
                                @lang('app.added') @lang('app.on')
                                <strong>{{ \Carbon\Carbon::parse($task->completedTask['updated_at'])->translatedFormat(company()->date_format) }}</strong> <i class="fa fa-times-circle ml-2"></i>
                                @lang('app.pending')
                            @endif

                            @if ($task->task_for == 'company')
                                <i class="fa fa-building ml-2"></i> Company Task
                            @elseif ($task->task_for == 'employee')
                                <i class="fa fa-user ml-2"></i> Employee Task
                            @else
                                <i class="fa fa-list ml-2"></i> Company Task
                            @endif
                            
                        </div>
                    </div>
                </div>
                 @endif
                @endforeach
            </div>
        </x-cards.data>
    @endif


    @push('scripts')
        <script>
            $(document).ready(function() {
                // Initialize tooltips for rejection reasons
                $('[data-bs-toggle="tooltip"]').tooltip();

                // Employee uses same complete modal as admin (no separate submit flow)

                // Approve task
                $('.approve-offboard-task').on('click', function() {
                    var taskId = $(this).data('task-id');
                    var token = "{{ csrf_token() }}";
                    var url = "{{ route('onboarding-approve-task') }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {
                            '_token': token,
                            'task_id': taskId
                        },
                        success: function(response) {
                            if (response.status == "success") {
                                window.location.reload();
                            }
                        }
                    });
                });

                // Reject task
                $('.reject-offboard-task').on('click', function() {
                    var taskId = $(this).data('task-id');
                    
                    Swal.fire({
                        title: "Reject Task",
                        text: "Please provide a reason for rejection:",
                        icon: 'warning',

                        input: 'text',
                        inputPlaceholder: 'Enter rejection reason...',
                        showCancelButton: true,
                        confirmButtonText: 'Reject',
                        cancelButtonText: 'Cancel',
                        customClass: {
                            confirmButton: 'btn btn-primary mr-3',
                            cancelButton: 'btn btn-secondary'
                        },
                        showClass: {
                            popup: 'swal2-noanimation',
                            backdrop: 'swal2-noanimation'
                        },
                        inputValidator: (value) => {
                            if (!value) {
                                return 'You need to provide a reason!'
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            var token = "{{ csrf_token() }}";
                            var url = "{{ route('onboarding-reject-task') }}";

                            $.easyAjax({
                                type: 'POST',
                                url: url,
                                data: {
                                    '_token': token,
                                    'task_id': taskId,
                                    'rejection_reason': result.value
                                },
                                success: function(response) {
                                    if (response.status == "success") {
                                        window.location.reload();
                                    }
                                }
                            });
                        }
                    });
                });

                // Cancel task
                $('.cancel-offboard-task').on('click', function() {
                    var taskId = $(this).data('task-id');
                    
                    Swal.fire({
                        title: "@lang('messages.sweetAlertTitle')",
                        text: "Are you sure you want to cancel this task?",
                        icon: 'warning',
                        showCancelButton: true,
                        focusConfirm: false,
                        confirmButtonText: "Yes, Cancel Task",
                        cancelButtonText: "@lang('app.cancel')",
                        customClass: {
                            confirmButton: 'btn btn-primary mr-3',
                            cancelButton: 'btn btn-secondary'
                        },
                        showClass: {
                            popup: 'swal2-noanimation',
                            backdrop: 'swal2-noanimation'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            var token = "{{ csrf_token() }}";
                            var url = "{{ route('onboarding-cancel-task') }}";

                            $.easyAjax({
                                type: 'POST',
                                url: url,
                                data: {
                                    '_token': token,
                                    'task_id': taskId
                                },
                                success: function(response) {
                                    if (response.status == "success") {
                                        window.location.reload();
                                    }
                                }
                            });
                        }
                    });
                });

                $('.complete-offboard-task').on('click', function() {
                    var taskId = $(this).data('task-id');
                    var empId = $(this).data('emp-id');
                    var url = "{{ route('onboarding-dashboard.create') }}" + '?task_id=' + taskId + '&empId=' +
                        empId;

                    $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
                    $.ajaxModal(MODAL_LG, url);
                });

                $(document).on('click', '#closeOffBoarding', function() {

                    Swal.fire({
                        title: "@lang('messages.sweetAlertTitle')",
                        text: "@lang('messages.recoverRecord')",
                        icon: 'warning',
                        showCancelButton: true,
                        focusConfirm: false,
                        confirmButtonText: "@lang('onboarding::messages.confirmCancel')",
                        cancelButtonText: "@lang('app.cancel')",
                        customClass: {
                            confirmButton: 'btn btn-primary mr-3',
                            cancelButton: 'btn btn-secondary'
                        },
                        showClass: {
                            popup: 'swal2-noanimation',
                            backdrop: 'swal2-noanimation'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            var empId = {{ $employee->id }};
                            var token = "{{ csrf_token() }}";
                            var url = "{{ route('onboarding-cancel-request') }}";

                            $.easyAjax({
                                type: 'POST',
                                url: url,
                                data: {
                                    '_token': token,
                                    'type': 'offboard',
                                    'empId': empId
                                },
                                success: function(response) {
                                    if (response.status == "success") {
                                        window.location.reload();
                                    }
                                }
                            });
                        }
                    });
                });

                $(document).on('click', '#completeAllOffBoarding', function() {

                    Swal.fire({
                        title: "@lang('messages.sweetAlertTitle')",
                        text: "@lang('onboarding::messages.markAllAsCompleteConfirm')",
                        icon: 'warning',
                        showCancelButton: true,
                        focusConfirm: false,
                        confirmButtonText: "@lang('onboarding::messages.markAllAsComplete')",
                        cancelButtonText: "@lang('app.cancel')",
                        customClass: {
                            confirmButton: 'btn btn-primary mr-3',
                            cancelButton: 'btn btn-secondary'
                        },
                        showClass: {
                            popup: 'swal2-noanimation',
                            backdrop: 'swal2-noanimation'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            var empId = {{ $employee->id }};
                            var token = "{{ csrf_token() }}";
                            var url = "{{ route('onboarding-completealloffboarding-request') }}";

                            $.easyAjax({
                                type: 'POST',
                                url: url,
                                data: {
                                    '_token': token,
                                    'type': 'offboard',
                                    'empId': empId
                                },
                                success: function(response) {
                                    if (response.status == "success") {
                                        window.location.reload();
                                    }
                                }
                            });
                        }
                    });
                });
            });
        </script>
    @endpush
@endif
