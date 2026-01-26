@php
    $viewonboardingPermission = user()->permission('manage_employee_onboarding');
    $viewoffboardingPermission = user()->permission('manage_employee_offboarding');
@endphp
@if ($viewoffboardingPermission == 'all')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <style>
        .capitalize-first-letter::first-letter {
            text-transform: capitalize;
        }
    </style>
    <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4">
        <div class="table-responsive">
            <table class="table table-bordered" id="sortable-offboarding">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>@lang('onboarding::clan.menu.title')</th>
                        <th>@lang('Task for')</th>
                        <th>@lang('Employee can see')</th>
                        <th class="text-right">@lang('app.action')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($onboardingSetting as $key => $status)
                        @if ($status->type === 'offboard')
                            <tr id="status-{{ $status->id }}" data-id="{{ $status->id }}">
                                <td><i class="bi bi-arrows-move"></i></td>
                                <td>{{ $status->title }}</td>
                                <td class= "capitalize-first-letter">{{ $status->task_for }}</td>
                                <td>{{ $status->employee_can_see == 1 ? 'Yes' : 'No' }}</td>
                                <td class="text-right">
                                    <div class="task_view">
                                        <a href="javascript:;" data-status-id="{{ $status->id }}"
                                            class="editOffboarding task_view_more d-flex align-items-center justify-content-center">
                                            <i class="fa fa-edit icons mr-1"></i> @lang('app.edit')
                                        </a>
                                    </div>
                                    <div class="task_view mt-1 mt-lg-0 mt-md-0 ml-1">
                                        <a href="javascript:;" data-status-id="{{ $status->id }}"
                                            class="delete-project-status task_view_more d-flex align-items-center justify-content-center">
                                            <i class="fa fa-trash icons mr-1"></i> @lang('app.delete')
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- Note about the Start Onboarding button -->
        <div class="mt-3">
            <p><strong>@lang('app.note'):</strong>@lang('onboarding::clan.offboardingBtnNote')</p>
        </div>
    </div>

    <script>
        $(function() {
            // Load the order from local storage on page load
            loadOrder();

            $("#sortable-offboarding tbody").sortable({
                placeholder: "ui-state-highlight",
                update: function(event, ui) {
                    var order = $(this).sortable('toArray', {
                        attribute: 'data-id'
                    });
                    // Store the new order in local storage
                    localStorage.setItem('offboardingOrder', JSON.stringify(order));

                    // Optionally, you can still send the order to the server for persistence
                    $.ajax({
                        url: "{{ route('onboarding-settings.priority') }}",
                        method: "POST",
                        data: {
                            order: order,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.status == "success") {
                                $("#sortable tbody").html(response.html);
                                // Optional: Show success message or perform other actions
                            }
                        }
                    });
                }
            }).disableSelection();
        });

        function loadOrder() {
            var order = JSON.parse(localStorage.getItem('offboardingOrder'));

            if (order) {
                $.each(order, function(index, id) {
                    var row = $('#status-' + id);
                    $('#sortable-offboarding tbody').append(row);
                });
            }
        }

        $('.editOffboarding').click(function() {

            var id = $(this).data('status-id');

            var url = "{{ route('onboarding-settings.edit', ':id') }}?type=offboarding";
            url = url.replace(':id', id);

            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('body').on('click', '.delete-project-status', function() {

            var id = $(this).data('status-id');

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

                    var url = "{{ route('onboarding-settings.destroy', ':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        blockUI: true,
                        data: {
                            '_token': token,
                            '_method': 'DELETE'
                        },
                        success: function(response) {
                            if (response.status == "success") {
                                $('#status-' + id).fadeOut();
                            }
                        }
                    });
                }
            });
        });
    </script>
@endif
