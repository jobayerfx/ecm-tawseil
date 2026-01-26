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
    <h5 class="modal-title" id="modelHeading">@lang("groupmessage::app.editGroup")</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-form id="createConversationForm" method="PUT">
        <input type="hidden" name="id" value="{{$group->id}}">
        <div class="row">
            <div class="col-xl-12 col-lg-12 col-sm-12">
                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <x-forms.text fieldId="name" :fieldLabel="__('app.name')"
                                        fieldName="name" fieldRequired="true"
                                        :fieldPlaceholder="__('placeholders.name')" :fieldValue="$group->name">
                        </x-forms.text>
                    </div>

                    <div class="col-md-12 col-sm-12">
                        <x-forms.label class="my-3" fieldId="members"
                            :fieldLabel="__('groupmessage::app.selectMembers')" fieldRequired="true">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control multiple-users" multiple name="members[]"
                                    id="members" data-live-search="true" data-size="8">
                                    @foreach ($employees as $emp)
                                        <x-user-option :user="$emp" :pill=true :selected="in_array($emp->id, $attendeeArray)"/>
                                    @endforeach
                                </select>
                        </x-forms.input-group>
                    </div>

                    <div class="col-md-12 col-sm-12">
                        <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.description')"
                            fieldName="description" fieldId="description" :fieldValue="$group->description">
                        </x-forms.textarea>
                    </div>
                </div>
            </div>
        </div>
    </x-form>
</div>
<div class="modal-footer mr-2">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-group" icon="check">@lang('app.update')</x-forms.button-primary>
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

    $('#save-group').click(function () {

        $('#group-members-container').html('');
        const groupId = '{{ $group->id }}';

        let url = "{{ route('group-messages.update', ':id') }}";
        url = url.replace(':id', groupId);

        $.easyAjax({
            url: url,
            container: '#createConversationForm',
            disableButton: true,
            blockUI: true,
            buttonSelector: "#save-group",
            type: "POST",
            data: $('#createConversationForm').serialize(),
            success: function (response) {
                $('.message-user').html(response.group_name);
                $('#user_list').val(response.user_list);
                $('#message_list').val(response.message_list);
                $('#receiver_id').val(response.receiver_id);
                $('#current_group_id').val(groupId);
                setContent();
                $('.show-user-messages').removeClass('active');
                $('#user-no-' + groupId + ' a').addClass('active');
                fetchGroupMembers(groupId);
            }
        })
    });

    function setContent() {
        document.getElementById('msgLeft').innerHTML = $('#user_list').val();
        document.getElementById('chatBox').innerHTML = $('#message_list').val();
        $('#sendMessageForm').removeClass('d-none');

        $(MODAL_LG).modal('hide');

        scrollChat();
    }

    init('#createConversationForm');
</script>
