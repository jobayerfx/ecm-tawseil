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

        .expiring-item {
            border-left: 4px solid #ffc107;
            /* background-color: #fff3cd; */
        }

        .expiring-item.danger {
            border-left-color: #dc3545;
            background-color: #f8d7da;
        }

    </style>
@endpush

@php
    $viewHostingPermission = user()->permission('view_hosting');
    $viewDomainPermission = user()->permission('view_domain');
    $today = now()->format(company()->moment_date_format);
    $thirtyDaysLater = now()->addDays(30)->format(company()->moment_date_format);
@endphp

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <div class="row row-cols-lg-4 my-3">

            @if ($viewHostingPermission != 'none' && $viewHostingPermission != '')
                <div class="col mb-4">
                    <a href="{{ route('hosting.index') }}" class="widget-filter-status">
                        <x-cards.widget :title="__('servermanager::app.dashboard.totalHostings')"
                                        value="{{ $totalHostings }}" icon="server" widgetId="totalHostings"/>
                    </a>
                </div>
            @endif

            @if ($viewHostingPermission != 'none' && $viewHostingPermission != '')
                <div class="col mb-4">
                    <a href="{{ route('hosting.index', ['status' => 'active']) }}" class="widget-filter-status">
                        <x-cards.widget :title="__('servermanager::app.dashboard.activeHostings')"
                                        value="{{ $activeHostings }}" icon="check-circle" widgetId="activeHostings"/>
                    </a>
                </div>
            @endif

            @if ($viewDomainPermission != 'none' && $viewDomainPermission != '')
                <div class="col mb-4">
                    <a href="{{ route('domain.index') }}" class="widget-filter-status">
                        <x-cards.widget :title="__('servermanager::app.dashboard.totalDomains')"
                                        value="{{ $totalDomains }}" icon="globe" widgetId="totalDomains"/>
                    </a>
                </div>
            @endif

            @if ($viewDomainPermission != 'none' && $viewDomainPermission != '')
                <div class="col mb-4">
                    <a href="{{ route('domain.index', ['status' => 'active']) }}" class="widget-filter-status">
                        <x-cards.widget :title="__('servermanager::app.dashboard.activeDomains')"
                                        value="{{ $activeDomains }}" icon="link" widgetId="activeDomains"/>
                    </a>
                </div>
            @endif

            @if ($viewHostingPermission != 'none' && $viewHostingPermission != '')
                <div class="col mb-4">
                    <a href="javascript:;" class="widget-filter-status" id="expiry_hosting">
                        <x-cards.widget :title="__('servermanager::app.dashboard.expiringHostings')"
                                        value="{{ $expiringHostings }}" icon="exclamation-triangle" widgetId="expiringHostings" :info="__('servermanager::app.dashboard.next30Days')"/>
                    </a>
                </div>
            @endif

            @if ($viewDomainPermission != 'none' && $viewDomainPermission != '')
                <div class="col mb-4">
                    <a href="javascript:;" class="widget-filter-status" id="expiry_domain">
                        <x-cards.widget :title="__('servermanager::app.dashboard.expiringDomains')"
                                        value="{{ $expiringDomains }}" icon="exclamation-triangle" widgetId="expiringDomains" :info="__('servermanager::app.dashboard.next30Days')"/>
                    </a>
                </div>
            @endif
        </div>

        <!-- EXPIRING ITEMS START -->
        @if ($viewHostingPermission != 'none' && $viewHostingPermission != '')
        <div class="row">
            <div class="col-sm-12 col-lg-6 mt-3">
                <x-cards.data :title="__('servermanager::app.dashboard.expiringHostingsTitle')" :value="__('servermanager::app.dashboard.next30Days')">
                    @if($expiringHostingsList->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>@lang('servermanager::app.hosting.name')</th>
                                        <th>@lang('servermanager::app.hosting.provider')</th>
                                        <th>@lang('servermanager::app.hosting.expiryDate')</th>
                                        {{-- <th>@lang('app.status')</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expiringHostingsList as $hosting)
                                        <tr class="expiring-item">
                                            <td>
                                                <a href="{{ route('hosting.show', $hosting->id) }}" class="text-dark">
                                                    {{ $hosting->name }}
                                                </a>
                                            </td>
                                            <td>{{ $hosting->provider->name }}</td>
                                            <td>{{ $hosting->renewal_date->format('M d, Y') }}</td>
                                            {{-- <td> --}}
                                                {{-- <x-status :value="$hosting->status" /> --}}
                                                    {{-- {{ $hosting->status }} --}}
                                            {{-- </td> --}}
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fa fa-check-circle fa-3x text-success mb-3"></i>
                            <h5>@lang('servermanager::app.dashboard.noExpiringHostings')</h5>
                        </div>
                    @endif
                </x-cards.data>
            </div>
            @endif

            @if ($viewDomainPermission != 'none' && $viewDomainPermission != '')
            <div class="col-sm-12 col-lg-6 mt-3">
                <x-cards.data :title="__('servermanager::app.dashboard.expiringDomainsTitle')" :value="__('servermanager::app.dashboard.next30Days')">
                    @if($expiringDomainsList->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>@lang('servermanager::app.domain.domainName')</th>
                                        <th>@lang('servermanager::app.domain.provider')</th>
                                        <th>@lang('servermanager::app.domain.expiryDate')</th>
                                        {{-- <th>@lang('app.status')</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expiringDomainsList as $domain)
                                        <tr class="expiring-item">
                                            <td>
                                                <a href="{{ route('domain.show', $domain->id) }}" class="text-dark">
                                                    {{ $domain->domain_name }}
                                                </a>
                                            </td>
                                            <td>{{ $domain->provider->name }}</td>
                                            <td>{{ $domain->expiry_date->format('M d, Y') }}</td>
                                            {{-- <td> --}}
                                                {{-- <x-status :value="$domain->status" /> --}}
                                                {{-- {{ $domain->status }} --}}
                                            {{-- </td> --}}
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fa fa-check-circle fa-3x text-success mb-3"></i>
                            <h5>@lang('servermanager::app.dashboard.noExpiringDomains')</h5>
                        </div>
                    @endif
                </x-cards.data>
            </div>
            @endif
        </div>
        <!-- EXPIRING ITEMS END -->

        <!-- RECENT ACTIVITIES START -->
        @if(in_array('admin', user_roles()))
            @include('servermanager::dashboard.recent-activities')
        @endif
        <!-- RECENT ACTIVITIES END -->

    </div>
    <!-- CONTENT WRAPPER END -->
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-refresh statistics every 5 minutes
    setInterval(function() {
        $.get('{{ route("server-manager.statistics") }}', function(data) {
            // Update statistics cards
            $('.widget-filter-status[data-widget-id="totalHostings"] .widget-value').text(data.hostings.total);
            $('.widget-filter-status[data-widget-id="activeHostings"] .widget-value').text(data.hostings.active);
            $('.widget-filter-status[data-widget-id="totalDomains"] .widget-value').text(data.domains.total);
            $('.widget-filter-status[data-widget-id="activeDomains"] .widget-value').text(data.domains.active);
            $('.widget-filter-status[data-widget-id="expiringHostings"] .widget-value').text(data.hostings.expiring);
            $('.widget-filter-status[data-widget-id="expiringDomains"] .widget-value').text(data.domains.expiring);
        });
    }, 300000); // 5 minutes
});

    $('#expiry_domain').click(function() {
        // Set startDate as today and endDate as 30 days later
        var startDate = moment().format("{{ company()->moment_date_format }}");
        var endDate = moment().add(30, 'days').format("{{ company()->moment_date_format }}");

        startDate = encodeURIComponent(startDate);
        endDate = encodeURIComponent(endDate);
        var url = `{{ route('domain.index') }}`;

        var string = `?status=active&startDate=${startDate}&endDate=${endDate}&date_filter_on=expiry_date`;
        url += string;

        window.location.href = url;
    });

    $('#expiry_hosting').click(function() {
        // Set startDate as today and endDate as 30 days later
        var startDate = moment().format("{{ company()->moment_date_format }}");
        var endDate = moment().add(30, 'days').format("{{ company()->moment_date_format }}");

        startDate = encodeURIComponent(startDate);
        endDate = encodeURIComponent(endDate);
        var url = `{{ route('hosting.index') }}`;

        var string = `?status=active&startDate=${startDate}&endDate=${endDate}&date_filter_on=renewal_date`;
        url += string;

        window.location.href = url;
    });
</script>
@endpush
