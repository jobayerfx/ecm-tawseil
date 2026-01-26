<link rel="stylesheet" href="{{ asset('vendor/css/bootstrap-colorpicker.css') }}">
<div class="modal-header">
    <h5 class="modal-title">@lang('onboarding::clan.menu.createOffboardingTask')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>
<x-form id="createOffboarding" method="POST" class="ajax-form">
    <div class="modal-body">
        <div class="portlet-body">
            <div class="row">
                <div class="col-sm-12">
                    <x-forms.text :fieldLabel="__('onboarding::clan.menu.title')" fieldName="title" fieldId="title" fieldRequired="true"/>
                </div>
                <div class="col-md-6">
                    <div class="form-group my-3">
                        <label class="f-14 text-dark-grey mb-1 w-100" for="onboarding">@lang('onboarding::clan.menu.taskFor')</label>
                        <div class="d-flex">
                            <x-forms.radio fieldId="employee" :fieldLabel="__('onboarding::clan.menu.employee')" fieldValue="employee" fieldName="task_for" checked>
                            </x-forms.radio>
                            <x-forms.radio fieldId="company" :fieldLabel="__('onboarding::clan.menu.company')" fieldValue="company" fieldName="task_for">
                            </x-forms.radio>
                        </div>
                    </div>
                </div>
                <div id="employee-access" class="col-md-6 my-3" style="display:none;">
                    <x-forms.checkbox fieldId="employee-access-checkbox" :fieldLabel="__('onboarding::clan.menu.employeeCanSee')" fieldValue="1" fieldName="employee_can_see">
                    </x-forms.checkbox>
                </div>

                <div class="col-md-6">
                    <input type="hidden" name="type" id="type" value= "offboard"> <!-- Default value is 'offboard' -->

                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
        <x-forms.button-primary id="save-offboarding-setting" icon="check">@lang('app.save')</x-forms.button-primary>
        <!-- Hidden input field for task_for -->
        <input type="hidden" name="task_for" id="task_for" value="employee">
    </div>
</x-form>

<script src="{{ asset('vendor/jquery/bootstrap-colorpicker.js') }}"></script>
<script>
    $(document).ready(function () {
        $('#colorpicker').colorpicker({ "color": "#16813D" });

        $('#company').click(function () {
            $('#employee-access').show();
            $('#task_for').val('company');
        });

        $('#employee').click(function () {
            $('#employee-access').hide();
            $('#task_for').val('employee');
        });

        $('#employee-access-checkbox').change(function () {
            if ($(this).is(':checked')) {
                $('input[name="employee_can_see"]').val(1);
            } else {
                $('input[name="employee_can_see"]').val(0);
            }
        });

        $('#save-offboarding-setting').click(function () {
            
            $.easyAjax({
                container: '#createOffboarding',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-offboarding-setting",
                url: "{{ route('onboarding-settings.store') }}",
                data: $('#createOffboarding').serialize(),
                success: function (response) {
                    if (response.status === 'success') {
                        window.location.reload();
                    }
                }
            });
        });
    });
</script>

