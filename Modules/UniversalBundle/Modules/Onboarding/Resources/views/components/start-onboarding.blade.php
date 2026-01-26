@if ($employee && $employee->employeeDetail)

    @php
        $viewonboardingPermission = user()->permission('manage_employee_onboarding');
        $viewoffboardingPermission = user()->permission('manage_employee_offboarding');

        // Direct logic to determine button visibility
        $onboardingStatus = $employee->employeeDetail->onboarding_status;
        $onboardCompleted = $employee->employeeDetail->onboard_completed;
        $offboardCompleted = $employee->employeeDetail->offboard_completed;
        
        // Check if onboarding is in progress (has pending tasks)
        $onboardingInProgress = \Modules\Onboarding\Entities\OnboardingCompletedTask::where('employee_id', $employee->id)
            ->where('type', 'onboard')
            ->where('status', 'pending')
            ->exists();
            
        // Check if offboarding is in progress (has pending tasks)
        $offboardingInProgress = \Modules\Onboarding\Entities\OnboardingCompletedTask::where('employee_id', $employee->id)
            ->where('type', 'offboard')
            ->where('status', 'pending')
            ->exists();
        
        // Show start onboarding button if:
        // 1. Onboarding is not completed (onboard_completed = 0), AND
        // 2. Onboarding is not in progress, AND
        // 3. Offboarding is not in progress, AND
        // 4. User has manage onboarding permission OR is admin
        $showStartOnboarding = ($onboardCompleted == 0 && !$onboardingInProgress && !$offboardingInProgress && 
            ($viewonboardingPermission === 'all' || in_array('admin', user_roles())));
        
        // Show start offboarding button if:
        // 1. Onboarding is completed (onboard_completed = 1), AND
        // 2. Offboarding is not completed (offboard_completed = 0), AND
        // 3. Offboarding is not in progress, AND
        // 4. Onboarding is not in progress, AND
        // 5. User has manage offboarding permission OR is admin
        $showStartOffboarding = (($onboardCompleted == 1 || ($onboardCompleted == 0 && $employee->employeeDetail->onboarding_status == 'old')) && $offboardCompleted == 0 && !$offboardingInProgress && !$onboardingInProgress && 
            ($viewoffboardingPermission === 'all' || in_array('admin', user_roles())));
    @endphp

    @if ($showStartOnboarding)
        <a class="dropdown-item" href="javascript:;" id="startOnboardingBtn" data-pinned="pinned">@lang('onboarding::clan.startOnboarding')</a>
    @endif

    @if ($showStartOffboarding)
        <a class="dropdown-item" href="javascript:;" id="startOffboardingBtn" data-pinned="pinned">@lang('onboarding::clan.startOffboarding')</a>
    @endif

@endif

@push('scripts')
    <script>
        // Start offboarding
        $('body').on('click', '#startOffboardingBtn', function() {
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('onboarding::messages.confirmOffboardingText')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('onboarding::messages.confirmOffboarding')",
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
                    var url = "{{ route('start.offboarding') }}";
                    var token = '{{ csrf_token() }}';

                    $.easyAjax({
                        url: url,
                        container: '#startOffboardingButton',
                        type: "POST",
                        blockUI: true,
                        buttonSelector: "#startOffboardingBtn",
                        disableButton: true,
                        data: {
                            _token: token,
                            employee_id: "{{ $employee->id }}",
                        },
                        success: function() {
                            window.location.reload();
                        }
                    });
                }
            });
        });

        // Start onboarding
        $('body').on('click', '#startOnboardingBtn', function() {
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('onboarding::messages.confirmOnboardingText')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('onboarding::messages.confirmOnboarding')",
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
                    var url = "{{ route('start.onboarding') }}";
                    var token = '{{ csrf_token() }}';
                    var id = "{{ $employee->id }}";

                    $.easyAjax({
                        url: url,
                        container: '#startOnboardingButton',
                        type: "POST",
                        blockUI: true,
                        buttonSelector: "#startOnboardingBtn",
                        disableButton: true,
                        data: {
                            _token: token,
                            id: id
                        },
                        success: function() {
                            window.location.reload();
                        }
                    });
                }
            });
        });

    </script>
@endpush
