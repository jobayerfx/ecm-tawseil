<style>
    .member-card {
        transition: all 0.15s ease-in-out;
    }
    
    .member-card:hover {
        border-color: var(--bs-primary) !important;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 123, 255, 0.075) !important;
    }
</style>

<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang("groupmessage::app.groupDetail")</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-sm-12">
            <div class="card-body"></div>

                <x-cards.data-row :label="__('app.name')" :value="$group->name"
                    html="true" />

                <x-cards.data-row :label="__('groupmessage::app.createdOn')" :value="$group->created_at ? $group->created_at->translatedFormat(company()->date_format) : '--'" />

                <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex mt-2 mb-2">
                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                        @lang('groupmessage::app.owner')
                    </p>
                    @if ($group->addedBy)
                        <x-employee :user="$group->addedBy"/>
                    @else
                        <p>--</p>
                    @endif
                </div>

                <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                        @lang('groupmessage::app.members')
                        @if ($group->members && $group->members->count() > 0)
                            <span class="badge bg-secondary ms-2">{{ $group->members->count() }}</span>
                        @endif
                    </p>
                    
                    @if ($group->members && $group->members->count() > 0)
                        <div class="ml-0">
                            @foreach ($group->members as $member)
                                <div class="mb-3 d-flex align-items-center justify-content-between p-2 border rounded member-card" id="member-{{ $member->id }}">
                                    <div class="d-flex align-items-center">
                                        <x-employee :user="$member"/>
                                        @if($member->id == $group->owner_id)
                                            <span class="badge bg-primary text-white ms-2 small">
                                                <i class="fa fa-crown me-1"></i> @lang('groupmessage::app.owner')
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <div class="d-flex align-items-center">
                                        @if(user()->id == $group->owner_id)
                                            @if(user()->id !== $member->id)
                                                <button class="btn btn-outline-danger btn-sm remove-member ml-3" 
                                                        data-member-id="{{ $member->id }}" 
                                                        data-toggle="tooltip" 
                                                        data-original-title="{{ __('groupmessage::messages.removeMember') }}">
                                                    <i class="fa fa-times"></i> @lang('app.remove')
                                                </button>
                                            @endif
                                        @elseif(user()->id == $member->id)
                                            <button class="btn btn-outline-warning btn-sm leave-group ml-3" 
                                                    data-member-id="{{ $member->id }}" 
                                                    data-toggle="tooltip" 
                                                    data-original-title="{{ __('groupmessage::messages.leaveFromGroup') }}">
                                                <i class="fa fa-sign-out-alt"></i> @lang('app.leave')
                                            </button>
                                        @elseif(in_array('admin', user_roles()) || in_array('employee', user_roles()))
                                            @if($group->owner_id !== $member->id)
                                                <button class="btn btn-outline-danger btn-sm remove-member ml-3" 
                                                        data-member-id="{{ $member->id }}" 
                                                        data-toggle="tooltip" 
                                                        data-original-title="{{ __('groupmessage::messages.removeMember') }}">
                                                    <i class="fa fa-times"></i> @lang('app.remove')
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fa fa-users fa-2x mb-2"></i>
                            <p class="mb-0">@lang('groupmessage::app.noMembersInGroup')</p>
                        </div>
                    @endif
                </div>

                <x-cards.data-row :label="__('app.description')" :value="$group->description"
                    html="true" />
            </div>
        </div>
    </div>
</div>
<div class="modal-footer mr-2">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
</div>

<script>
    $(document).ready(function() {
        $('.remove-member').on('click', function() {
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmRemove')",
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

                var memberId = $(this).data('member-id');
                var memberDiv = $('#member-' + memberId);

                const groupId = '{{ $group->id }}';
                let url = "{{ route('group-messages.remove-group-member', ':id') }}";
                url = url.replace(':id', groupId);

                $.easyAjax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        member_id: memberId
                    },
                    success: function(response) {
                        if (response.status == 'success') {
                            let currentUser = '{{user()->id}}';

                            if (memberId == currentUser) {
                                window.location.reload();
                            }
                            else {
                                memberDiv.remove();
                            }
                        }
                        else {
                            console.log('Failed to remove member.');
                        }
                    },
                    error: function(xhr) {
                        console.log('An error occurred while trying to remove the member.');
                    }
                });
            });
        });

        // Handle leave group functionality
        $('.leave-group').on('click', function() {
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('groupmessage::messages.leaveFromGroup')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('app.leave')",
                cancelButtonText: "@lang('app.cancel')",
                customClass: {
                    confirmButton: 'btn btn-warning mr-3',
                    cancelButton: 'btn btn-secondary'
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var memberId = $(this).data('member-id');
                    var memberDiv = $('#member-' + memberId);

                    const groupId = '{{ $group->id }}';
                    let url = "{{ route('group-messages.remove-group-member', ':id') }}";
                    url = url.replace(':id', groupId);

                    $.easyAjax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            member_id: memberId
                        },
                        success: function(response) {
                            if (response.status == 'success') {
                                // User left the group, reload the page
                                window.location.reload();
                            } else {
                                console.log('Failed to leave group.');
                            }
                        },
                        error: function(xhr) {
                            console.log('An error occurred while trying to leave the group.');
                        }
                    });
                }
            });
        });
    });
</script>
