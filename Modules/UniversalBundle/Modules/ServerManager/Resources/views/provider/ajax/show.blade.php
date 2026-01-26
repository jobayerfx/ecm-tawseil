<?php
$editProviderPermission = user()->permission('edit_provider');
$deleteProviderPermission = user()->permission('delete_provider');
?>
<div id="task-detail-section">
    {{-- <h3 class="heading-h1 mb-3">@lang('servermanager::app.provider.viewProvider')</h3> --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="card bg-white border-0 b-shadow-4">
                <div class="card-header bg-white  border-bottom-grey  justify-content-between p-20">
                    <div class="row">
                        <div class="col-lg-8 col-10">
                            @lang('servermanager::app.provider.viewProvider')
                        </div>
                        <div class="col-2 col-lg-4 d-flex justify-content-end text-right">
                            <div class="dropdown">
                                <button
                                    class="btn btn-lg f-14 px-2 py-1 text-dark-grey  rounded  dropdown-toggle"
                                    type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-ellipsis-h"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                    aria-labelledby="dropdownMenuLink" tabindex="0">
                                    @if ($editProviderPermission == 'all' || ($editProviderPermission == 'added' && $provider->created_by == user()->id))
                                    <a class="dropdown-item" href="{{ route('provider.edit', $provider->id) }}"
                                        ><i class="mr-2 fa fa-edit"></i>@lang('app.edit')</a>
                                    @endif
                                    @if ($deleteProviderPermission == 'all' || ($deleteProviderPermission == 'added' && $provider->created_by == user()->id))
                                    <a class="dropdown-item delete-provider" href="javascript:;" data-provider-id="{{ $provider->id }}">
                                                <i class="mr-2 fa fa-trash"></i>
                                                @lang('app.delete')
                                            </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
                        <p class="mb-0 text-lightest f-14 w-30 ">{{ __('servermanager::app.provider.name') }}</p>
                        <p class="mb-0 text-dark-grey f-14 text-wrap text-darkest-grey">
                            {{ $provider->name }}
                        </p>
                    </div>
                    <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
                        <p class="mb-0 text-lightest f-14 w-30 ">{{ __('servermanager::app.provider.url') }}</p>
                        <p class="mb-0 text-dark-grey f-14 text-wrap text-darkest-grey">
                            {{ $provider->url }}
                        </p>
                    </div>
                    <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
                        <p class="mb-0 text-lightest f-14 w-30 ">{{ __('servermanager::app.provider.type') }}</p>
                        <p class="mb-0 text-dark-grey f-14 text-wrap text-darkest-grey">
                            <span class="badge {{ $provider->getTypeBadgeClass() }}">{{ ucfirst($provider->type) }}</span>
                        </p>
                    </div>
                    <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
                        <p class="mb-0 text-lightest f-14 w-30 ">{{ __('app.status') }}</p>
                        <p class="mb-0 text-dark-grey f-14 text-wrap text-darkest-grey">
                            <span class="badge {{ $provider->getStatusBadgeClass() }}">{{ ucfirst($provider->status) }}</span>
                        </p>
                    </div>
                    <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
                        <p class="mb-0 text-lightest f-14 w-30 ">{{ __('app.description') }}</p>
                        <p class="mb-0 text-dark-grey f-14 text-wrap text-darkest-grey">
                            {{ $provider->description }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('.edit-table-row').click(function() {
        var id = $(this).data('provider-id');
        var url = "{{ route('provider.edit', ':id') }}";
        url = url.replace(':id', id);

        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

    $('.delete-table-row').click(function() {
        var id = $(this).data('provider-id');
        Swal.fire({
            title: "@lang('messages.sweetAlertTitle')",
            text: "@lang('messages.recoverRecord')",
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: true,
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
                var url = "{{ route('provider.destroy', ':id') }}";
                url = url.replace(':id', id);

                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: {
                        '_token': token,
                        '_method': 'DELETE'
                    },
                    success: function(response) {
                        if (response.status == "success") {
                            $(MODAL_LG).modal('hide');
                            showTable();
                            Swal.fire({
                                icon: 'success',
                                text: response.message,
                                toast: true,
                                position: 'top-end',
                                timer: 3000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                            });
                        }
                    }
                });
            }
        });
    });

    init(MODAL_LG);
</script>
