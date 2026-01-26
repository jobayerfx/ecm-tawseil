<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang("groupmessage::app.addChannel")</h5>
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
    <x-forms.button-primary id="save-channel" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>

    $('#save-channel').click(function () {
        let url = "{{ route('channel-messages.store') }}";

        $.easyAjax({
            url: url,
            container: '#createConversationForm',
            disableButton: true,
            blockUI: true,
            buttonSelector: "#save-channel",
            type: "POST",
            data: $('#createConversationForm').serialize(),
            success: function (response) {
                $('#user_list').val(response.user_list);
                $('#message_list').val(response.message_list);
                $('#receiver_id').val(response.receiver_id);
                $('.message-user').html(response.userName);
                $('#current_channel_id').val(response.receiver_id);

                setContent();

                $('.show-user-messages').removeClass('active');
                $('#user-no-' + response.receiver_id + ' a').addClass('active');
            }
        })
    });

    function setContent() {
        document.getElementById('msgLeft').innerHTML = $('#user_list').val();
        document.getElementById('chatBox').innerHTML = $('#message_list').val();
        $('#sendMessageForm').removeClass('d-none');
        // quillMention(null, '#submitTexts');
        $(MODAL_LG).modal('hide');
    }

    init('#createConversationForm');
</script>
