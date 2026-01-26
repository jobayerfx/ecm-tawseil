<link rel="stylesheet" href="{{ asset('vendor/css/bootstrap-colorpicker.css') }}" />
<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/css/image-picker.min.css') }}">

<div class="modal-header">
    <h5 class="modal-title">{{ $taskName }}</h5>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>
<x-form id="createOnboarding" method="POST" class="ajax-form" enctype="multipart/form-data">
    <div class="modal-body">
        <div class="portlet-body">
            <div class="row">
                <input type="hidden" name="type" id="type" value="onboard">
                <input type="hidden" name="empId" id="type" value="{{ $empId }}">
                <input type="hidden" name="onboarding_task_id" id="onboarding_task_id" value="{{ $taskId }}">

                <div class="col-lg-12 col-md-8">
                    <x-forms.datepicker fieldId="completed_on" fieldRequired="true" :fieldLabel="__('onboarding::clan.menu.completed_on')"
                        fieldName="completed_on" :fieldPlaceholder="__('placeholders.date')" :fieldValue="now(company()->timezone)->format(company()->date_format)" />
                </div>

                <div class="col-lg-12">
                    <x-forms.file allowedFileExtensions="png jpg jpeg svg bmp" class="mr-0 mr-lg-2 mr-md-2 dropify"
                        :fieldLabel="__('onboarding::clan.menu.file')" fieldName="file" fieldId="file"/>
                </div>

            </div>
        </div>
    </div>
    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
        <x-forms.button-primary id="save-onboarding-setting" icon="check">@lang('app.save')</x-forms.button-primary>
    </div>
</x-form>

<script src="{{ asset('vendor/jquery/bootstrap-colorpicker.js') }}"></script>
<script>
    $(document).ready(function() {

        $('#colorpicker').colorpicker({
            "color": "#16813D"
        });

        $(document).find('#file').dropify({
            messages: dropifyMessages
        });

        $('#save-onboarding-setting').click(function(event) {
            event.preventDefault();
            // Set the type to 'onboard' before submitting
            $('#type').val('onboard');

            var onboardingtaskid = {{ $taskId }};
            $('#onboarding_task_id').val(onboardingtaskid);
            var formData = new FormData($('#createOnboarding')[0]);

            $.easyAjax({
                container: '#createOnboarding',
                type: "POST",
                disableButton: true,
                blockUI: true,
                file: true,
                processData: false,
                contentType: false,
                data: formData,
                buttonSelector: "#save-onboarding-setting",
                url: "{{ route('onboarding-dashboard.store') }}",
                success: function(response) {
                    if (response.status === 'success') {

                        $('#onboardTask' + onboardingtaskid).prop('disabled', true);
                        window.location.reload();
                    }
                },

            })
        });


        $(document).on('hidden.bs.modal', MODAL_LG, function() {
            var taskID = {{ $taskId }};
            $('#onboardTask' + taskID).prop('checked', false);
        });

        $(document).on('click', '[data-dismiss="modal"]', function() {
            var taskId = $('#onboarding_task_id').val(); // Get the task ID associated with the modal

            // Uncheck the checkbox associated with the canceled task
            $('#onboardTask' + taskId).prop('checked', false);

            // Remove the checked state from localStorage
            localStorage.removeItem('task_' + taskId + '_checked');

            // Re-enable the checkbox if it was previously disabled
            $('#onboardTask' + taskId).removeAttr('disabled');
        });


        $('.cropper').on('dropify.fileReady', function(e) {
            var inputId = $(this).find('input').attr('id');
            var url = "{{ route('cropper', ':element') }}";
            url = url.replace(':element', inputId);
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });


        function monthlyOn() {
            let ele = $('#monthlyOn');
            let url = '{{ route('events.monthly_on') }}';
            setTimeout(() => {
                $.easyAjax({
                    url: url,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        date: $('#completed_on').val()
                    },
                    success: function(response) {
                        @if (App::environment('development'))
                            $('#event_name').val(response.message);
                            $('#selectAssignee').val({{ user()->id }});
                            $('#selectAssignee').selectpicker('refresh');
                        @endif
                        ele.html(response.message);
                        $('#repeat_type').selectpicker('refresh');
                    }
                });
            }, 100);

        }

        $(document).ready(function() {
            const dp1 = datepicker('#completed_on', {
                position: 'bl',
                ...datepickerConfig
            });
        });
    });
</script>
