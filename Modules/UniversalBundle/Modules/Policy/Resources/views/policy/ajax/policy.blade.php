@php
    $editPermission = user()->permission('edit_policy');
    $deletePermission = user()->permission('delete_policy');
    $addPermission = user()->permission('add_policy');
@endphp

<div id="task-detail-section" class="mx-auto mt-4">
    <div class="bg-white rounded b-shadow-4">
        <!-- Header -->
        <div class="border-bottom border-grey p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 mr-3">
                        <div class="w-48 h-48 rounded d-flex align-items-center justify-content-center bg-light">
                            <i class="fa fa-file text-primary f-18"></i>
                        </div>
                    </div>
                    <div>
                        <h1 class="f-21 font-weight-semibold text-dark mb-0">{{ $policy->title }}</h1>
                        <div class="d-flex align-items-center mt-2">
                            @if($policy->status == 'published')
                                <span class="badge badge-success mr-2">
                                    <i class="fa fa-check-circle mr-1"></i>@lang('policy::app.published')
                                </span>
                            @else
                                <span class="badge badge-warning mr-2">
                                    <i class="fa fa-clock-o mr-1"></i>@lang('policy::app.draft')
                                </span>
                            @endif
                            @if($isAcknowledged->employeeAcknowledge->isNotEmpty())
                                <span class="badge badge-info mr-2">
                                    <i class="fa fa-check mr-1"></i>@lang('policy::app.acknowledged')
                                </span>
                            @endif
                            <span class="f-13 text-dark-grey">
                                @lang('policy::app.publishDate'): {{ $policy->publish_date ? $policy->publish_date->translatedFormat(company()->date_format) : '--' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center">
                    @if(!$policy->trashed())
                        @if ($policy->status == 'published' && !is_null($ackPermission))
                            <x-forms.button-primary icon="check" data-policy-id="{{$policy->id}}" id="acknowledge"
                                class="{{ $isAcknowledged->employeeAcknowledge->isNotEmpty() ? 'd-none' : '' }} mr-2">
                                @lang('policy::app.acknowledge')
                            </x-forms.button-primary>
                        @endif
                        @if ($policy->status == 'draft' && (user()->hasRole('admin') || $addPermission == 'all'))
                            <x-forms.button-secondary icon="check-circle" class="publish-policy mr-2" data-policy-id="{{ $policy->id }}">
                                @lang('policy::app.publish')
                            </x-forms.button-secondary>
                        @endif
                    @endif


                    @if(($policy->employeeAcknowledge->isEmpty() && (($editPermission == 'all' || ($editPermission == 'added' && $policy->added_by == user()->id) || ($editPermission == 'owned' && $department && $designation && $employmentType)) || ($editPermission == 'both' && (($department && $designation && $employmentType) || $policy->added_by == user()->id))))
                    ||
                    ($policy->status == 'draft' && (user()->hasRole('admin') || $addPermission == 'all'))
                    )

                        <div class="position-relative">
                            <button class="p-2 text-dark-grey rounded btn btn-lg f-14 text-lightest text-capitalize dropdown-toggle" type="button" data-toggle="dropdown">
                                <i class="fa fa-ellipsis-h"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right border-grey b-shadow-4" aria-labelledby="dropdownMenuLink" tabindex="0">
                                @if(!$policy->trashed())
                                    @if($policy->employeeAcknowledge->isEmpty() && (($editPermission == 'all' || ($editPermission == 'added' && $policy->added_by == user()->id) || ($editPermission == 'owned' && $department && $designation && $employmentType)) || ($editPermission == 'both' && (($department && $designation && $employmentType) || $policy->added_by == user()->id))))
                                        <a class="dropdown-item openRightModal" href="{{ route('policy.edit', $policy->id) }}">
                                            <i class="mr-2 fa fa-edit"></i>@lang('app.edit')
                                        </a>
                                    @endif

                                    @if ($policy->status == 'draft' && (user()->hasRole('admin') || $addPermission == 'all'))
                                        <a href="javascript:;" class="dropdown-item publish-policy" data-policy-id="{{ $policy->id }}">
                                            <i class="mr-2 fa fa-check-circle"></i>@lang('policy::app.publish')
                                        </a>
                                    @endif
                                @endif

                                @if ($policy->status == 'draft' && ($deletePermission == 'all' || ($deletePermission == 'added' && $policy->added_by == user()->id) || ($deletePermission == 'owned' && $department && $designation && $employmentType)))
                                    <a href="javascript:;" class="dropdown-item delete-policy" data-policy-id="{{ $policy->id }}">
                                        <i class="mr-2 fa fa-trash"></i>@lang('app.delete')
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>


        </div>

        <!-- Content -->
        <div class="row border-top border-grey">
            <!-- Main Content -->
            <div class="col-md-8 p-4">
                <div>
                    @if($policy->description)
                        <div class="mb-4">
                            <h2 class="f-18 font-weight-medium text-dark mb-3">@lang('app.description')</h2>
                            <div class="text-dark-grey">
                                {!! $policy->description !!}
                            </div>
                        </div>
                    @endif

                    @if($policy->filename)
                        <div class="mb-4">
                            <h2 class="f-18 font-weight-medium text-dark mb-3">@lang('app.file')</h2>
                            <a class="tn btn-sm btn-secondary restore-project mr-2"
                               href="{{ route('policy-file.download', md5($policy->id)) }}?type=only-file">
                                <i class="fa fa-download mr-2"></i> @lang('app.download')
                            </a>
                        </div>
                    @endif

                    <!-- Policy Scope -->
                    <div class="mt-4">
                        <h2 class="f-18 font-weight-medium text-dark mb-4">Policy Scope</h2>
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <dt class="f-14 font-weight-medium text-dark-grey mb-2">@lang('app.menu.department')</dt>
                                <dd class="f-14 text-dark">
                                    @if($departments && $departments != '--')
                                        <div class="d-flex flex-wrap">
                                            @foreach(explode(',', strip_tags($departments)) as $dept)
                                                <span class="badge badge-secondary mr-2 mb-2">
                                                    {{ trim($dept) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        --
                                    @endif
                                </dd>
                            </div>

                            <div class="col-md-6 mb-4">
                                <dt class="f-14 font-weight-medium text-dark-grey mb-2">@lang('app.menu.designation')</dt>
                                <dd class="f-14 text-dark">
                                    @if($designations && $designations != '--')
                                        <div class="d-flex flex-wrap">
                                            @foreach(explode(',', strip_tags($designations)) as $designation)
                                                <span class="badge badge-secondary mr-2 mb-2">
                                                    {{ trim($designation) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        --
                                    @endif
                                </dd>
                            </div>

                            <div class="col-md-6 mb-4">
                                <dt class="f-14 font-weight-medium text-dark-grey mb-2">@lang('modules.employees.gender')</dt>
                                <dd class="f-14 text-dark">{{ $policy->gender ? __('app.'.$policy->gender) : '--' }}</dd>
                            </div>

                            <div class="col-md-6 mb-4">
                                <dt class="f-14 font-weight-medium text-dark-grey mb-2">@lang('modules.employees.employmentType')</dt>
                                <dd class="f-14 text-dark">{!! $employmentTypes ? $employmentTypes : '--' !!}</dd>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4 border-left border-grey">
                <!-- Policy Details -->
                <div>
                    <h2 class="f-18 font-weight-medium text-dark p-4 mb-0">@lang('app.details')</h2>
                    <dl>
                        <div class="px-4 py-3 border-bottom border-grey">
                            <dt class="f-14 font-weight-medium text-dark-grey mb-1">@lang('app.addedBy')</dt>
                            <dd class="mb-0">
                                @if ($policy->addedBy)
                                    <x-employee :user="$policy->addedBy"/>
                                @else
                                    <span class="f-14 text-dark">--</span>
                                @endif
                            </dd>
                        </div>

                        <div class="px-4 py-3 border-bottom border-grey">
                            <dt class="f-14 font-weight-medium text-dark-grey mb-1">Created On</dt>
                            <dd class="mb-0 f-14 text-dark">
                                {{ $policy->created_at ? $policy->created_at->timezone(company()->timezone)->translatedFormat(company()->date_format .' '. company()->time_format) : '--' }}
                            </dd>
                        </div>

                        @if ($policy->updatedBy)

                            <div class="px-4 py-3 border-bottom border-grey">
                                <dt class="f-14 font-weight-medium text-dark-grey mb-1">@lang('policy::app.updatedBy')</dt>
                                <dd class="mb-0">
                                    <x-employee :user="$policy->updatedBy"/>
                                </dd>
                            </div>

                            <div class="px-4 py-3">
                                <dt class="f-14 font-weight-medium text-dark-grey mb-1">@lang('policy::app.lastUpdatedOn')</dt>
                                <dd class="mb-0 f-14 text-dark">
                                    {{ $policy->updated_at ? $policy->updated_at->timezone(company()->timezone)->translatedFormat(company()->date_format .' '. company()->time_format) : '--' }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.w-48 {
    width: 48px;
    height: 48px;
}
.h-48 {
    height: 48px;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>
<script>

    $('body').on('click', '.delete-policy', function() {
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
                var url = "{{ route('policy.destroy', $policy->id) }}";

                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: {
                        '_token': token,
                        '_method': 'DELETE',
                    },
                    success: function(response) {
                        if (response.status == "success") {
                            window.location.href = response.redirectUrl;
                        }
                    }
                });
            }
        });
    });

    $('body').on('click', '.delete-file', function() {
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
                var url = "{{ route('policy-file.destroy', ':id') }}";
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
                            $('#policy-file-list').html(response.view);
                        }
                    }
                });
            }
        });
    });

    var signature_required = "{{$policy->signature_required}}";

    $('body').on('click', '#acknowledge', function() {
        if(signature_required == 'yes'){
            var id = $(this).data('policy-id');
            url = "{{ route('policy.sign', ':id') }}";
            url = url.replace(':id', id);

            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        }
        else {
            Swal.fire({
                title: "@lang('policy::messages.confirmPolicy')",
                icon: 'success',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('policy::app.acknowledge')",
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
                    const url = "{{route('policy.acknowledge', $policy->id)}}"
                    let token = "{{ csrf_token() }}";
                    $.easyAjax({
                        url: url,
                        container: '#savePolicyForm',
                        type: "POST",
                        data: {
                        '_token': token
                        },
                        disableButton: true,
                        blockUI: true,
                        buttonSelector: "#acknowledge",
                        success: function(response) {
                            if (response.status == 'success') {
                                    window.location.reload();
                            }
                        }
                    });
                }
            });
        }
    });

    $('body').on('click', '.publish-policy', function() {
        var id = $(this).data('policy-id');
        var url = "{{ route('policy.publish', ':id') }}";
        url = url.replace(':id', id);

        var token = "{{ csrf_token() }}";

        $.easyAjax({
            type: 'POST',
            url: url,
            data: {
                '_token': token,
            },
            success: function(response) {
                if (response.status == "success") {
                    window.location.reload();
                }
            }
        });
    });
</script>
