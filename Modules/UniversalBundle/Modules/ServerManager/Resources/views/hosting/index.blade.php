@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@push('styles')
    <style>
        .h-200 {
            height: 340px;
            overflow-y: auto;
        }

        .border-1 {
            background: #ECEFF34D;
            border-radius: 2px;
            padding: 16px;
        }

        .badge-light {
            font-size: 11.5px;
        }

        .table thead th{
            text-align: left !important;
        }

        .column-width-title {
            width: 140px;
        }

        .column-width{
            width:150px;
        }

    </style>
@endpush

@php
    $addHostingPermission = user()->permission('add_hosting');
    $viewHostingPermission = user()->permission('view_hosting');
    $editHostingPermission = user()->permission('edit_hosting');
    $deleteHostingPermission = user()->permission('delete_hosting');
@endphp

@section('filter-section')
    <x-filters.filter-box>

        <div class="select-box d-flex pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.duration')</p>
            <div class="select-status d-flex">
                <input type="text" class="position-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500 border-additional-grey"
                    id="datatableRange" placeholder="@lang('placeholders.dateRange')">
            </div>
        </div>

        <!-- STATUS START -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.status')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="status" id="status" data-live-search="true" data-size="8">
                    <option value="all">@lang('app.all')</option>
                    <option value="active">@lang('app.active')</option>
                    <option value="inactive">@lang('app.inactive')</option>
                    <option value="expired">@lang('servermanager::app.expired')</option>
                    <option value="suspended">@lang('servermanager::app.suspended')</option>
                    <option value="cancelled">@lang('app.cancelled')</option>
                    <option value="pending">@lang('servermanager::app.pending')</option>
                </select>
            </div>
        </div>
        <!-- STATUS END -->

        <!-- PROVIDER START -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('servermanager::app.hosting.provider')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="provider" id="provider" data-live-search="true" data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($providers as $provider)
                        <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <!-- PROVIDER END -->

        <!-- SEARCH BY HOSTING NAME START -->
        <div class="task-search d-flex  py-1 px-lg-3 px-0 border-right-grey align-items-center">
            <form class="w-100 mr-1 mr-lg-0 mr-md-1 ml-md-1 ml-0 ml-lg-0">
                <div class="input-group bg-grey rounded">
                    <div class="input-group-prepend">
                        <span class="input-group-text border-0 bg-additional-grey">
                            <i class="fa fa-search f-13 text-dark-grey"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control f-14 p-1 border-additional-grey" id="search-text-field"
                        placeholder="@lang('app.startTyping')">
                </div>
            </form>
        </div>
        <!-- SEARCH BY HOSTING NAME END -->

        <!-- RESET START -->
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
        <!-- RESET END -->

        <!-- MORE FILTERS START -->
        <x-filters.more-filter-box>
            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 " for="usr">@lang('app.dateFilterOn')</label>
                <div class="select-filter mb-4">
                    <select class="form-control select-picker" name="date_filter_on" id="date_filter_on">
                        <option value="created_at">@lang('app.createdOn')</option>
                        <option value="renewal_date" selected>@lang('servermanager::app.hosting.renewalDate')</option>
                        <option value="purchase_date">@lang('servermanager::app.hosting.purchaseDate')</option>
                    </select>
                </div>
            </div>
            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 " for="usr">@lang('servermanager::app.hosting.serverType')</label>
                <div class="select-filter mb-4">
                    <select class="form-control select-picker" name="server_type" id="server_type" data-container="body" data-live-search="true" data-size="8">
                        <option value="all">@lang('app.all')</option>
                        @foreach ($serverTypes as $serverType)
                            <option value="{{ $serverType->id }}">{{ $serverType->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 " for="usr">@lang('app.client')</label>
                <div class="select-filter mb-4">
                    <select class="form-control select-picker" data-live-search="true" name="client_id" id="client_id" data-container="body"
                            data-size="8">
                            @if (!in_array('client', user_roles()))
                                <option selected value="all">@lang('app.all')</option>
                            @endif
                            @foreach ($clients as $client)
                                <x-user-option :user="$client" />
                            @endforeach
                        </select>
                </div>
            </div>
        </x-filters.more-filter-box>
        <!-- MORE FILTERS END -->
    </x-filters.filter-box>
@endsection

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <!-- Add Task Export Buttons -->
        <div class="d-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                @if ($addHostingPermission == 'all' || $addHostingPermission == 'added')
                    <x-forms.link-primary :link="route('hosting.create')" class="mr-3 openRightModal" icon="plus">
                        @lang('servermanager::app.hosting.addHosting')
                    </x-forms.link-primary>
                @endif

                @if (canDataTableExport())
                    <x-forms.button-secondary id="export-all" class="mr-3 mb-2 mb-lg-0" icon="file-export">
                        @lang('app.exportExcel')
                    </x-forms.button-secondary>
                @endif
            </div>

            <x-datatable.actions>
                <div class="select-status mr-3 pl-lg-3">
                    <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                        <option value="">@lang('app.selectAction')</option>
                        <option value="change-status">@lang('modules.tasks.changeStatus')</option>
                        <option value="delete">@lang('app.delete')</option>
                    </select>
                </div>
                <div class="select-status mr-3 d-none quick-action-field" id="change-status-action">
                    <select name="status" class="form-control select-picker">
                        <option value="active">@lang('app.active')</option>
                        <option value="inactive">@lang('app.inactive')</option>
                        <option value="suspended">@lang('app.suspended')</option>
                        <option value="expired">@lang('app.expired')</option>
                        <option value="cancelled">@lang('app.cancelled')</option>
                    </select>
                </div>
            </x-datatable.actions>
        </div>

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100', 'id' => 'hosting-table']) !!}

        </div>
    </div>
    <!-- CONTENT WRAPPER END -->
@endsection

@push('scripts')
    @include('sections.datatable_js')
    {!! $dataTable->scripts() !!}

    <script>

        // Global error handler to catch parentNode errors
        window.addEventListener('error', function(e) {
            if (e.message && e.message.includes('parentNode')) {
                console.warn('ParentNode error caught and handled:', e.message);
                console.warn('Error source:', e.filename, 'Line:', e.lineno, 'Column:', e.colno);
                console.warn('Stack trace:', e.error ? e.error.stack : 'No stack trace available');
                e.preventDefault();
                return false;
            }
        });

        $('#hosting-table').on('preXhr.dt', function(e, settings, data) {
            var dateRangePicker = $('#datatableRange').data('daterangepicker');
            var startDate = $('#datatableRange').val();

            if (startDate == '') {
                startDate = null;
                endDate = null;
            } else {
                startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
            }

            var searchText = $('#search-text-field').val();
            var status = $('#status').val();
            var provider = $('#provider').val();
            var server_type = $('#server_type').val();
            var client_id = $('#client_id').val();
            var date_filter_on = $('#date_filter_on').val();

            data['startDate'] = startDate;
            data['endDate'] = endDate;
            data['searchText'] = searchText;
            data['status'] = status;
            data['provider'] = provider;
            data['server_type'] = server_type;
            data['client_id'] = client_id;
            data['date_filter_on'] = date_filter_on;
            @if (!is_null(request('start')) && !is_null(request('end')))
                data['startDate'] = '{{ request('start') }}';
                data['endDate'] = '{{ request('end') }}';
            @endif
        });

        const showTable = () => {
            window.LaravelDataTables["hosting-table"].draw(true);

        }

        $('#status, #provider, #server_type, #date_filter_on, #client_id')
            .on('change keyup',
                function() {
                    if ($('#status').val() != "all") {
                        $('#reset-filters').removeClass('d-none');
                    } else if ($('#provider').val() != "all") {
                        $('#reset-filters').removeClass('d-none');
                    } else if ($('#server_type').val() != "all") {
                        $('#reset-filters').removeClass('d-none');
                    } else if ($('#date_filter_on').val() != "renewal_date") {
                        $('#reset-filters').removeClass('d-none');
                    } else if ($('#client_id').val() != "all") {
                        $('#reset-filters').removeClass('d-none');
                    } else {
                        $('#reset-filters').addClass('d-none');
                    }
                    showTable();
                }
            );

        $('#search-text-field').on('keyup', function() {
            if ($('#search-text-field').val() != "") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            }
        });

        $('#reset-filters,#reset-filters-2').click(function() {
            $('#filter-form')[0].reset();
            $('.filter-box #status').val('all');
            $('.filter-box #date_filter_on').val('renewal_date');
            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });

        $('#quick-action-type').change(function() {
            const actionValue = $(this).val();
            if (actionValue != '') {
                $('#quick-action-apply').removeAttr('disabled');

                if (actionValue == 'change-status') {
                    $('.quick-action-field').addClass('d-none');
                    $('#change-status-action').removeClass('d-none');
                } else {
                    $('.quick-action-field').addClass('d-none');
                }
            } else {
                $('#quick-action-apply').attr('disabled', true);
                $('.quick-action-field').addClass('d-none');
            }
        });

        $('#quick-action-apply').click(function() {
            const actionValue = $('#quick-action-type').val();
            if (actionValue == 'delete') {
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
                        applyQuickAction();
                    }
                });

            } else {
                applyQuickAction();
            }
        });

        $('body').on('click', '#reset-filters', function() {

            const filterForm = $('#filter-form')[0];
            if (filterForm) {
                filterForm.reset();
            }

            // Reset individual filter elements safely
            const dateFilterOn = $('.filter-box #date_filter_on');
            if (dateFilterOn.length > 0) {
                dateFilterOn.val('renewal_date');
            }

            const statusFilter = $('.filter-box #status');
            if (statusFilter.length > 0) {
                statusFilter.val('all');
            }

            const selectPickers = $('.filter-box .select-picker');
            if (selectPickers.length > 0) {
                selectPickers.selectpicker("refresh");
            }

            $('#reset-filters').addClass('d-none');
            showTable();
        });

        $('body').on('click', '.delete-hosting', function() {
            var id = $(this).data('hosting-id');
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
                                window.location.reload();
                            }
                        }
                    });
                }
            });
        });

        // Handle URL parameters for filtering
        $(document).ready(function() {
            const urlParams = new URLSearchParams(window.location.search);

            // Set status filter if provided in URL
            if (urlParams.get('status')) {
                $('#status').val(urlParams.get('status')).trigger('change');
            }

            // Set provider filter if provided in URL
            if (urlParams.get('provider')) {
                $('#provider').val(urlParams.get('provider')).trigger('change');
            }

            // Set server type filter if provided in URL
            if (urlParams.get('server_type')) {
                $('#server_type').val(urlParams.get('server_type')).trigger('change');
            }

            // Set client filter if provided in URL
            if (urlParams.get('client_id')) {
                $('#client_id').val(urlParams.get('client_id')).trigger('change');
            }

            // Set date filter on if provided in URL
            if (urlParams.get('date_filter_on')) {
                $('#date_filter_on').val(urlParams.get('date_filter_on')).trigger('change');
            }

            // Set date range if provided in URL
            if (urlParams.get('startDate') && urlParams.get('endDate')) {
                const startDate = urlParams.get('startDate');
                const endDate = urlParams.get('endDate');

                // Initialize date range picker if it exists
                if ($('#datatableRange').length) {
                    $('#datatableRange').data('daterangepicker').setStartDate(startDate);
                    $('#datatableRange').data('daterangepicker').setEndDate(endDate);
                    $('#datatableRange').val(startDate + ' @lang("app.to") ' + endDate);
                }
            }

            // Set search text if provided in URL
            if (urlParams.get('searchText')) {
                $('#search-text-field').val(urlParams.get('searchText'));
            }

            // Refresh select pickers
            $('.select-picker').selectpicker('refresh');
        });



        const applyQuickAction = () => {
            var rowdIds = $("#hosting-table input:checkbox:checked").map(function() {
                return $(this).val();
            }).get();

            var url = "{{ route('server-manager.hosting.apply_quick_action') }}?row_ids=" + rowdIds;

            $.easyAjax({
                url: url,
                container: '#quick-action-form',
                type: "POST",
                disableButton: true,
                buttonSelector: "#quick-action-apply",
                data: $('#quick-action-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        showTable();
                        resetActionButtons();
                        deSelectAll();
                        $('#quick-action-form').hide();
                    }
                }
            })
        };

        @if (canDataTableExport())
            $('#export-all').click(function () {

                @if (request('start') && request('end'))
                    $('#datatableRange').data('daterangepicker').setStartDate("{{ request('start') }}");
                    $('#datatableRange').data('daterangepicker').setEndDate("{{ request('end') }}");
                @endif

                @if(request('date_filter_on'))
                    $('#date_filter_on').val("{{ request('date_filter_on') }}");
                @endif

                var dateRangePicker = $('#datatableRange').data('daterangepicker');

                let startDate = $('#datatableRange').val();
                let endDate;

                if (startDate == '') {
                    startDate = null;
                    endDate = null;
                } else {
                    startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                    endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
                }

                startDate = encodeURIComponent(startDate);
                endDate = encodeURIComponent(endDate);
                let dateFilterOn = $('#date_filter_on').val();

                var url = "{{ route('server-manager.hosting.export_all') }}";
                string = `?startDate=${startDate}&endDate=${endDate}&dateFilterOn=${dateFilterOn}`;
                url += string;
                window.location.href = url;

            });
        @endif

        $('#hosting-table').on('change', '.change-status', function() {
            var url = "{{ route('server-manager.hosting.change_status') }}";
            var token = "{{ csrf_token() }}";
            var id = $(this).data('hosting-id');
            var status = $(this).val();
            $.easyAjax({
                url: url,
                type: "POST",
                container: '.content-wrapper',
                blockUI: true,
                data: {
                    '_token': token,
                    hostingId: id,
                    status: status,
                    sortBy: 'id'
                },
                success: function(response) {
                    window.LaravelDataTables["hosting-table"].draw(true);
                }
            });
        });
    </script>
@endpush
