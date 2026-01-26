<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">
<style>
    #message-new .ql-editor {
        border: 1px solid #a3a3a3;
        border-radius: 6px;
        padding-left: 6px !important;
        height: 100% !important;
    }

    .ql-editor-disabled {
        border-radius: 6px;
        background-color: rgba(124, 0, 0, 0.2);
        transition-duration: 0.5s;
    }

    .ql-toolbar {
        display: none !important;
    }

</style>
<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang("groupmessage::app.addGroup")</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-form id="createConversationForm">
        <div class="row">
            <div class="col-xl-12 col-lg-12 col-sm-12">
                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <x-forms.text fieldId="name" :fieldLabel="__('app.name')"
                                        fieldName="name" fieldRequired="true"
                                        :fieldPlaceholder="__('placeholders.name')">
                        </x-forms.text>
                    </div>

                    <div class="col-md-12 col-sm-12">
                        <x-forms.label class="my-3" fieldId="members"
                            :fieldLabel="__('groupmessage::app.selectMembers')" fieldRequired="true">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="members[]"
                                id="members" data-live-search="true" multiple>
                                @foreach ($employees as $employee)
                                    <x-user-option :user="$employee" :pill="true"/>
                                @endforeach
                            </select>
                        </x-forms.input-group>
                    </div>

                    <div class="col-md-12 col-sm-12">
                        <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.description')"
                            fieldName="description" fieldId="description">
                        </x-forms.textarea>
                    </div>
                </div>
            </div>
        </div>
    </x-form>
</div>
<div class="modal-footer mr-2">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-group" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>

    $('#selectEmployee').selectpicker();

    $("#members").selectpicker({
        actionsBox: true,
        selectAllText: "{{ __('modules.permission.selectAll') }}",
        deselectAllText: "{{ __('modules.permission.deselectAll') }}",
        multipleSeparator: " ",
        selectedTextFormat: "count > 8",
        countSelectedText: function (selected, total) {
            return selected + " {{ __('app.membersSelected') }} ";
        }
    });

    @if (isset($userData))
        var atValues = @json($userData);
        quillMention(atValues, '#message-new');
    @endif

    $("input[name=user_type]").click(function () {
        if ($(this).val() == 'employee') {
            $('#member-list').removeClass('d-none');
            $('#client-list').addClass('d-none');
        } else {
            $('#member-list').addClass('d-none');
            $('#client-list').removeClass('d-none');
        }
    });

    $('#save-group').click(function () {
        var url = "{{ route('group-messages.store') }}";

        $.easyAjax({
            url: url,
            container: '#createConversationForm',
            disableButton: true,
            blockUI: true,
            buttonSelector: "#save-group",
            type: "POST",
            data: $('#createConversationForm').serialize(),
            success: function (response) {
                $('#user_list').val(response.user_list);
                $('#message_list').val(response.message_list);
                $('#receiver_id').val(response.receiver_id);
                $('.message-user').html(response.userName);
                $('#current_group_id').val(response.receiver_id);

                setContent();

                $('.show-user-messages').removeClass('active');
                $('#user-no-' + response.receiver_id + ' a').addClass('active');
                let receiverId = $('#chatBox').data('chat-for-user');
                // $('#user-no-' + receiverId + ' a').addClass('active');
            }
        })
    });

    function setContent() {
        @if (isset($client))
        let clientId = $('#client_id').val();
        var redirectUrl = "{{ route('group-messaging.index') }}?clientId=" + clientId;
        window.location.href = redirectUrl;
        @endif

        document.getElementById('msgLeft').innerHTML = $('#user_list').val();
        document.getElementById('chatBox').innerHTML = $('#message_list').val();
        $('#sendMessageForm').removeClass('d-none');

        if ($("input[name=user_type]").length > 0 && $("input[name=user_type]").val() ==
            'client') {
            var userId = $('#client-list').val();
        } else {
            var userId = $('#selectEmployee').val();
        }

        $('#current_user_id').val(userId);
        $('#receiver_id').val(userId);
        $(MODAL_LG).modal('hide');
    }

    // If request comes from project overview tab where client id is set, then it will select that client name default
    @if (isset($client))
    $("#user-type-client").prop("checked", true);
    $('#member-list, #client-list').toggleClass('d-none');
    @endif

    init('#createConversationForm');
</script>
