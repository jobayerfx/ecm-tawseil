@extends('layouts.app')

<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">

@push('styles')
    <style>
        .message-action {
            visibility: hidden;
        }

        .message_wrapper .msg-content-right .chat-box .card:hover .message-action {
            visibility: visible;
        }
        #submitTexts {
            border-top: 1px solid;
        }
        .ql-editor {
            padding-left: 0px !important;
        }

        .ql-editor-disabled {
        border-radius: 6px;
        background-color: rgba(124, 0, 0, 0.2);
        transition-duration: 0.5s;
        }
        .ql-toolbar{
            display: none !important;
        }
        .ql-editor.ql-blank::before{
            font-size: 14px !important;
            font-style: inherit;
            color: #6c757d;
            left: 17px !important;
        }
        #msgContentRight .card-body .card-text span > p {
            margin-bottom: 0rem !important;
        }

        .ajax-tab {
            padding: 10px 15px 5px 15px !important;
        }


        .card-img {
            border-radius: 50%;
            height: 40px;
            width: 40px;
        }

        .card-img img {
            height: 40px;
            width: 40px;
        }

        .user_list_box .card-body, .chat-box .card-body {
            padding: 0.75rem;
        }

        .user_list_box .card-img {
            margin: 0.75rem;
        }

    </style>
@endpush

@section('content')

    <!-- MESSAGE START -->
    <div class="message_wrapper bg-white border-top-0">
        <!-- MESSAGE CONTENT START -->
        <div class="w-100 d-lg-flex d-md-flex d-block">
            <!-- MESSAGE CONTENT LEFT START -->
            <div class="msg-content-left border-top-0 border-bottom-0">
                <div class="msg-header d-flex align-items-center">
                    <div class="msg-header-left d-flex justify-content-between">
                        <div class="flex-lg-grow-1">
                            <form class="mb-0">
                                <div class="input-group rounded py-1">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text border-0">
                                            <i class="fa fa-search f-12 text-lightest"></i>
                                        </span>
                                    </div>
                                    <input type="text" id="user-search" class="form-control border-0 f-14"
                                        placeholder="@lang('groupmessage::app.searchContact')">
                                </div>
                            </form>
                        </div>

                        <div class="pl-2 d-lg-none">
                            <x-forms.button-primary class="btn btn-sm actionBtn private-btn client-btn" data-type="{{$activeTab}}" id="new-chat-mbl" icon="plus">@lang('app.new')
                            </x-forms.button-primary>
                            <x-forms.button-primary class="actionBtn d-none group-btn" id="new-group-mbl" icon="plus">@lang('groupmessage::app.addGroup')
                            </x-forms.button-primary>
                            <x-forms.button-primary class="actionBtn d-none channel-btn" id="new-channel-mbl" icon="plus">@lang('groupmessage::app.addChannel')
                            </x-forms.button-primary>
                        </div>
                    </div>
                </div>


                @if (!in_array('client', user_roles()))
                    <x-tab-section class="chat-tabs">
                        <div class="d-flex justify-content-around">

                            <x-tab-item class="ajax-tab private" data-type="private" :active="request('view') === 'private' || !request('view')"
                            :link="route('group-messaging.index').'?view=private'">@lang('groupmessage::app.private')</x-tab-item>

                            @if ((in_array('employee', user_roles()) && !in_array('admin', user_roles()) && $messageSetting->allow_client_employee == 'yes') || (in_array('admin', user_roles()) && $messageSetting->allow_client_admin == 'yes'))
                                <x-tab-item class="ajax-tab client" data-type="client"  :active="request('view') === 'client'"
                            :link="route('group-messaging.index').'?view=client'" disabled>@lang('groupmessage::app.client')</x-tab-item>
                            @endif

                            <x-tab-item class="ajax-tab group"  data-type="group" :active="request('view') === 'group'"
                            :link="route('group-messaging.index').'?view=group'">@lang('groupmessage::app.group')</x-tab-item>

                            <x-tab-item class="ajax-tab channel"  data-type="channel" :active="request('view') === 'channel'"
                            :link="route('group-messaging.index').'?view=channel'">@lang('groupmessage::app.channel')</x-tab-item>
                        </div>
                    </x-tab-section>
                @endif

                <!-- This msgLeft id is for scroll plugin -->
                <div data-menu-vertical="1" data-menu-scroll="1" data-menu-dropdown-timeout="500" id="msgLeft"
                     class="nav nav-tabs border-bottom-0" role="tablist">
                     @if ($activeTab == 'channel')
                        @include('groupmessage::messages.channels.channel_lists')
                    @elseif ($activeTab == 'group')
                        @include('groupmessage::messages.groups.group_lists')
                    @else
                        @include('groupmessage::messages.private.user_list')
                    @endif
                </div>

            </div>
            <!-- MESSAGE CONTENT LEFT END -->

            <!-- MESSAGE CONTENT RIGHT START -->
            <div class="msg-content-right" id="msgContentRight">
                <div class="msg-header d-none d-lg-flex align-items-center">
                    <div class="msg-header-right w-100 d-flex justify-content-between align-items-center">
                        <div class="msg-sender-name d-flex">
                            <p class="f-15 text-capitailize text-dark mb-0 f-w-500 message-user mr-2"></p>
                            <div id="group-members-container"></div>
                        </div>

                        <x-forms.button-primary class="actionBtn private-btn client-btn" data-type="{{$activeTab}}" id="new-chat" icon="plus">@lang('modules.messages.startConversation')
                        </x-forms.button-primary>

                        <x-forms.button-primary class="actionBtn d-none group-btn" id="new-group" icon="plus">@lang('groupmessage::app.addGroup')
                        </x-forms.button-primary>

                        <x-forms.button-primary class="actionBtn d-none channel-btn" id="new-channel" icon="plus">@lang('groupmessage::app.addChannel')
                        </x-forms.button-primary>
                    </div>
                </div>

                <!-- MOBILE MESSAGE SENDER NAME START -->
                <div
                    class="msg-sender-name d-flex d-lg-none mbl-sender-name align-items-center justify-content-between">
                    <p class="f-15 text-capitailize text-dark mb-0 f-w-500 message-user"></p>
                    <i class="fa fa-long-arrow-alt-right f-16 text-dark" onclick="closeMessageTab()"></i>
                </div>
                <!-- MOBILE MESSAGE SENDER NAME END -->

                <!-- CHAT BOX START -->
                <div class="chat-box">
                    <!-- This chatBox id is for scroll plugin -->
                    <div data-menu-vertical="1" data-menu-scroll="1" data-menu-dropdown-timeout="500" id="chatBox"
                         class="tab-content" data-chat-for-user="">

                        <div id="tab1" class="tabcontent" style="display: block;">
                            <x-cards.no-record icon="comment-alt" :message="__('messages.selectConversation')"/>
                        </div><!-- TAB END -->

                    </div>

                </div>
                <!-- CHAT BOX END -->

                <!-- SEND MESSAGE START -->
                <x-form id="sendMessageForm" class="d-none mb-0">
                    <input type="hidden" name="user_id" id="current_user_id">
                    <input type="hidden" name="group_id" id="current_group_id">
                    <input type="hidden" name="channel_id" id="current_channel_id">
                    <div class="row">
                        <div class="w-100 col-md-12">
                            <div class="form-group">
                             <br>
                             <input type="hidden" name="types" value="chat"/>
                             <div id="submitTexts" class="form-control rounded-0 f-14 p-3 border-left-0 border-right-0 border-bottom-0" data-text="@lang('messages.enterText')"></div>
                            <textarea name="message" id="message-text" class="d-none" ></textarea>
                            </div>
                        </div>
                       <input type = "hidden" name = "mention_user_id" id = "mentionUserId" class ="mention_user_ids">
                       <div class="col-md-12">
                           <div class="w-100 justify-content-start attach-send bg-white">
                               <a class="f-15 f-w-500" href="javascript:;" id="add-file"><i
                                       class="fa fa-paperclip font-weight-bold mr-1"></i>@lang('modules.projects.uploadFile')
                               </a>
                           </div>
                       </div>
                        <div class="col-md-12 d-none file-container">
                           <x-forms.file-multiple class="mr-0 mr-lg-2 mr-md-2 ml-3"
                                                  :fieldLabel="__('app.menu.addFile')" fieldName="file"
                                                  fieldId="file-upload-dropzone"/>
                           <input type="hidden" name="message_id" id="messageId">
                           <input type="hidden" name="type" id="message">

                           {{-- These inputs fields are used for file attchment --}}
                           <input type="hidden" name="user_list" id="user_list">
                           <input type="hidden" name="message_list" id="message_list">
                           <input type="hidden" name="receiver_id" id="receiver_id">
                        </div>


                    </div>
                    <div class="col-md-12 border-top-grey p-0">
                        <div class="w-100 justify-content-start attach-send bg-white">
                            <x-forms.button-primary id="sendMessage" class="mr-1" icon="location-arrow">
                                @lang('modules.messages.send')
                            </x-forms.button-primary>
                        </div>
                    </div>
                </x-form>
                <!-- SEND MESSAGE END -->

            </div>
            <!-- MESSAGE CONTENT RIGHT START -->
        </div>
        <!-- MESSAGE CONTENT END -->
    </div>
    <!-- MESSAGE END -->
@endsection

@push('scripts')

    <script>
        $(document).ready(function() {
            getUserMention();
            @if (isset($userData))
                var atValues = @json($userData);
                quillMention(atValues, '#submitTexts');
            @else
                quillMention(null, '#submitTexts');
            @endif
        });

        tabType = null;

        $('.ajax-tab').removeClass('active');
        const activeTab = "{{ $activeTab }}";
        $('.' + activeTab).addClass('active');

        $(".ajax-tab").click(function (event) {
            event.preventDefault();

            $('#current_user_id').val('');
            $('#current_group_id').val('');
            $('#current_channel_id').val('');

            $('.chat-tabs .ajax-tab').removeClass('active');
            $(this).addClass('active');

            var type = $(this).data('type');
            let requestUrl = this.href;

            $.easyAjax({
                url: requestUrl,
                blockUI: true,
                container: "#msgLeft",
                historyPush: ($(RIGHT_MODAL).hasClass('in') ? false : true),
                data: {
                    'json': true
                },
                success: function (response) {

                    if (response.status == "success") {
                        $('#msgLeft').html(response.html);
                        resetChatBoxView();
                        showBtn(type);
                    }
                }
            });
        });

        function showBtn(activeTab) {
            $('.actionBtn').addClass('d-none');
            $('.' + activeTab + '-btn').removeClass('d-none');

            if (activeTab == 'channel') {
                tabType = 'channel';
            }

            if (activeTab == 'group') {
                tabType = 'group';
            }

            if (activeTab == 'private') {
                tabType = 'private';
            }

            if (activeTab == 'client') {
                tabType = 'client';
            }
        }

        showBtn(activeTab);

        var totalUnreadMessagesCount = parseInt("{{ $unreadMessagesCount }}");

        @if (session('message_user_id'))
            let message_user_id = {{ session('message_user_id') }};

            setTimeout(() => {
                $('a[data-user-id="' + message_user_id + '"]').click();
            }, 500);
        @endif

        // change query parameter from url
        // history.replaceState(null, null, "{{route('group-messaging.index')}}");

        /* Upload images */
        Dropzone.autoDiscover = false;
        //Dropzone class
        taskDropzone = new Dropzone("#file-upload-dropzone", {
            dictDefaultMessage: "{{ __('app.dragDrop') }}",
            url: "{{ route('message-file.store') }}",
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            paramName: "file",
            maxFilesize: DROPZONE_MAX_FILESIZE,
            maxFiles: DROPZONE_MAX_FILES,
            autoProcessQueue: false,
            uploadMultiple: true,
            addRemoveLinks: true,
            parallelUploads: DROPZONE_MAX_FILES,
            acceptedFiles: DROPZONE_FILE_ALLOW,
            init: function () {
                taskDropzone = this;
                this.on("success", function (file, response) {
                    $('#chatBox').html(response.message_list);
                    showContent();
                    $.easyUnblockUI();
                    taskDropzone.removeAllFiles(true);
                })
            }
        });
        taskDropzone.on('sending', function (file, xhr, formData) {
            var ids = $('#messageId').val();
            formData.append('message_id', ids);
            formData.append('type', 'message');
            formData.append('receiver_id', $('#receiver_id').val());
            $.easyBlockUI();
        });
        taskDropzone.on('uploadprogress', function () {
            $.easyBlockUI();
        });
        taskDropzone.on('removedfile', function () {
            var grp = $('div#file-upload-dropzone').closest(".form-group");
            var label = $('div#file-upload-box').siblings("label");
            $(grp).removeClass("has-error");
            $(label).removeClass("is-invalid");
        });
        taskDropzone.on('error', function (file, message) {
            taskDropzone.removeFile(file);
            var grp = $('div#file-upload-dropzone').closest(".form-group");
            var label = $('div#file-upload-box').siblings("label");
            $(grp).find(".help-block").remove();
            var helpBlockContainer = $(grp);

            if (helpBlockContainer.length == 0) {
                helpBlockContainer = $(grp);
            }

            helpBlockContainer.append('<div class="help-block invalid-feedback">' + message + '</div>');
            $(grp).addClass("has-error");
            $(label).addClass("is-invalid");

        });

        // Submitting message
        $(document).on('click', '#sendMessage', function (e) {
            e.preventDefault();

            // Get the note content and set it to the message text input
            var note = $('#submitTexts').children().first().html();
            $('#message-text').val(note);

            // Collect mention user IDs
            var mention_user_id = $('#submitTexts span[data-id]').map(function() {
                return $(this).attr('data-id');
            }).get();
            $('#mentionUserId').val(mention_user_id.join(','));

            // Determine the correct URL based on tabType
            var url;

            if (tabType === 'channel') {
                url = "{{ route('channel-messages.store-channel-message') }}";
            }
            else if (tabType === 'group') {
                url = "{{ route('group-messages.store-group-message') }}";
            }
            else {
                url = "{{ route('group-messaging.store') }}?active_tab=" + tabType;
            }

            $.easyAjax({
                url: url,
                container: '#sendMessageForm',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#sendMessage",
                data: $('#sendMessageForm').serialize(),
                success: function (response) {
                    if (response.status === 'fail') {
                        $('#message-text').html(`<div class="alert alert-danger">${response.message}</div>`);
                        return;
                    }

                    // Update hidden fields with response data
                    $('#user_list').val(response.user_list);
                    $('#message_list').val(response.message_list);
                    $('#receiver_id').val(response.receiver_id);
                    $('#current_group_id').val(response.receiver_id);
                    $('#current_channel_id').val(response.receiver_id);

                    // Reload lists based on tabType
                    if (tabType === 'channel') {
                        fetchChannelList();
                    }
                    else if (tabType === 'group') {
                        fetchGroupList();
                    }
                    else {
                        fetchUserList();
                    }

                    // Handle file uploads if any
                    if (taskDropzone.getQueuedFiles().length > 0) {
                        $('#messageId').val(response.message_id);
                        taskDropzone.processQueue();
                    }
                    else {
                        showContent();
                    }
                }
            });

            return false;
        });

        function showContent() {
            $('.ql-editor p').html('');
            $('#sendMessageForm').removeClass('d-none');
            scrollChat();
            $('#msgContentRight').addClass('d-block');
            $('.file-container').addClass('d-none');
            taskDropzone.removeAllFiles(true);

            fetchUserMessages();
        }

        // Start new conversation
        $('#new-chat, #new-chat-mbl').click(function () {
            var url = "{{ route('group-messaging.create') }}?type=" + tabType;
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        // Channel view, add, edit, delete
        $('#new-channel, #new-channel-mbl').click(function () {
            const url = "{{ route('channel-messages.create') }}";

            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $(document).on('click', '.view-channel', function (e) {
            e.stopPropagation();

            const channelId = $(this).data('channel-id');
            let url = "{{ route('channel-messages.show', ':id') }}";
            url = url.replace(':id', channelId);

            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $(document).on('click', '.edit-channel', function (e) {
            e.stopPropagation();

            const channelId = $(this).data('channel-id');
            let url = "{{ route('channel-messages.edit', ':id') }}";
            url = url.replace(':id', channelId);

            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $(document).on('click', '.delete-channel', function (e) {

            e.stopPropagation();
            let id = $(this).data('channel-id');

            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')",
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
                    var url = "{{ route('channel-messages.destroy', ':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {
                            '_token': token,
                            '_method': 'DELETE'
                        },
                        success: function (response) {
                            if (response.status == "success") {
                                $('#message-' + id).remove();

                                // Reload the channel-list
                                fetchChannelList();

                                $('#msgLeft').html(response.chat_details);
                                resetChatBoxView();
                            }
                        }
                    });
                }
            });
        });

        // Group view, add, edit, delete
        $('#new-group, #new-group-mbl').click(function () {
            const url = "{{ route('group-messages.create') }}";

            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $(document).on('click', '.view-group', function (e) {
            e.stopPropagation();

            const groupId = $(this).data('group-id');
            let url = "{{ route('group-messages.show', ':id') }}";
            url = url.replace(':id', groupId);

            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $(document).on('click', '.edit-group', function (e) {
            e.stopPropagation();

            const groupId = $(this).data('group-id');
            let url = "{{ route('group-messages.edit', ':id') }}";
            url = url.replace(':id', groupId);

            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $(document).on('click', '.delete-group', function (e) {

            e.stopPropagation();
            let id = $(this).data('group-id');

            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')",
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
                    var url = "{{ route('group-messages.destroy', ':id') }}";
                    url = url.replace(':id', id);
                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {
                            '_token': token,
                            '_method': 'DELETE'
                        },
                        success: function (response) {
                            if (response.status == "success") {
                                $('#message-' + id).remove();
                                // Reload left group-list
                                fetchGroupList();
                                $('#msgLeft').html(response.chat_details);
                                resetChatBoxView();
                            }
                        }
                    });
                }
            });
        });

        // Search user/group/channel
        $('#user-search').keyup(function () {
            var url = "{{ route('group-messaging.index') }}";
            var term = $(this).val();

            $.easyAjax({
                url: url,
                blockUI: true,
                container: "#msgLeft",
                data: {
                    term: term,
                    type: tabType,
                },
                success: function (response) {
                    if (response.status == "success") {
                        $('#msgLeft').html(response.html);
                        $('#current_user_id').val('');
                        $('#current_group_id').val('');
                        $('#current_channel_id').val('');
                        $('#chatBox').html('');

                        resetChatBoxView();
                    }
                }
            });
        });

        $('body').on('click', '#add-file', function () {
            $('.file-container').toggleClass('d-none');
            window.scrollBy(0, 200);
        });

        // Fetch message details of a particular user/group/channel
        $(document).on('click', '.show-user-messages', function (e) {
            e.preventDefault();

            var $this = $(this);
            var type = $this.data('type');
            var id = $this.data('user-id');
            var userName = $this.data('name');
            var isUnreadMessage = $this.hasClass('unread-message');
            var unreadMessageCount = $this.data('unread-message-count');

            if (isUnreadMessage) {
                $this.find('.card-text').removeClass('text-dark font-weight-bold');
                $this.find('.unread-count').remove();
            }

            // Update UI
            $('.message-user').html(userName);
            $('#current_user_id').val(id);
            $('.show-user-messages').removeClass('active');
            $this.addClass('active');
            $('#group-members-container').html('');

            // Determine the URL and tabType
            var url;

            if (type === 'channel') {
                var channelId = $this.data('channel-id');
                $('#current_channel_id').val(channelId);
                url = "{{ route('channel-messages.show-channel-chat', ':id') }}".replace(':id', channelId);
                tabType = 'channel';
            }
            else if (type === 'group') {
                var groupId = $this.data('group-id');
                $('#current_group_id').val(groupId);
                url = "{{ route('group-messages.show-group-chat', ':id') }}".replace(':id', groupId);
                tabType = 'group';
            }
            else {
                url = "{{ route('group-messaging.show', ':id') }}".replace(':id', id);
            }

            $.easyAjax({
                url: url,
                blockUI: true,
                container: "#chatBox",
                data: {'unreadMessageCount': unreadMessageCount},
                success: function (response) {
                    if (response.status == "success") {

                        $('#chatBox').html(response.html);
                        $('#user-no-' + response.id + '> a').attr("data-unread-message-count", 0);

                        // Set data value to chatBox to set active class later
                        $('#chatBox').attr("data-chat-for-user", id);

                        $('#sendMessageForm').removeClass('d-none');
                        $('#msgContentRight').addClass('d-block');

                        if (totalUnreadMessagesCount > 0 && isUnreadMessage && response.unreadMessages == 0) {
                            var remainingUnreadMessages = parseInt(totalUnreadMessagesCount) - parseInt(unreadMessageCount);
                            if (remainingUnreadMessages > 0) {
                                $(body).find('.message-menu .menu-item-count').html(remainingUnreadMessages);
                            } else {
                                $(body).find('.message-menu .menu-item-count').remove();
                            }

                            totalUnreadMessagesCount = remainingUnreadMessages;
                        }

                        if (type === 'group') {
                            fetchGroupMembers(response.id);
                        }

                        scrollChat();
                    }
                }
            });
        });

        $('body').on('keypress', '#submitTexts', function (e) {
            var key = e.which;
            if (key == 13 && !e.shiftKey) // the enter key code
            {
                e.preventDefault();
                $('#sendMessage').click();
                return false;
            }
        });

        function scrollChat(params) {
            $('#chatBox').stop().animate({
                scrollTop: $("#chatBox")[0].scrollHeight
            }, 800);
        }

        function fetchGroupMembers(id) {
            let url = "{{ route('group-messages.fetch-group-members', ':id') }}".replace(':id', id);
            $('#group-members-container').html('');

            console.log(url);

            if (id) {
                $.easyAjax({
                    url: url,
                    type: "GET",
                    success: function (response) {
                        if (response.members) {
                            $('#group-members-container').html(response.members);
                        }
                    }
                });
            }
        }

        // Delete a message of private/client/group/channel
        $('body').on('click', '.delete-message', function () {
            var id = $(this).data('row-id');

            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')",
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

                    var url = "{{ route('group-messaging.destroy', ':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {
                            '_token': token,
                            'type': tabType,
                            '_method': 'DELETE'
                        },
                        success: function (response) {
                            if (response.status == "success") {
                                $('#message-' + id).remove();

                                // Reload left user-list
                                if(tabType == 'channel'){
                                    fetchChannelList();
                                }
                                else if(tabType == 'group'){
                                    fetchGroupList();
                                }
                                else{
                                    fetchUserList();
                                }

                                if (response.chat_details.length == 0) {
                                    resetChatBoxView();
                                }
                            }
                        }
                    });
                }
            });
        });

        function resetChatBoxView() {
            $('#chatBox').html(`
            <div id="tab1" class="tabcontent" style="display: block;">
                <x-cards.no-record icon="comment-alt" :message="__('messages.selectConversation')" />
            </div>
            `);

            $('#sendMessageForm').addClass('d-none');
            $('.message-user').html('');
            $('#group-members-container').html('');
        }

        function fetchUserList() {
            var url = "{{ route('group-messaging.fetch-user-list') }}?type="+tabType;

            $.easyAjax({
                url: url,
                type: "GET",
                success: function (response) {
                    $('#msgLeft').html(response.user_list);
                    $('.show-user-messages').removeClass('active');

                    let receiverId = $('#receiver_id').val();

                    setTimeout(function() {
                        $('#user-no-'+ receiverId +' a').addClass('active');
                    }, 100);
                }
            });
        }

        function fetchGroupList() {
            let url = "{{ route('group-messages.fetch-groups-list') }}";

            $.easyAjax({
                url: url,
                type: "GET",
                success: function (response) {
                    $('#msgLeft').html(response.user_list);

                    $('.show-user-messages').removeClass('active');
                    let receiverId = $('#current_group_id').val();
                    $('#user-no-' + receiverId + ' a').addClass('active');
                }
            });
        }

        function fetchChannelList() {
            var url = "{{ route('channel-messages.fetch-channel-list') }}";
            $.easyAjax({
                url: url,
                type: "GET",
                success: function (response) {
                    $('#msgLeft').html(response.user_list);

                    $('.show-user-messages').removeClass('active');
                    let receiverId = $('#chatBox').data('chat-for-user');
                    $('#user-no-' + receiverId + ' a').addClass('active');
                }
            });
        }

        function fetchUserMessages(scrollChatBox = true) {
            let url = '';
            let entityId = '';

            switch (tabType) {
                case 'channel':
                    entityId = $('#current_channel_id').val();
                    url = "{{ route('channel-messages.fetch-channel-messages', ':id') }}";
                    break;
                case 'group':
                    entityId = $('#current_group_id').val();
                    url = "{{ route('group-messages.fetch-group-messages', ':id') }}";
                    break;
                default:
                    entityId = $('#current_user_id').val();
                    url = "{{ route('group-messaging.fetch-user-messages', ':id') }}";
                    break;
            }

            if (!entityId) {
                return false;
            }

            url = url.replace(':id', entityId);

            $.easyAjax({
                url: url,
                container: '#sendMessageForm',
                type: "POST",
                data: {
                    '_token': "{{ csrf_token() }}",
                },
                success: function (response) {
                    $('#chatBox').html(response.message_list);
                    if (scrollChatBox) {
                        scrollChat();
                    }
                    $('#msgContentRight').addClass('d-block');
                }
            });
        }

        function getUserMention(){
            $('.user_list_box').each(function(i, obj) {
                var content = $(obj).find('.message-mention').html();
                var name = $(obj).find('.message-mention p a').data('name');
                var replacement = '<div class="card-text f-11 text-lightest d-flex justify-content-between message-mention">@' + name + '</div>';
                if(content !== undefined && replacement !== undefined && name !== undefined){
                    $(obj).find('.message-mention').replaceWith(replacement);
                }
            });
        }

        @if (isset($client))
        let clientId = '{{ $client->id }}';
        $("a[data-user-id='" + clientId + "']").click();
        @endif

        if ((pusher_setting.status === 1 && pusher_setting.messages === 1) || (pusher_setting.status == "1" && pusher_setting.messages == "1")) {
            var channel = pusher.subscribe('messages-channel');
            channel.bind('messages.received', function (data) {
                fetchUserMessages();

                if (message_setting.send_sound_notification == 1) {
                    newMessageNotificationPlay();
                }
            });

            $('#submitTexts').on('keydown', function () {
                var currentUserId = $('#current_user_id').val();
                let channel2 = Echo.private('chat');
                setTimeout(() => {
                    channel2.whisper('typing', {
                        from: "{{ user()->id }}",
                        to: currentUserId,
                        typing: true
                    })
                }, 300)
            });

            Echo.private('chat').listenForWhisper('typing', (e) => {
                var currentUserId = $('#current_user_id').val();

                if (e.to == Laravel.user.id && e.from == currentUserId) {
                    e.typing ? $('#chatBox').find('.typing').removeClass('invisible').addClass('visible') : $('#chatBox').find('.typing').removeClass('visible').addClass('invisible')
                    // remove is typing indicator
                    setTimeout(function () {
                        e.typing = false;
                        $('#chatBox').find('.typing').removeClass('visible').addClass('invisible');
                    }, 1500);
                }
            });
        } else {
            window.setInterval(function () {
                fetchUserMessages(false)
            }, 10000); // Fetch messages every 10 seconds
        }

        // Delete file
        $('body').on('click', '.delete-file', function () {
            var id = $(this).data('row-id');
            var messageFile = $(this);
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')",
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
                    var url = "{{ route('message-file.destroy', ':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {
                            '_token': token,
                            '_method': 'DELETE'
                        },
                        success: function (response) {
                            if (response.status == "success") {
                                messageFile.closest('.card').remove();
                            }
                        }
                    });
                }
            });
        });
    </script>
@endpush
