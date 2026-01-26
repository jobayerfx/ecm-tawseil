<div class="modal-header">
    <h5 class="modal-title">@lang('onboarding::clan.menu.editOffboardingTask')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>
<x-form id="editOffboarding" method="POST" class="ajax-form">
    @method('PUT')
    <div class="modal-body">
        <div class="portlet-body">
            <div class="row">
                <div class="col-sm-12">
                    <x-forms.text :fieldLabel="__('onboarding::clan.menu.title')" fieldName="title" fieldId="title" fieldRequired="true" :fieldValue="$onboardingSetting->title"/>
                </div>
                <div class="col-md-6">
                    <div class="form-group my-3">
                        <label class="f-14 text-dark-grey mb-1 w-100" for="status">@lang('onboarding::clan.menu.taskFor')</label>
                        <div class="d-flex">
                            <x-forms.radio fieldId="employee" :fieldLabel="__('onboarding::clan.menu.employee')" fieldValue="employee" fieldName="task_for" :checked="$onboardingSetting->task_for === 'employee'">
                            </x-forms.radio>
                            <x-forms.radio fieldId="company" :fieldLabel="__('onboarding::clan.menu.company')" fieldValue="company" fieldName="task_for" :checked="$onboardingSetting->task_for === 'company'">
                            </x-forms.radio>
                        </div>
                    </div>
                </div>
                <div id="employee-access" class="col-md-6 my-3" style="display: {{ $onboardingSetting->task_for === 'company' ? 'block' : 'none' }}">
                    <x-forms.checkbox fieldId="employee-access-checkbox" :fieldLabel="__('onboarding::clan.menu.employeeCanSee')" fieldValue="1" fieldName="employee_can_see" :checked="$onboardingSetting->employee_can_see">
                    </x-forms.checkbox>
                </div>
                <input type="hidden" name="type" id="type" value="offboard">
                <input type="hidden" name="onboarding_status_id" value="{{ $onboardingSetting->id }}">
                <!-- Hidden input field for task_for -->
                <input type="hidden" name="task_for" id="task_for" value="{{ $onboardingSetting->task_for }}">
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
        <x-forms.button-primary id="update-offboarding-setting" icon="check">@lang('app.update')</x-forms.button-primary>
    </div>
</x-form>

<script>
    $(document).ready(function() {
        function toggleEmployeeCheckbox() {
            if ($('#company').is(':checked')) {
                $('#employee-access').show();
                $('#task_for').val('company');
            } else {
                $('#employee-access').hide();
                $('#task_for').val('employee');
            }
        }

        toggleEmployeeCheckbox();

        $('#company').click(function () {
            toggleEmployeeCheckbox();
        });

        $('#employee').click(function () {
            toggleEmployeeCheckbox();
        });

        // Handle checkbox change event to set value
        $('#employee-access-checkbox').change(function () {
            if ($(this).is(':checked')) {
                $('input[name="employee_can_see"]').val(1);
            } else {
                $('input[name="employee_can_see"]').val(0);
            }
        });

        $('#update-offboarding-setting').click(function () {
            $('#type').val('offboard');

            $.easyAjax({
                container: '#editOffboarding',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#update-offboarding-setting",
                url: "{{ route('onboarding-settings.update', $onboardingSetting->id) }}",
                data: $('#editOffboarding').serialize(),
                success: function (response) {
                    if (response.status === 'success') {
                        window.location.reload();
                    }
                }
            })
        });
    });
</script>

