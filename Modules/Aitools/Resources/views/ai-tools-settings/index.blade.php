@extends('layouts.app')

@push('styles')
    @include('sections.datatable_css')
    <style>
        .ai-tool-card {
            background: #fff;
            border: 1px solid #e0e6ed;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 24px;
            transition: all 0.3s ease;
        }

        .ai-tool-card:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .ai-tool-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e0e6ed;
        }

        .ai-tool-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .ai-tool-title h5 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
        }

        .model-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            /*background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);*/
            color: #070404;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.3px;
            box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
        }

        .ai-tool-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            color: #fff;
            font-size: 20px;
        }

        .tabs .nav-item.nav-link.active {
            border-bottom: 3px solid #667eea;
            color: #667eea;
            font-weight: 600;
        }

        .tabs .nav-item.nav-link {
            transition: all 0.2s ease;
        }

        .tabs .nav-item.nav-link:hover {
            border-bottom: 3px solid rgba(102, 126, 234, 0.5);
            color: #667eea;
        }

        .chat-test-toggle-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 16px;
        }

        .chat-test-toggle-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .chat-test-toggle-btn i {
            transition: transform 0.3s ease;
        }

        .chat-test-toggle-btn.active i {
            transform: rotate(180deg);
        }

        .chat-test-container {
            background: #fff;
            border: 1px solid #e0e6ed;
            border-radius: 8px;
            padding: 24px;
            margin-top: 24px;
            display: none;
            animation: slideDown 0.3s ease;
        }

        .chat-test-container.show {
            display: block;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .chat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e0e6ed;
        }

        .chat-header h6 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .chat-messages {
            height: 400px;
            overflow-y: auto;
            padding: 16px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 16px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .chat-message {
            display: flex;
            gap: 12px;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .chat-message.user {
            flex-direction: row-reverse;
        }

        .chat-message-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 16px;
        }

        .chat-message.user .chat-message-avatar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
        }

        .chat-message.assistant .chat-message-avatar {
            background: #e0e6ed;
            color: #667eea;
        }

        .chat-message-content {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 12px;
            word-wrap: break-word;
        }

        .chat-message.user .chat-message-content {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border-bottom-right-radius: 4px;
        }

        .chat-message.assistant .chat-message-content {
            background: #fff;
            color: #2c3e50;
            border: 1px solid #e0e6ed;
            border-bottom-left-radius: 4px;
        }

        .chat-input-container {
            display: flex;
            gap: 12px;
            align-items: flex-end;
        }

        .chat-input-wrapper {
            flex: 1;
        }

        .chat-input-wrapper textarea {
            height: 50px;
            min-height: 50px;
            max-height: 120px;
            resize: vertical;
            border: 1px solid #e0e6ed;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 14px;
            line-height: 1.5;
            box-sizing: border-box;
        }

        .chat-input-wrapper textarea:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .chat-send-btn {
            height: 50px;
            padding: 0 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            color: #fff;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }

        .chat-send-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .chat-send-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .chat-loading {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #667eea;
            padding: 12px 16px;
        }

        .chat-loading-dots {
            display: flex;
            gap: 4px;
        }

        .chat-loading-dots span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #667eea;
            animation: bounce 1.4s infinite ease-in-out both;
        }

        .chat-loading-dots span:nth-child(1) {
            animation-delay: -0.32s;
        }

        .chat-loading-dots span:nth-child(2) {
            animation-delay: -0.16s;
        }

        @keyframes bounce {
            0%, 80%, 100% {
                transform: scale(0);
            }
            40% {
                transform: scale(1);
            }
        }

        .empty-chat {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #94a3b8;
            text-align: center;
        }

        .empty-chat i {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .usage-stat-card {
            padding: 20px;
            border-radius: 8px;
            transition: transform 0.2s ease;
        }

        .usage-stat-card:hover {
            transform: translateY(-2px);
        }

        .usage-stat-card h3 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .usage-stat-card p {
            font-size: 14px;
            margin: 0;
        }
    </style>
@endpush

@section('content')

    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">

        <x-setting-sidebar :activeMenu="$activeSettingMenu" />

        <x-setting-card>
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <nav class="tabs px-4 border-bottom-grey">
                        <div class="nav" id="nav-tab" role="tablist">
                            <a class="nav-item nav-link f-15 {{ ($activeTab ?? 'settings') == 'settings' ? 'active' : '' }} chatgpt" 
                               href="{{ route('ai-tools-settings.index') }}" 
                               role="tab" 
                               aria-controls="nav-chatgpt" 
                               aria-selected="{{ ($activeTab ?? 'settings') == 'settings' ? 'true' : 'false' }}">
                                <i class="fa fa-robot mr-2"></i>@lang('aitools::app.chatgpt')
                            </a>
                            <a class="nav-item nav-link f-15 {{ ($activeTab ?? 'settings') == 'usage' ? 'active' : '' }} usage" 
                               href="{{ route('ai-tools-settings.index', ['tab' => 'usage']) }}" 
                               role="tab" 
                               aria-controls="nav-usage" 
                               aria-selected="{{ ($activeTab ?? 'settings') == 'usage' ? 'true' : 'false' }}">
                                <i class="fa fa-chart-line mr-2"></i>@lang('aitools::app.usageHistory')
                            </a>
                        </div>
                    </nav>
                </div>
            </x-slot>

            <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-20">
                @if(($activeTab ?? 'settings') == 'usage')
                    {{-- Usage Statistics Tab --}}
                    <div class="row">
                        <div class="col-lg-12 mb-4">
                            <h5 class="mb-4">@lang('aitools::app.usageStatistics')</h5>
                            
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="ai-tool-card usage-stat-card text-center">
                                        <h3 class="mb-2 text-info" id="stat-prompt-tokens">{{ number_format($totalPromptTokens ?? 0) }}</h3>
                                        <p class="mb-0 text-muted">@lang('aitools::app.promptTokens')</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="ai-tool-card usage-stat-card text-center">
                                        <h3 class="mb-2 text-warning" id="stat-completion-tokens">{{ number_format($totalCompletionTokens ?? 0) }}</h3>
                                        <p class="mb-0 text-muted">@lang('aitools::app.completionTokens')</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="ai-tool-card usage-stat-card text-center">
                                        <h3 class="mb-2 text-success" id="stat-total-requests">{{ number_format($totalRequests ?? 0) }}</h3>
                                        <p class="mb-0 text-muted">@lang('aitools::app.totalRequests')</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="ai-tool-card usage-stat-card text-center">
                                        <h3 class="mb-2 text-primary" id="stat-total-tokens">{{ number_format($totalTokens ?? 0) }}</h3>
                                        <p class="mb-0 text-muted">@lang('aitools::app.totalTokens')</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4 text-center">
                                <button type="button" id="refresh-usage-btn" class="btn btn-primary mr-2">
                                    <i class="fa fa-refresh mr-2"></i>
                                    <span>@lang('aitools::app.refreshUsageData')</span>
                                </button>
                                <button type="button" id="reset-usage-btn" class="btn btn-danger">
                                    <i class="fa fa-trash mr-2"></i>
                                    <span>@lang('aitools::app.resetUsageData')</span>
                                </button>
                                <small class="d-block text-muted mt-2">@lang('aitools::app.syncLatestUsageData')</small>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Settings Tab --}}
                    <div class="row">
                        <div class="col-lg-12 mb-4">
                            <x-alert type="info">
                                @lang('aitools::app.aiToolsDescription')
                            </x-alert>
                        </div>

                        <div class="col-lg-12">
                        <div class="ai-tool-card">
                            <div class="ai-tool-header">
                                <div class="ai-tool-title">
                                    <div class="ai-tool-icon">
                                        <i class="fa fa-robot"></i>
                                    </div>
                                    <div>
                                        <h5>@lang('aitools::app.chatgpt')</h5>
                                        <span class="model-badge" id="selected-model-badge">{{ $aiToolsSetting->model_name ?? 'gpt-3.5-turbo' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="ai-tool-content">
                                <div class="form-group">
                                    <x-forms.label fieldId="current_model" :fieldLabel="__('aitools::app.currentOpenAiModel')"></x-forms.label>
                                    <div class="form-control" style="background-color: #f8f9fa; cursor: not-allowed;">
                                        <strong>{{ $aiToolsSetting->model_name ?? 'gpt-3.5-turbo' }}</strong>
                                    </div>
                                    <small class="form-text text-muted mt-1">@lang('aitools::app.latestModelAutoFetched')</small>
                                </div>

                                <x-forms.text :fieldLabel="__('aitools::app.chatgptApiKey')"
                                              fieldPlaceholder="sk-..."
                                              fieldName="chatgpt_api_key" fieldId="chatgpt_api_key"
                                              :fieldValue="$aiToolsSetting->chatgpt_api_key ?? ''"/>

                                <small class="form-text text-muted my-2 d-block">
                                    @lang('aitools::app.chatgptApiKeyHelp')
                                </small>
                            </div>
                        </div>

                        <button type="button" class="chat-test-toggle-btn" id="chat-test-toggle-btn">
                            <i class="fa fa-comments"></i>
                            <span>@lang('aitools::app.testChatInterface')</span>
                            <i class="fa fa-chevron-down ml-2"></i>
                        </button>

                        <div class="chat-test-container" id="chat-test-container">
                            <div class="chat-header">
                                <h6>
                                    <i class="fa fa-comments"></i>
                                    @lang('aitools::app.testChatInterface')
                                </h6>
                            </div>

                            <div class="chat-messages" id="chat-messages">
                                <div class="empty-chat">
                                    <i class="fa fa-comment-dots"></i>
                                    <p>@lang('aitools::app.startConversation')</p>
                                </div>
                            </div>

                            <div class="chat-input-container">
                                <div class="chat-input-wrapper">
                                    <textarea 
                                        id="chat-prompt-input" 
                                        class="form-control" 
                                        placeholder="@lang('aitools::app.askAnything')"
                                        rows="2"></textarea>
                                </div>
                                <button type="button" id="chat-send-btn" class="chat-send-btn">
                                    <i class="fa fa-paper-plane"></i>
                                    <span>@lang('aitools::app.send')</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <x-slot name="action">
                @if(($activeTab ?? 'settings') == 'settings')
                <!-- Buttons Start -->
                <div class="w-100 border-top-grey">
                    <x-setting-form-actions>
                        <x-forms.button-primary id="save-ai-tools-form" class="mr-3" icon="check">@lang('app.save')
                        </x-forms.button-primary>
                    </x-setting-form-actions>
                </div>
                <!-- Buttons End -->
                @endif
            </x-slot>

        </x-setting-card>

    </div>
    <!-- SETTINGS END -->
@endsection

@push('scripts')
    <script>
        $('body').on('click', '#save-ai-tools-form', function () {
            const url = "{{ route('ai-tools-settings.update', [$aiToolsSetting->id ?? 0]) }}";

            $.easyAjax({
                url: url,
                container: '#editSettings',
                type: "POST",
                disableButton: true,
                buttonSelector: "#save-ai-tools-form",
                data: $('#editSettings').serialize(),
            })
        });

        // Chat toggle functionality
        const chatTestToggleBtn = $('#chat-test-toggle-btn');
        const chatTestContainer = $('#chat-test-container');

        chatTestToggleBtn.on('click', function() {
            chatTestContainer.toggleClass('show');
            chatTestToggleBtn.toggleClass('active');
        });

        // Chat functionality
        const chatMessages = $('#chat-messages');
        const chatInput = $('#chat-prompt-input');
        const chatSendBtn = $('#chat-send-btn');

        function addMessage(content, isUser = true) {
            // Remove empty chat message if exists
            chatMessages.find('.empty-chat').remove();

            const messageClass = isUser ? 'user' : 'assistant';
            const avatarIcon = isUser ? '<i class="fa fa-user"></i>' : '<i class="fa fa-robot"></i>';
            
            const messageHtml = `
                <div class="chat-message ${messageClass}">
                    <div class="chat-message-avatar">
                        ${avatarIcon}
                    </div>
                    <div class="chat-message-content">
                        ${content.replace(/\n/g, '<br>')}
                    </div>
                </div>
            `;
            
            chatMessages.append(messageHtml);
            chatMessages.scrollTop(chatMessages[0].scrollHeight);
        }

        function showLoading() {
            const loadingHtml = `
                <div class="chat-message assistant" id="loading-message">
                    <div class="chat-message-avatar">
                        <i class="fa fa-robot"></i>
                    </div>
                    <div class="chat-loading">
                        <div class="chat-loading-dots">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                        <span>{{ __('aitools::app.thinking') }}</span>
                    </div>
                </div>
            `;
            chatMessages.append(loadingHtml);
            chatMessages.scrollTop(chatMessages[0].scrollHeight);
        }

        function removeLoading() {
            $('#loading-message').remove();
        }

        function sendMessage() {
            const prompt = chatInput.val().trim();
            
            if (!prompt) {
                return;
            }

            // Add user message
            addMessage(prompt, true);
            
            // Clear input
            chatInput.val('');
            chatInput.focus();
            
            // Disable send button
            chatSendBtn.prop('disabled', true);
            
            // Show loading
            showLoading();

            // Send to backend
            $.easyAjax({
                url: "{{ route('ai-tools-settings.test-chat') }}",
                type: "POST",
                data: {
                    prompt: prompt,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    removeLoading();
                    if (response.status === 'success' && response.response) {
                        addMessage(response.response, false);
                    } else if (response.status === 'fail' && response.message) {
                        addMessage(response.message, false);
                    } else {
                        addMessage('{{ __('aitools::app.errorOccurredGeneric') }}', false);
                    }
                },
                error: function(xhr) {
                    removeLoading();
                    let errorMessage = '{{ __('aitools::app.errorOccurredRetry') }}';
                    
                    // Try different response formats
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.data && xhr.responseJSON.data.message) {
                            errorMessage = xhr.responseJSON.data.message;
                        } else if (xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                    } else if (xhr.responseText) {
                        try {
                            const parsed = JSON.parse(xhr.responseText);
                            if (parsed.message) {
                                errorMessage = parsed.message;
                            }
                        } catch (e) {
                            // If parsing fails, use default message
                        }
                    }
                    
                    addMessage(errorMessage, false);
                },
                complete: function() {
                    chatSendBtn.prop('disabled', false);
                }
            });
        }

        // Send button click
        chatSendBtn.on('click', function() {
            sendMessage();
        });

        // Enter key to send (Shift+Enter for new line)
        chatInput.on('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Refresh Usage Data
        $('body').on('click', '#refresh-usage-btn', function() {
            const btn = $(this);
            const originalHtml = btn.html();
            
            btn.prop('disabled', true);
            btn.html('<i class="fa fa-spinner fa-spin mr-2"></i><span>{{ __('aitools::app.refreshing') }}</span>');

            $.easyAjax({
                url: "{{ route('ai-tools-settings.refresh-usage') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.status === 'success') {
                        // Update statistics immediately
                        if (response.data) {
                            const data = response.data;
                            $('#stat-total-tokens').text(number_format(data.total_tokens || 0));
                            $('#stat-total-requests').text(number_format(data.total_requests || 0));
                            $('#stat-prompt-tokens').text(number_format(data.total_prompt_tokens || 0));
                            $('#stat-completion-tokens').text(number_format(data.total_completion_tokens || 0));
                        }
                        
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __('aitools::app.success') }}',
                            text: response.message || '{{ __('aitools::app.usageDataRefreshedSuccessfully') }}',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        // Reload the page to show updated history
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '{{ __('aitools::app.error') }}',
                            text: response.message || '{{ __('aitools::app.failedToRefreshUsageData') }}',
                            confirmButtonText: '{{ __('app.ok') }}'
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = '{{ __('aitools::app.errorRefreshingUsageData') }}';
                    
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.data && xhr.responseJSON.data.message) {
                            errorMessage = xhr.responseJSON.data.message;
                        }
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __('aitools::app.error') }}',
                        text: errorMessage,
                        confirmButtonText: '{{ __('app.ok') }}'
                    });
                },
                complete: function() {
                    btn.prop('disabled', false);
                    btn.html(originalHtml);
                }
            });
        });

        // Reset Usage Data
        $('body').on('click', '#reset-usage-btn', function() {
            const btn = $(this);
            
            Swal.fire({
                title: '{{ __('aitools::app.confirmReset') }}',
                text: '{{ __('aitools::app.confirmResetUsageData') }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '{{ __('aitools::app.yesReset') }}',
                cancelButtonText: '{{ __('app.cancel') }}',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
            }).then((result) => {
                if (result.isConfirmed) {
                    const originalHtml = btn.html();
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i><span>{{ __('aitools::app.resetting') }}</span>');
                    
                    $.ajax({
                        url: "{{ route('ai-tools-settings.reset-usage') }}",
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                // Reset statistics to zero
                                if (response.data) {
                                    const data = response.data;
                                    $('#stat-total-tokens').text(number_format(data.total_tokens || 0));
                                    $('#stat-total-requests').text(number_format(data.total_requests || 0));
                                    $('#stat-prompt-tokens').text(number_format(data.total_prompt_tokens || 0));
                                    $('#stat-completion-tokens').text(number_format(data.total_completion_tokens || 0));
                                }
                                
                                // Show success message
                                Swal.fire({
                                    icon: 'success',
                                    text: response.message || '{{ __('aitools::app.usageDataResetSuccessfully') }}',
                                    toast: true,
                                    position: "top-end",
                                    timer: 3000,
                                    timerProgressBar: true,
                                    showConfirmButton: false,
                                });

                                window.location.reload();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    text: response.message || '{{ __('aitools::app.failedToResetUsageData') }}',
                                    showConfirmButton: true,
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = '{{ __('aitools::app.errorResettingUsageData') }}';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                text: errorMessage,
                                showConfirmButton: true,
                            });
                        },
                        complete: function() {
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                }
            });
        });

        function number_format(number) {
            return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

    </script>
@endpush

