<style>
    /* Floating Chat Widget */
    .floating-chat-widget {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
    }

    .floating-chat-btn {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        /* background: #000; */
        border: none;
        color: #fff;
        font-size: 24px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .floating-chat-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
    }

    .floating-chat-window {
        position: fixed;
        bottom: 90px;
        right: 20px;
        width: 400px;
        height: 600px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        display: none;
        flex-direction: column;
        z-index: 1001;
        overflow: hidden;
        animation: slideUp 0.3s ease;
    }

    .floating-chat-window.show {
        display: flex;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .floating-chat-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-radius: 12px 12px 0 0;
    }

    .floating-chat-header h6 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .floating-chat-close {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: #fff;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .floating-chat-close:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: rotate(90deg);
    }

    .floating-chat-header .custom-switch {
        margin: 0;
    }

    .floating-chat-header .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #fff;
        border-color: #fff;
    }

    .floating-chat-header .custom-control-input:checked ~ .custom-control-label::after {
        background-color: #667eea;
    }

    .floating-chat-header .custom-control-label::before {
        background-color: rgba(255, 255, 255, 0.3);
        border-color: rgba(255, 255, 255, 0.5);
    }

    .floating-chat-header .custom-control-label {
        color: #fff;
    }

    .floating-chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
        background: #f8f9fa;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .floating-chat-input-container {
        padding: 16px;
        background: #fff;
        border-top: 1px solid #e0e6ed;
        display: flex;
        gap: 12px;
        align-items: flex-end;
    }

    .floating-chat-input-wrapper {
        flex: 1;
    }

    .floating-chat-input-wrapper textarea {
        width: 100%;
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

    .floating-chat-input-wrapper textarea:focus {
        border-color: #667eea;
        outline: none;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .floating-chat-send-btn {
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

    .floating-chat-send-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .floating-chat-send-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .floating-chat-message {
        display: flex;
        gap: 12px;
        animation: fadeIn 0.3s ease;
    }

    .floating-chat-message.user {
        flex-direction: row-reverse;
    }

    .floating-chat-message-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 14px;
    }

    .floating-chat-message.user .floating-chat-message-avatar {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
    }

    .floating-chat-message.assistant .floating-chat-message-avatar {
        background: #e0e6ed;
        color: #667eea;
    }

    .floating-chat-message-content {
        max-width: 75%;
        padding: 10px 14px;
        border-radius: 12px;
        word-wrap: break-word;
        font-size: 14px;
    }

    .floating-chat-message.user .floating-chat-message-content {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        border-bottom-right-radius: 4px;
    }

    .floating-chat-message.assistant .floating-chat-message-content {
        background: #fff;
        color: #2c3e50;
        border: 1px solid #e0e6ed;
        border-bottom-left-radius: 4px;
    }

    .floating-chat-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #94a3b8;
        text-align: center;
        padding: 20px;
    }

    .floating-chat-empty i {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.5;
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

    @media (max-width: 768px) {
        .floating-chat-window {
            width: calc(100% - 40px);
            right: 20px;
            left: 20px;
            height: 70vh;
            bottom: 90px;
        }
    }
</style>

{{-- Floating Chat Widget --}}
@php
    $aiToolsSetting = null;
    $hasApiKey = false;
    if (module_enabled('Aitools')) {
        try {
            $aiToolsSetting = \Modules\Aitools\Entities\AiToolsSetting::where('company_id', company()->id)->first();
            $hasApiKey = $aiToolsSetting && !empty($aiToolsSetting->chatgpt_api_key);
        } catch (\Exception $e) {
            // Module model not available
        }
    }
@endphp

@if($hasApiKey && in_array('aitools', user_modules()) && user()->permission('view_aitools') == 'all')
    <div class="floating-chat-widget">
        <button type="button" class="floating-chat-btn" id="floating-chat-btn">
            <i class="fa fa-comments"></i>
        </button>
        
        <div class="floating-chat-window" id="floating-chat-window">
            <div class="floating-chat-header">
                <h6>
                    <i class="fa fa-robot"></i>
                    @lang('aitools::app.chatgpt')
                </h6>
                <div class="d-flex align-items-center">
                   
                    <button type="button" class="floating-chat-close" id="floating-chat-close">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>
            
            <div class="floating-chat-messages" id="floating-chat-messages">
                <div class="floating-chat-empty">
                    <i class="fa fa-comment-dots"></i>
                    <p>@lang('aitools::app.startConversation')</p>
                </div>
            </div>
            
            <div class="floating-chat-input-container">
                <div class="floating-chat-input-wrapper">
                    <textarea 
                        id="floating-chat-input" 
                        class="form-control" 
                        placeholder="@lang('aitools::app.askAnything')"
                        rows="2"></textarea>
                </div>
                <button type="button" id="floating-chat-send-btn" class="floating-chat-send-btn">
                    <i class="fa fa-paper-plane"></i>
                    <span>@lang('aitools::app.send')</span>
                </button>
            </div>
        </div>
    </div>
@endif


{{-- Floating Chat Widget Script --}}
@if($hasApiKey)
<script>
    $(document).ready(function() {
        const floatingChatBtn = $('#floating-chat-btn');
        const floatingChatWindow = $('#floating-chat-window');
        const floatingChatClose = $('#floating-chat-close');
        const floatingChatMessages = $('#floating-chat-messages');
        const floatingChatInput = $('#floating-chat-input');
        const floatingChatSendBtn = $('#floating-chat-send-btn');

        function addFloatingMessage(content, isUser = true) {
            floatingChatMessages.find('.floating-chat-empty').remove();

            const messageClass = isUser ? 'user' : 'assistant';
            const avatarIcon = isUser ? '<i class="fa fa-user"></i>' : '<i class="fa fa-robot"></i>';
            
            const messageHtml = `
                <div class="floating-chat-message ${messageClass}">
                    <div class="floating-chat-message-avatar">
                        ${avatarIcon}
                    </div>
                    <div class="floating-chat-message-content">
                        ${content.replace(/\n/g, '<br>')}
                    </div>
                </div>
            `;
            
            floatingChatMessages.append(messageHtml);
            floatingChatMessages.scrollTop(floatingChatMessages[0].scrollHeight);
        }

        function showFloatingLoading() {
            const loadingHtml = `
                <div class="floating-chat-message assistant" id="floating-loading-message">
                    <div class="floating-chat-message-avatar">
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
            floatingChatMessages.append(loadingHtml);
            floatingChatMessages.scrollTop(floatingChatMessages[0].scrollHeight);
        }

        function removeFloatingLoading() {
            $('#floating-loading-message').remove();
        }

        function sendFloatingMessage() {
            const prompt = floatingChatInput.val().trim();
            
            if (!prompt) {
                return;
            }

            addFloatingMessage(prompt, true);
            floatingChatInput.val('');
            floatingChatInput.focus();
            floatingChatSendBtn.prop('disabled', true);
            showFloatingLoading();

            $.easyAjax({
                url: "{{ route('ai-tools-settings.test-chat') }}",
                type: "POST",
                data: {
                    prompt: prompt,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    removeFloatingLoading();
                    if (response.status === 'success' && response.response) {
                        addFloatingMessage(response.response, false);
                    } else if (response.status === 'fail' && response.message) {
                        addFloatingMessage(response.message, false);
                    } else {
                        addFloatingMessage('{{ __('aitools::app.errorOccurredGeneric') }}', false);
                    }
                },
                error: function(xhr) {
                    removeFloatingLoading();
                    let errorMessage = '{{ __('aitools::app.errorOccurredRetry') }}';
                    
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
                    
                    addFloatingMessage(errorMessage, false);
                },
                complete: function() {
                    floatingChatSendBtn.prop('disabled', false);
                }
            });
        }

        // In-Application AI toggle handler (design only, no value stored)
        $('#in_application_ai_toggle').on('change', function() {
            // This is just for design/UI purposes - no value is stored
            // You can add any visual feedback or UI changes here if needed
            const isEnabled = $(this).is(':checked');
            // Add any UI logic here if needed
        });

        // Toggle floating chat window
        floatingChatBtn.on('click', function() {
            floatingChatWindow.toggleClass('show');
            if (floatingChatWindow.hasClass('show')) {
                floatingChatInput.focus();
            }
        });

        floatingChatClose.on('click', function() {
            floatingChatWindow.removeClass('show');
        });

        // Send button click
        floatingChatSendBtn.on('click', function() {
            sendFloatingMessage();
        });

        // Enter key to send (Shift+Enter for new line)
        floatingChatInput.on('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendFloatingMessage();
            }
        });
    });
</script>
@endif 
