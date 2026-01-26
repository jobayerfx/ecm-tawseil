@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')
    <x-filters.filter-box>
        <!-- DATE START -->
        <div class="pr-2 select-box d-flex border-right-grey border-right-grey-sm-0">
            <p class="pr-2 mb-0 f-14 text-dark-grey d-flex align-items-center">@lang('app.duration')</p>
            <div class="select-status d-flex">
                <input type="text" class="p-2 text-left border-0 position-relative text-dark form-control f-14 f-w-500 border-additional-grey"
                    id="datatableRange" placeholder="@lang('placeholders.dateRange')">
            </div>
        </div>
        <!-- DATE END -->

        <!-- SEARCH BY TASK START -->
        <div class="px-0 py-1 task-search d-flex px-lg-3 border-right-grey align-items-center">
            <form class="ml-0 mr-1 w-100 mr-lg-0 mr-md-1 ml-md-1 ml-lg-0">
                <div class="rounded input-group bg-grey">
                    <div class="input-group-prepend">
                        <span class="border-0 input-group-text bg-additional-grey">
                            <i class="fa fa-search f-13 text-dark-grey"></i>
                        </span>
                    </div>
                    <input type="text" class="p-1 form-control f-14 border-additional-grey" id="search-text-field"
                        placeholder="@lang('app.startTyping')">
                </div>
            </form>
        </div>
        <!-- SEARCH BY TASK END -->

        <!-- RESET START -->
        <div class="px-0 py-1 select-box d-flex px-lg-2 px-md-2">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
        <!-- RESET END -->

        <!-- MORE FILTERS START -->
        <x-filters.more-filter-box>
            <div class="more-filter-items">
                <label class="mb-12 f-14 text-dark-grey text-capitalize" for="usr">@lang('app.menu.teams')</label>
                <div class="mb-4 select-filter">
                    <select class="form-control select-picker" name="department" id="department" data-live-search="true" data-size="8">
                        <option value="all">@lang('app.all')</option>
                            @foreach ($teams as $team)
                                <option value="{{$team->id}}">{{$team->team_name}}</option>
                            @endforeach
                    </select>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="mb-12 f-14 text-dark-grey text-capitalize" for="usr">@lang('app.menu.designation')</label>
                <div class="mb-4 select-filter">
                    <select class="form-control select-picker" name="designation" id="designation" data-live-search="true" data-size="8">
                        <option value="all">@lang('app.all')</option>
                            @foreach ($designations as $designation)
                                <option value="{{$designation->id}}">{{$designation->name}}</option>
                            @endforeach
                    </select>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="mb-12 f-14 text-dark-grey text-capitalize" for="usr">@lang('modules.employees.employmentType')</label>
                <div class="mb-4 select-filter">
                    <select class="form-control select-picker" name="employment_type" id="employmentType" data-live-search="true" data-size="8">
                        <option value="all">@lang('app.all')</option>
                        <option value="full_time">@lang('app.fullTime')</option>
                        <option value="part_time">@lang('app.partTime')</option>
                        <option value="on_contract">@lang('app.onContract')</option>
                        <option value="internship">@lang('app.internship')</option>
                        <option value="trainee">@lang('app.trainee')</option>
                    </select>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="mb-12 f-14 text-dark-grey text-capitalize" for="usr">@lang('policy::app.signatureRequired')</label>
                <div class="mb-4 select-filter">
                    <select class="form-control select-picker" name="signature_required" id="signatureRequired" data-live-search="true" data-size="8">
                        <option value="all">@lang('app.all')</option>
                        <option value="yes">@lang('app.yes')</option>
                        <option value="no">@lang('app.no')</option>
                    </select>
                </div>
            </div>
        </x-filters.more-filter-box>
        <!-- MORE FILTERS END -->
    </x-filters.filter-box>
@endsection

@php
    $addPermission = user()->permission('add_policy');
    $archivePermission = user()->permission('can_archive_policy');
@endphp

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <!-- Add Task Export Buttons Start -->
        <div class="d-grid d-lg-flex d-md-flex action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center mb-2 mb-lg-0 mb-md-0"></div>

            <div class="btn-group mt-2 mt-lg-0 mt-md-0 ml-0 ml-lg-3 ml-md-3" role="group">
                <a href="{{ route('policy.index') }}" class="btn btn-secondary f-14 policies" data-toggle="tooltip"
                    data-original-title="@lang('policy::app.policies')"><i class="side-icon bi bi-list-ul"></i></a>

                @if ($archivePermission != 'none')
                    <a href="{{ route('policy.archive') }}" class="btn btn-secondary f-14 btn-active" data-toggle="tooltip"
                        data-original-title="@lang('app.archive')"><i class="side-icon bi bi-archive"></i></a>
                @endif
            </div>
        </div>
        <!-- Add Task Export Buttons End -->
        <!-- Task Box Start -->
        <div class="mt-3 bg-white rounded d-flex flex-column w-tables">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>
        <!-- Task Box End -->
    </div>
    <!-- CONTENT WRAPPER END -->

@endsection

@push('scripts')
    @include('sections.datatable_js')
    <script>
        $('#archive-policy-table').on('preXhr.dt', function(e, settings, data) {

            var dateRangePicker = $('#datatableRange').data('daterangepicker');
            let startDate = $('#datatableRange').val();

            if (startDate == '') {
                startDate = null;
                endDate = null;
            } else {
                startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
            }

            var department = $('#department').val();
            var designation = $('#designation').val();
            var employmentType = $('#employmentType').val();
            var searchText = $('#search-text-field').val();
            var signatureRequired = $('#signatureRequired').val();

            data['startDate'] = startDate;
            data['endDate'] = endDate;
            data['department'] = department;
            data['designation'] = designation;
            data['employmentType'] = employmentType;
            data['searchText'] = searchText;
            data['signatureRequired'] = signatureRequired;
        });

        $('#search-text-field, #department, #designation, #employmentType, #signatureRequired').on('change keyup', function() {
            if ($('#search-text-field').val() != "") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            } else if ($('#department').val() != "all") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            } else if ($('#designation').val() != "all") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            } else if ($('#employmentType').val() != "all") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            } else if ($('#signatureRequired').val() != "all") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            }
            else {
                $('#reset-filters').addClass('d-none');
                showTable();
            }
        });

        $('#reset-filters,#reset-filters-2').click(function() {
            $('#filter-form')[0].reset();
            $('.select-picker').val('all');

            $('.select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');

            showTable();
        });

        const showTable = () => {
            window.LaravelDataTables["archive-policy-table"].draw(false);
        }

        $('body').on('click', '.delete-archived-policy', function() {
            var id = $(this).data('policy-id');
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
                    var url = "{{ route('policy.destroy', ':id') }}";
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
                                showTable();
                            }
                        }
                    });
                }
            });
        });

        $('body').on('click', '.restore-archived-policy', function() {
            var id = $(this).data('user-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('policy::messages.unArchiveMessage')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmRevert')",
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
                    var url = "{{ route('policy.archive_restore', ':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {
                            '_token': token
                        },
                        success: function(response) {
                            if (response.status == "success") {
                                showTable();
                            }
                        }
                    });
                }
            });
        });
    </script>
@endpush
