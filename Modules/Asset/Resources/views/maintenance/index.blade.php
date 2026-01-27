@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@php
    $addMaintenancePermission = user()->permission('add_asset');
@endphp

@section('filter-section')
    <x-filters.filter-box>
        <!-- ASSET START -->
        <div class="select-box d-flex py-2 pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('asset::app.assetName')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="asset_id" id="asset_id" data-live-search="true" data-size="8">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($assets as $asset)
                        <option value="{{ $asset->id }}">{{ $asset->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <!-- ASSET END -->

        <!-- TYPE START -->
        <div class="select-box d-flex py-2 px-lg-3 px-md-3 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('asset::app.maintenanceType')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="type" id="filter_type">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($types as $type)
                        <option value="{{ $type }}">{{ __('asset::app.' . $type) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <!-- TYPE END -->

        <!-- STATUS START -->
        <div class="select-box d-flex py-2 px-lg-3 px-md-3 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('asset::app.status')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="status" id="filter_status">
                    <option value="all">@lang('app.all')</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}">{{ __('asset::app.' . $status) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <!-- STATUS END -->

        <!-- SEARCH START -->
        <div class="task-search d-flex py-1 px-lg-3 px-0 border-right-grey align-items-center">
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
        <!-- SEARCH END -->

        <!-- RESET START -->
        <div class="select-box d-flex py-2 px-lg-3 px-md-3 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
        <!-- RESET END -->
    </x-filters.filter-box>
@endsection

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <!-- Statistics Cards Start -->
        <div class="row row-cols-lg-3 mb-4">
            <div class="col mb-4">
                <a href="javascript:;" class="widget-filter-status" data-status="overdue">
                    <x-cards.widget :title="__('asset::app.overdueMaintenance')" :value="$overdueMaintenance" 
                                    icon="exclamation-triangle" 
                                    :info="__('asset::app.requireImmediateAttention')" />
                </a>
            </div>

            <div class="col mb-4">
                <a href="javascript:;" class="widget-filter-schedule" data-date="{{ today()->format('Y-m-d') }}">
                    <x-cards.widget :title="__('asset::app.todaySchedule')" :value="$todaySchedule" 
                                    icon="calendar" 
                                    :info="__('asset::app.maintenanceScheduledToday')" />
                </a>
            </div>

            <div class="col mb-4">
                <a href="{{ route('assets.index') }}">
                    <x-cards.widget :title="__('asset::app.activeAssets')" :value="$activeAssets" 
                                    icon="cube" 
                                    :info="__('asset::app.currentlyOperational')" />
                </a>
            </div>
        </div>
        <!-- Statistics Cards End -->

        <!-- Add Maintenance Export Buttons Start -->
        <div class="d-grid d-lg-flex d-md-flex action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                @if ($addMaintenancePermission == 'all')
                    <x-forms.link-primary :link="route('asset-maintenance.create')" class="mr-3 openRightModal float-left" icon="plus">
                        @lang('asset::app.addMaintenance')
                    </x-forms.link-primary>
                @endif
            </div>
        </div>
        <!-- Add Maintenance Export Buttons End -->
        
        <!-- Maintenance Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
        <!-- Maintenance Box End -->
    </div>
    <!-- CONTENT WRAPPER END -->
@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        $('#asset-maintenance-table').on('preXhr.dt', function (e, settings, data) {
            var asset_id = $('#asset_id').val();
            var status = $('#filter_status').val();
            var type = $('#filter_type').val();
            var searchText = $('#search-text-field').val();
            data['asset_id'] = asset_id;
            data['status'] = status;
            data['type'] = type;
            data['searchText'] = searchText;
            if (window.todayScheduleFilter) {
                data['todaySchedule'] = 'true';
            }
        });

        const showTable = () => {
            window.LaravelDataTables["asset-maintenance-table"].draw(true);
        }

        $('#asset_id, #filter_status, #filter_type, #search-text-field').on('change keyup', function () {
            if ($('#filter_status').val() != "all" || $('#asset_id').val() != "all" || $('#filter_type').val() != "all" || $('#search-text-field').val() != "") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            } else {
                $('#reset-filters').addClass('d-none');
                showTable();
            }
        });

        $('#reset-filters').click(function () {
            $('#asset_id, #filter_status, #filter_type').val('all');
            $('.filter-box .select-picker').selectpicker("refresh");
            $('#search-text-field').val('');
            $('#reset-filters').addClass('d-none');
            window.todayScheduleFilter = false;
            showTable();
        });

        // Card click handlers
        $('.widget-filter-status').click(function() {
            var status = $(this).data('status');
            $('#filter_status').val(status);
            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').removeClass('d-none');
            window.todayScheduleFilter = false;
            showTable();
        });

        $('.widget-filter-schedule').click(function() {
            $('#filter_status').val('all');
            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').removeClass('d-none');
            // Set todaySchedule parameter
            window.todayScheduleFilter = true;
            showTable();
        });

        $('body').on('click', '.delete-table-row', function () {
            var id = $(this).data('maintenance-id');
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
                    var url = "{{ route('asset-maintenance.destroy', ':id') }}";
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
                        success: function (response) {
                            if (response.status == "success") {
                                window.LaravelDataTables["asset-maintenance-table"].draw(true);
                            }
                        }
                    });
                }
            });
        });
    </script>
@endpush

