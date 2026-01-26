<?php
$editDomainPermission = user()->permission('edit_domain');
$deleteDomainPermission = user()->permission('delete_domain');
?>
<div class="row mt-4">
    <div class="col-8">
        <div class="col-lg-12 col-md-12 mb-4 mb-lg-4">
            <div class="card bg-white border-0 b-shadow-4">
                <div class="card-header bg-white  border-bottom-grey  justify-content-between p-20">
                    <div class="row">
                        <div class="col-lg-8 col-10">
                            <h5 class="mb-0">@lang('servermanager::app.domain.basicInfo')</h5>
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
                                    @if ($editDomainPermission == 'all' || ($editDomainPermission == 'added' && $domain->created_by == user()->id))
                                        <a class="dropdown-item" href="{{ route('domain.edit', $domain->id) }}"
                                            ><i class="mr-2 fa fa-edit"></i>@lang('app.edit')</a>
                                    @endif
                                    @if ($deleteDomainPermission == 'all' || ($deleteDomainPermission == 'added' && $domain->created_by == user()->id))
                                    <a class="dropdown-item delete-domain" href="javascript:;" data-domain-id="{{ $domain->id }}">
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
                        <p class="mb-0 text-lightest f-14 w-30 ">{{ __('servermanager::app.domain.domainName') }}</p>
                        <p class="mb-0 text-dark-grey f-14 text-wrap text-darkest-grey">
                            {{ $domain->domain_name }}
                        </p>
                    </div>
                    <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
                        <p class="mb-0 text-lightest f-14 w-30 ">{{ __('servermanager::app.domain.hosting') }}</p>
                        <p class="mb-0 text-dark-grey f-14 text-wrap text-darkest-grey">
                            {{ $domain?->hosting?->name ?? '--' }}
                        </p>
                    </div>
                    <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
                        <p class="mb-0 text-lightest f-14 w-30 ">{{ __('servermanager::app.domain.provider') }}</p>
                        <p class="mb-0 text-dark-grey f-14 text-wrap text-darkest-grey">
                            {{ $domain->provider ? $domain->provider->name : '--' }}
                        </p>
                    </div>
                    <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
                        <p class="mb-0 text-lightest f-14 w-30 ">{{ __('servermanager::app.domain.providerUrl') }}</p>
                        <p class="mb-0 text-dark-grey f-14 text-wrap text-darkest-grey">
                            {{ $domain->provider_url ?: '--' }}
                        </p>
                    </div>
                    {{-- <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
                        <p class="mb-0 text-lightest f-14 w-30 ">{{ __('servermanager::app.domain.type') }}</p>
                        <p class="mb-0 text-dark-grey f-14 text-wrap text-darkest-grey">
                            {{ $domain->domain_type }}
                        </p>
                    </div> --}}
                    <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
                        <p class="mb-0 text-lightest f-14 w-30 ">{{ __('app.status') }}</p>
                        <p class="mb-0 text-dark-grey f-14 text-wrap text-darkest-grey">
                            {{ $domain->status }}
                        </p>
                    </div>

                    <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
                        <p class="mb-0 text-lightest f-14 w-30 ">{{ __('servermanager::app.domain.whoisProtection') }}</p>
                        <p class="mb-0 text-dark-grey f-14 text-wrap text-darkest-grey">
                            {{ $domain->whois_protection }}
                        </p>
                    </div>
                    <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
                        <p class="mb-0 text-lightest f-14 w-30 ">{{ __('servermanager::app.domain.username') }}</p>
                        <p class="mb-0 text-dark-grey f-14 text-wrap text-darkest-grey">
                            {{ $domain->username ?: '--' }}
                        </p>
                    </div>
                    <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
                        <p class="mb-0 text-lightest f-14 w-30 ">{{ __('servermanager::app.domain.project') }}</p>
                        <p class="mb-0 text-dark-grey f-14 text-wrap text-darkest-grey">
                            {{ $domain->project?->project_name ?: '--' }}
                        </p>
                    </div>
                    <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
                        <p class="mb-0 text-lightest f-14 w-30 ">{{ __('servermanager::app.domain.client') }}</p>
                        <p class="mb-0 text-dark-grey f-14 text-wrap text-darkest-grey">
                            {{ $domain->client?->company_name ?: $domain->client?->user?->name ?: '--' }}
                        </p>
                    </div>
                    <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
                        <p class="mb-0 text-lightest f-14 w-30 ">{{ __('app.notes') }}</p>
                        <p class="mb-0 text-dark-grey f-14 text-wrap text-darkest-grey">
                            {{ $domain->notes }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12 col-md-12 mb-4 mb-lg-4">
            <div class="card bg-white border-0 b-shadow-4">
                <div class="card-header bg-white  border-bottom-grey  justify-content-between p-20">
                    <div class="row">
                        <div class="col-lg-8 col-10">
                            <h5 class="mb-0">@lang('servermanager::app.domain.dnsInfo')</h5>
                        </div>
                        <div class="col-2 col-lg-4 d-flex justify-content-end text-right">
                            <button type="button" class="btn btn-sm btn-primary" id="refresh-dns-btn">
                                <i class="fa fa-refresh mr-1"></i>@lang('servermanager::app.refresh')
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card bg-white border-0 b-shadow-4">
                <div class="card-body pt-2">
                    <div id="dns-loading" class="text-center py-3" style="display: none;">
                        <i class="fa fa-spinner fa-spin fa-2x text-primary"></i>
                        <p class="mt-2">@lang('servermanager::app.domain.fetchingDnsRecords')</p>
                    </div>

                    <div id="dns-error" class="alert alert-danger" style="display: none;">
                        <i class="fa fa-exclamation-triangle mr-2"></i>
                        <span id="dns-error-message"></span>
                    </div>

                    <div id="dns-content" style="display: none;">
                        <!-- DNS Summary -->
                        <div class="row mb-3 mt-3">
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h5 class="card-title text-primary" id="total-records">0</h5>
                                        <p class="card-text small">@lang('servermanager::app.domain.totalRecords')</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h5 class="card-title text-success" id="nameserver-count">0</h5>
                                        <p class="card-text small">@lang('servermanager::app.domain.nameservers')</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h5 class="card-title text-info" id="mail-server-count">0</h5>
                                        <p class="card-text small">@lang('servermanager::app.domain.mailServers')</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h5 class="card-title text-warning" id="ip-count">0</h5>
                                        <p class="card-text small">@lang('servermanager::app.domain.ipAddresses')</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- DNS Records -->
                        <div id="dns-records-container">
                            <!-- Records will be populated here -->
                        </div>

                        <!-- Last Updated -->
                        <div class="text-muted small mt-3">
                            <i class="fa fa-clock-o mr-1"></i>
                            @lang('servermanager::app.domain.lastUpdated'): <span id="dns-timestamp">--</span>
                        </div>
                    </div>

                    <!-- Fallback to static DNS info if API fails -->
                    <div id="dns-fallback" style="display: none;">
                        <x-cards.data-row :label="__('servermanager::app.domain.dnsProvider')" :value="$domain->dns_provider" />
                        <x-cards.data-row :label="__('servermanager::app.domain.nameservers')" :value="is_array($domain->nameservers) ? implode(', ', $domain->nameservers) : ($domain->nameservers ?: '--')" />
                        <x-cards.data-row
                            :label="__('servermanager::app.domain.dnsRecords')"
                            :value="is_array($domain->dns_records)
                                ? implode(', ', array_map(function($record) {
                                    return is_array($record) ? json_encode($record) : $record;
                                }, $domain->dns_records))
                                : ($domain->dns_records ?: '--')"
                        />
                        <x-cards.data-row :label="__('servermanager::app.domain.dnsStatus')" :value="ucfirst($domain->dns_status) ?: '--'" />
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="col-lg-12 col-md-12  mb-4 mb-lg-4">
            <x-cards.data :title="__('servermanager::app.domain.datesAndBilling')">
                <x-cards.data-row :label="__('servermanager::app.domain.registrationDate')" :value="$domain->registration_date?->format(company()->date_format) ?? '--'" />
                <x-cards.data-row :label="__('servermanager::app.domain.expiryDate')" :value="$domain->expiry_date?->format(company()->date_format) ?? '--'" />
                <x-cards.data-row :label="__('servermanager::app.domain.price')" :value="$domain->annual_cost ? '$' . number_format($domain->annual_cost, 2) : '--'" />
                <x-cards.data-row :label="__('servermanager::app.domain.plan')" :value="$domain->billing_cycle ?: '--'" />
            </x-cards.data>
        </div>
        <div class="col-lg-12 col-md-12  mb-4 mb-lg-4">
            <x-cards.data :title="__('servermanager::app.AdditionalInfo')">
                <x-cards.data-row :label="__('servermanager::app.domain.registrarUrl')" :value="$domain->registrar_url ?: '--'" />
                <x-cards.data-row :label="__('servermanager::app.domain.registrarUsername')" :value="$domain->registrar_username ?: '--'" />
                <x-cards.data-row :label="__('servermanager::app.domain.registrarStatus')" :value="$domain->registrar_status ?: '--'" />
                <x-cards.data-row :label="__('servermanager::app.domain.whoisProtection')" :value="$domain->whois_protection ? 'Yes' : 'No'" />
                <x-cards.data-row :label="__('servermanager::app.domain.autoRenewal')" :value="$domain->auto_renewal ? 'Yes' : 'No'" />
            </x-cards.data>
        </div>
    </div>
</div>

<script>

    $(document).ready(function() {
        $('body').on('click', '.delete-domain', function() {
            var id = $(this).data('domain-id');
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
                    var url = "{{ route('domain.destroy', ':id') }}";
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
                                window.location.href = response.redirectUrl;
                            }
                        }
                    });
                }
            });
        });

        // DNS functionality
        const domainId = {{ $domain->id }};

        // Load DNS details on page load
        loadDnsDetails();

        // Refresh DNS button click
        $('#refresh-dns-btn').click(function() {
            loadDnsDetails();
        });

        function loadDnsDetails() {
            showDnsLoading();

            $.ajax({
                url: "{{ route('server-manager.domain.dns-details', ':id') }}".replace(':id', domainId),
                type: 'GET',
                success: function(response) {
                    if (response.status === 'success') {
                        displayDnsData(response.data);
                    } else {
                        showDnsError('Failed to fetch DNS data');
                    }
                },
                error: function(xhr) {
                    showDnsError('Network error occurred while fetching DNS data');
                    showDnsFallback();
                }
            });
        }

        function showDnsLoading() {
            $('#dns-loading').show();
            $('#dns-content').hide();
            $('#dns-error').hide();
            $('#dns-fallback').hide();
        }

        function showDnsError(message) {
            $('#dns-loading').hide();
            $('#dns-content').hide();
            $('#dns-error').show();
            $('#dns-error-message').text(message);
            $('#dns-fallback').show();
        }

        function showDnsFallback() {
            $('#dns-fallback').show();
        }

        function displayDnsData(data) {
            $('#dns-loading').hide();
            $('#dns-error').hide();
            $('#dns-fallback').hide();
            $('#dns-content').show();

            // Update summary cards
            $('#total-records').text(data.summary?.total_records || 0);
            $('#nameserver-count').text(data.summary?.nameservers?.length || 0);
            $('#mail-server-count').text(data.summary?.mail_servers?.length || 0);
            $('#ip-count').text(data.summary?.ip_addresses?.length || 0);

            // Update timestamp
            if (data.timestamp) {
                const timestamp = new Date(data.timestamp);
                $('#dns-timestamp').text(timestamp.toLocaleString());
            }

            // Display DNS records
            displayDnsRecords(data.records);
        }

        function displayDnsRecords(records) {
            const container = $('#dns-records-container');
            container.empty();

            if (!records || Object.keys(records).length === 0) {
                container.html('<div class="alert alert-info">No DNS records found for this domain.</div>');
                return;
            }

            // Define record type order and colors
            const recordOrder = ['A', 'AAAA', 'CNAME', 'MX', 'NS', 'TXT', 'SOA'];
            const recordColors = {
                'A': 'primary',
                'AAAA': 'info',
                'CNAME': 'secondary',
                'MX': 'success',
                'NS': 'warning',
                'TXT': 'dark',
                'SOA': 'danger'
            };

            recordOrder.forEach(recordType => {
                if (records[recordType]) {
                    const color = recordColors[recordType] || 'primary';
                    const recordsHtml = records[recordType].map(record => {
                        let recordHtml = `
                            <div class="dns-record-item border-bottom py-2">
                                <div class="row">
                                    <div class="col-md-2">
                                        <span class="badge badge-${color}">${record.type}</span>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>${record.name || '@'}</strong>
                                    </div>
                                    <div class="col-md-4">
                                        <code class="text-break">${record.value}</code>
                                    </div>
                                    <div class="col-md-2">
                                        <small class="text-muted">TTL: ${record.ttl}</small>
                                    </div>
                                </div>`;

                        // Add priority for MX records
                        if (record.priority) {
                            recordHtml += `<div class="row mt-1">
                                <div class="col-md-2"></div>
                                <div class="col-md-4">
                                    <small class="text-muted">Priority: ${record.priority}</small>
                                </div>
                            </div>`;
                        }

                        // Add additional fields for SOA records
                        if (record.type === 'SOA') {
                            recordHtml += `
                                <div class="row mt-1">
                                    <div class="col-md-2"></div>
                                    <div class="col-md-10">
                                        <small class="text-muted">
                                            Admin: ${record.admin || 'N/A'} |
                                            Serial: ${record.serial || 'N/A'} |
                                            Refresh: ${record.refresh || 'N/A'} |
                                            Retry: ${record.retry || 'N/A'} |
                                            Expire: ${record.expire || 'N/A'} |
                                            Minimum: ${record.minimum || 'N/A'}
                                        </small>
                                    </div>
                                </div>`;
                        }

                        recordHtml += '</div>';
                        return recordHtml;
                    }).join('');

                    container.append(`
                        <div class="mb-4">
                            <h6 class="text-${color} mb-3">
                                <i class="fa fa-server mr-2"></i>${recordType} Records (${records[recordType].length})
                            </h6>
                            ${recordsHtml}
                        </div>
                    `);
                }
            });
        }
        // Function to fetch DNS records
        function fetchDnsRecords() {
            const domainId = {{ $domain->id }};

            // Show loading state
            $('#dns-loading').show();
            $('#dns-content').hide();
            $('#dns-error').hide();
            $('#dns-fallback').hide();

            $.ajax({
                url: "{{ route('server-manager.domain.dns-details', $domain->id) }}",
                type: 'GET',
                success: function(response) {
                    $('#dns-loading').hide();

                    if (response.status === 'success' && response.data.success) {
                        displayDnsData(response.data);
                        $('#dns-content').show();
                    } else {
                        showDnsError(response.data?.error || 'Failed to fetch DNS records');
                    }
                },
                error: function(xhr) {
                    $('#dns-loading').hide();
                    showDnsError('Network error occurred while fetching DNS records');
                }
            });
        }

        // Function to display DNS data
        function displayDnsData(data) {
            // Update summary cards
            $('#total-records').text(data.summary?.total_records || 0);
            $('#nameserver-count').text(data.summary?.nameserver_count || 0);
            $('#mail-server-count').text(data.summary?.mail_server_count || 0);
            $('#ip-count').text(data.summary?.ip_count || 0);

            // Update timestamp
            if (data.timestamp) {
                const timestamp = new Date(data.timestamp);
                $('#dns-timestamp').text(timestamp.toLocaleString());
            }

            // Display DNS records
            displayDnsRecords(data.records);
        }

        // Function to display DNS records
        function displayDnsRecords(records) {
            const container = $('#dns-records-container');
            container.empty();

            if (!records || Object.keys(records).length === 0) {
                container.html('<div class="alert alert-info">No DNS records found for this domain.</div>');
                return;
            }

            // Define record type order and colors
            const recordOrder = ['A', 'AAAA', 'CNAME', 'MX', 'NS', 'TXT', 'SOA'];
            const recordColors = {
                'A': 'primary',
                'AAAA': 'info',
                'CNAME': 'secondary',
                'MX': 'success',
                'NS': 'warning',
                'TXT': 'dark',
                'SOA': 'danger'
            };

            recordOrder.forEach(recordType => {
                if (records[recordType] && records[recordType].length > 0) {
                    const color = recordColors[recordType] || 'primary';
                    const recordsHtml = records[recordType].map(record => {
                        return `
                            <div class="dns-record-item border-bottom py-2">
                                <div class="row">
                                    <div class="col-md-2">
                                        <span class="badge badge-${color}">${record.type}</span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>${record.name || '@'}</strong>
                                    </div>
                                    <div class="col-md-5">
                                        <code class="text-break">${record.formatted_data || record.data}</code>
                                    </div>
                                    <div class="col-md-2">
                                        <small class="text-muted">TTL: ${record.ttl}</small>
                                    </div>
                                </div>
                            </div>`;
                    }).join('');

                    container.append(`
                        <div class="mb-4">
                            <h6 class="text-${color} mb-3">
                                <i class="fa fa-server mr-2"></i>${recordType} Records (${records[recordType].length})
                            </h6>
                            ${recordsHtml}
                        </div>
                    `);
                }
            });
        }

        // Function to show DNS error
        function showDnsError(message) {
            $('#dns-error-message').text(message);
            $('#dns-error').show();
            $('#dns-fallback').show();
        }

        // Initialize DNS lookup on page load
        $(document).ready(function() {
            // Fetch DNS records when page loads
            fetchDnsRecords();

            // Handle refresh button click
            $('#refresh-dns-btn').click(function() {
                fetchDnsRecords();
            });
        });
    });
</script>
