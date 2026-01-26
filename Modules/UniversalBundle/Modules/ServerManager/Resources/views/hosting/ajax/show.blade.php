<div class="content-wrapper">
    <div class="content">
        <div class="container-fluid">
            <!-- Status Banner -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert @switch($hosting->status)
                        @case('active') alert-success @break
                        @case('suspended') alert-warning @break
                        @case('expired') alert-danger @break
                        @case('cancelled') alert-secondary @break
                        @default alert-info @endswitch" role="alert">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="alert-heading mb-0">
                                    <i class="fas @switch($hosting->status)
                                        @case('active') fa-check-circle @break
                                        @case('suspended') fa-exclamation-triangle @break
                                        @case('expired') fa-times-circle @break
                                        @case('cancelled') fa-ban @break
                                        @default fa-info-circle @endswitch"></i>
                                    Status:
                                    @switch($hosting->status)
                                        @case('active')
                                            <span class="badge badge-success">Active</span>
                                            @break
                                        @case('suspended')
                                            <span class="badge badge-warning">Suspended</span>
                                            @break
                                        @case('expired')
                                            <span class="badge badge-danger">Expired</span>
                                            @break
                                        @case('cancelled')
                                            <span class="badge badge-secondary">Cancelled</span>
                                            @break
                                        @default
                                            <span class="badge badge-secondary">{{ ucfirst($hosting->status) }}</span>
                                    @endswitch
                                </h5>
                                @if($hosting->renewal_date && $hosting->renewal_date->diffInDays(now()) <= 30)
                                    <small class="text-muted">
                                        <i class="fas fa-clock"></i>
                                        Renewal due in {{ $hosting->renewal_date->diffInDays(now()) }} days
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <!-- Main Hosting Information Card -->
                    @include('servermanager::hosting.ajax.hosting-information')

                    <!-- Associated Domains Card -->
                    @include('servermanager::hosting.ajax.associated-domains')
                </div>

                <div class="col-md-4">

                    @include('servermanager::hosting.ajax.billing')
                    <!-- Activity Log Card -->
                    {{-- @include('servermanager::hosting.ajax.activities') --}}

                    <!-- Statistics Card -->
                    {{-- @include('servermanager::hosting.ajax.statistics') --}}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<div class="modal fade" id="renewalModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Renew Hosting</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to renew this hosting plan?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success">Renew Hosting</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="suspensionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Suspend Hosting</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to suspend this hosting plan?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning">Suspend Hosting</button>
            </div>
        </div>
    </div>
</div>

<script>
function showRenewalModal() {
    $('#renewalModal').modal('show');
}

function showSuspensionModal() {
    $('#suspensionModal').modal('show');
}

function showDeleteModal() {
    $('#deleteModal').modal('show');
}

function checkServerStatus() {
    // Add server status check functionality
    alert('Server status check functionality will be implemented here');
}

function downloadBackup() {
    // Add backup download functionality
    alert('Backup download functionality will be implemented here');
}

function showLogs() {
    // Add logs view functionality
    alert('Logs view functionality will be implemented here');
}

function addDomain() {
    // Add domain functionality
    alert('Add domain functionality will be implemented here');
}

    $(document).ready(function() {
        $('body').on('click', '.delete-hosting', function() {
            var id = $(this).data('id');

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
                    var url = "{{ route('hosting.destroy', ':id') }}";
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
                                window.location = response.redirectUrl;
                            }
                        }
                    });
                }
            });
        });
    });
</script>
