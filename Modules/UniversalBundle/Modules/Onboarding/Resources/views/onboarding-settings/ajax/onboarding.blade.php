@php
    $viewonboardingPermission = user()->permission('manage_employee_onboarding');
    $viewoffboardingPermission = user()->permission('manage_employee_offboarding');
@endphp
@if ($viewonboardingPermission == 'all')
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
        <table class="table table-bordered" id="sortable">
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
                    @if ($status->type === 'onboard')
                        <tr id="status-{{ $status->id }}" data-id="{{ $status->id }}">
                            <td><i class="bi bi-arrows-move"></i></td>
                            <td>{{ $status->title }}</td>
                            <td class= "capitalize-first-letter">{{ $status->task_for }}</td>
                            <td>{{ $status->employee_can_see == 1 ? 'Yes' : 'No' }}</td>
                            <td class="text-right">
                                <div class="task_view">
                                    <a href="javascript:;" data-status-id="{{ $status->id }}"
                                        class="editOnboard task_view_more d-flex align-items-center justify-content-center">
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
        <x-alert type="info">
            <strong>@lang('app.note'):</strong>@lang('onboarding::clan.onboardingBtnNote')
        </x-alert>
    </div>

</div>

<script>
    $(function() {
        loadOrder();
        $("#sortable tbody").sortable({
            placeholder: "ui-state-highlight",
            update: function(event, ui) {
                var order = $(this).sortable('toArray', {
                    attribute: 'data-id'
                });
                localStorage.setItem('onboardingOrder', JSON.stringify(order));

                $.ajax({
                    url: "{{ route('onboarding-settings.priority') }}",
                    method: "POST",
                    data: {
                        order: order,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.status == "success") {
                            $("#sortable tbody").html(response
                            .html); // Assuming the server returns updated HTML

                        }
                    }
                });

            }
        }).disableSelection();
    });

    function loadOrder() {
        var order = JSON.parse(localStorage.getItem('onboardingOrder'));

        if (order) {
            $.each(order, function(index, id) {
                var row = $('#status-' + id);
                $('#sortable tbody').append(row);
            });
        }
    }

    $('body').on('click', '.default_status', function() {
        var statusID = $(this).data('status-id');
        var token = "{{ csrf_token() }}";

        $.easyAjax({
            url: "{{ route('project-settings.setDefault', ':id') }}",
            type: "POST",
            data: {
                id: statusID,
                _token: token
            },
            blockUI: true,
            success: function(response) {
                if (response.status == "success") {
                    window.location.reload();
                }
            }
        });
    });



    $('.editOnboard').click(function() {

        var id = $(this).data('status-id');

        var url = "{{ route('onboarding-settings.edit', ':id') }}?type=onboarding";
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
