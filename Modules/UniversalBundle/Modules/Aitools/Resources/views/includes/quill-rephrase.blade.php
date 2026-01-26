@if (module_enabled('Aitools') && (in_array('admin', user_roles()) || user()->permission('view_aitools') == 'all'))
    // Add custom rephrase button to toolbar (only if Aitools module is enabled)
    function addRephraseButton(quillInstance, editorId) {
        let attempts = 0;
        const maxAttempts = 10;

        function tryAddButton() {
            attempts++;
            const editorElement = document.querySelector(editorId);
            if (!editorElement) {
                if (attempts < maxAttempts) {
                    setTimeout(tryAddButton, 100);
                }
                return;
            }

            // Toolbar is typically a sibling of the editor container
            const toolbarContainer = editorElement.parentNode.querySelector('.ql-toolbar');
            if (!toolbarContainer) {
                if (attempts < maxAttempts) {
                    setTimeout(tryAddButton, 100);
                }
                return;
            }

            // Check if button already exists
            if (toolbarContainer.querySelector('.ql-rephrase')) {
                return;
            }

            // Create the button element
            const rephraseButton = document.createElement('button');
            rephraseButton.type = 'button';
            rephraseButton.className = 'ql-rephrase';
            rephraseButton.setAttribute('title', 'Rephrase Text');
            rephraseButton.setAttribute('aria-label', 'Rephrase');
            rephraseButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-stars" viewBox="0 0 16 16"><path d="M7.657 6.247c.11-.33.576-.33.686 0l.645 1.937a2.89 2.89 0 0 0 1.829 1.828l1.936.645c.33.11.33.576 0 .686l-1.937.645a2.89 2.89 0 0 0-1.828 1.829l-.645 1.936a.361.361 0 0 1-.686 0l-.645-1.937a2.89 2.89 0 0 0-1.828-1.828l-1.937-.645a.361.361 0 0 1 0-.686l1.937-.645a2.89 2.89 0 0 0 1.828-1.828zM3.794 1.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387A1.73 1.73 0 0 0 4.593 5.69l-.387 1.162a.217.217 0 0 1-.412 0L3.407 5.69A1.73 1.73 0 0 0 2.31 4.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387A1.73 1.73 0 0 0 3.407 2.31zM10.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.16 1.16 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.16 1.16 0 0 0-.732-.732L9.1 2.137a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732z"></path></svg>';

            // Add click handler
            rephraseButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                handleRephrase(quillInstance);
            });

            // Find the clean button and its format container
            const cleanButton = toolbarContainer.querySelector('.ql-clean');
            let inserted = false;

            if (cleanButton) {
                // Find the ql-formats container that holds the clean button
                const formatContainer = cleanButton.closest('.ql-formats');
                if (formatContainer && cleanButton.parentNode) {
                    // Add button to the same format group after clean button
                    cleanButton.parentNode.insertBefore(rephraseButton, cleanButton.nextSibling);
                    inserted = true;
                } else if (cleanButton.parentNode) {
                    // If no format container, insert after clean button directly
                    cleanButton.parentNode.insertBefore(rephraseButton, cleanButton.nextSibling);
                    inserted = true;
                }
            }

            // If we haven't inserted the button yet, create a new format group at the end
            if (!inserted && !rephraseButton.parentNode) {
                const formatsSpan = document.createElement('span');
                formatsSpan.className = 'ql-formats';
                formatsSpan.appendChild(rephraseButton);
                toolbarContainer.appendChild(formatsSpan);
                inserted = true;
            }

            if (inserted && rephraseButton.parentNode) {
                console.log('Rephrase button added successfully to', editorId);
            }
        }

        // Start trying to add the button
        setTimeout(tryAddButton, 150);
    }

    /**
     * Handle rephrase button click
     *
     * @param {Quill} quillInstance
     */
    function handleRephrase(quillInstance) {
        let range = quillInstance.getSelection(true);

        // If no selection, get all text
        if (!range || range.length === 0) {
            const text = quillInstance.getText();
            if (!text || text.trim().length === 0) {
                showRephraseError('{{ __("aitools::messages.pleaseEnterSomeText") }}');
                return;
            }
            range = { index: 0, length: text.length };
        }

        const textToRephrase = quillInstance.getText(range.index, range.length);

        if (!textToRephrase || textToRephrase.trim().length === 0) {
            showRephraseError('{{ __("aitools::messages.pleaseEnterSomeText") }}');
            return;
        }

        // Show loading state
        const toolbarContainer = quillInstance.container.parentNode.querySelector('.ql-toolbar');
        const rephraseButton = toolbarContainer ? toolbarContainer.querySelector('.ql-rephrase') : null;
        if (rephraseButton) {
            rephraseButton.classList.add('ql-disabled');
            rephraseButton.setAttribute('title', 'Rephrasing...');
        }

        // Call API to rephrase text
        $.ajax({
            url: "{{ route('projects.rephrase-text') }}",
            type: 'POST',
            data: {
                text: textToRephrase,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.status === 'success' && response.rephrased_text) {
                    // Delete selected text and insert rephrased text
                    quillInstance.deleteText(range.index, range.length, 'user');
                    quillInstance.insertText(range.index, response.rephrased_text, 'user');
                    quillInstance.setSelection(range.index + response.rephrased_text.length, 0);

                    // Show success message
                    if (typeof Swal !== 'undefined') {
                        // Swal.fire({
                        //     icon: 'success',
                        //     text: '{{ __("aitools::messages.textRephrasedSuccessfully") }}',
                        //     toast: true,
                        //     position: "top-end",
                        //     timer: 3000,
                        //     timerProgressBar: true,
                        //     showConfirmButton: false,
                        //     customClass: {
                        //         confirmButton: "btn btn-primary",
                        //     },
                        //     showClass: {
                        //         popup: "swal2-noanimation",
                        //         backdrop: "swal2-noanimation",
                        //     },
                        // });
                    } else if (typeof showSuccessMessage !== 'undefined') {
                        showSuccessMessage('{{ __("aitools::messages.textRephrasedSuccessfully") }}');
                    }
                } else {
                    showRephraseError(response.message || '{{ __("aitools::messages.aiRequestFailed") }}');
                }
            },
            error: function(xhr) {
                let errorMessage = '{{ __("aitools::messages.aiRequestFailed") }}';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }

                showRephraseError(errorMessage);
            },
            complete: function() {
                // Remove loading state
                const toolbarContainer = quillInstance.container.parentNode.querySelector('.ql-toolbar');
                const rephraseBtn = toolbarContainer ? toolbarContainer.querySelector('.ql-rephrase') : null;
                if (rephraseBtn) {
                    rephraseBtn.classList.remove('ql-disabled');
                    rephraseBtn.setAttribute('title', '{{ __("aitools::messages.rephraseText") }}');
                }
            }
        });
    }


    /**
     * Show rephrase error message
     *
     * @param {string} message
     */
    function showRephraseError(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                text: message,
                showConfirmButton: true,
                customClass: {
                    confirmButton: 'btn btn-primary',
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
                buttonsStyling: false
            });
        } else if (typeof showAlertMessage !== 'undefined') {
            showAlertMessage(message, 'error');
        } else if (typeof toastr !== 'undefined') {
            toastr.error(message);
        } else {
            alert(message);
        }
    }
@endif
