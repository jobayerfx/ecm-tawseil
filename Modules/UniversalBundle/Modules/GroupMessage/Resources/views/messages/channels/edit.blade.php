<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang("groupmessage::app.editChannel")</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-form id="createConversationForm" method="PUT">
        <input type="hidden" name="channel_id" id="channel_id" value="{{ $channel->id }}">
        <div class="row">
            <div class="col-xl-12 col-lg-12 col-sm-12">
                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <x-forms.text fieldId="name" :fieldLabel="__('app.name')"
                                        fieldName="name" fieldRequired="true"
                                        :fieldPlaceholder="__('placeholders.name')" :fieldValue="$channel->name">
                        </x-forms.text>
                    </div>

                    <div class="col-md-12 col-sm-12">
                        <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.description')"
                            fieldName="description" fieldId="description" :fieldValue="$channel->description">
                        </x-forms.textarea>
                    </div>
                </div>
            </div>
        </div>
    </x-form>
</div>
<div class="modal-footer mr-2">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-channel" icon="check">@lang('app.update')</x-forms.button-primary>
</div>

<script>

    $('#save-channel').click(function () {
        let channelId = '{{ $channel->id }}';
        let url = "{{ route('channel-messages.update', ':id') }}";
        url = url.replace(':id', channelId);

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

                setContent();

                $('.show-user-messages').removeClass('active');
                $('#user-no-' + response.receiver_id + ' a').addClass('active');
                let receiverId = $('#chatBox').data('chat-for-user');
                $('#user-no-' + receiverId + ' a').addClass('active');
            }
        })
    });

    function setContent() {
        @if (isset($client))
        let clientId = $('#client_id').val();
        var redirectUrl = "{{ route('messages.index') }}?clientId=" + clientId;
        window.location.href = redirectUrl;
        @endif

        document.getElementById('msgLeft').innerHTML = $('#user_list').val();
        document.getElementById('chatBox').innerHTML = $('#message_list').val();
        $('#sendMessageForm').removeClass('d-none');

        $(MODAL_LG).modal('hide');
        scrollChat();
    }

    init('#createConversationForm');
</script>
