<?php

$editDomainPermission = user()->permission('edit_domain');
$deleteDomainPermission = user()->permission('delete_domain');
?>
@if($hosting->domains && $hosting->domains->count() > 0)
    <div class="card bg-white border-0 b-shadow-4 mt-3">
        <div class="card-header bg-white border-bottom-grey justify-content-between p-20 d-flex align-items-center">
            <h3 class="card-title mb-0">
                @lang('servermanager::app.hosting.associatedDomains')
                <span class="badge badge-primary">{{ $hosting->domains->count() }}</span>
            </h3>
            <div class="card-tools">
                <a href="{{ route('domain.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> @lang('servermanager::app.domain.addDomain')
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead>
                        <tr>
                            <th>@lang('servermanager::app.domain.domainName')</th>
                            <th>@lang('servermanager::app.domain.provider')</th>
                            <th>@lang('servermanager::app.domain.expiryDate')</th>
                            <th>@lang('app.status')</th>
                            <th>@lang('app.action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($hosting->domains as $domain)
                            <tr>
                                <td>
                                    <a href="{{ route('domain.show', $domain->id) }}" target="_blank" class="text-darkest-grey">
                                        {{ $domain->domain_name }}
                                        {{-- <i class="fas fa-external-link-alt"></i> --}}
                                    </a>
                                </td>
                                <td>{{ $domain->provider ? $domain->provider->name : '--' }}</td>
                                <td>

                                        {{ $domain->expiry_date->timezone(company()->timezone)->translatedFormat(company()->date_format) }}

                                </td>
                                <td>
                                    @switch($domain->status)
                                        @case('active')
                                            <span class="badge badge-success">@lang('app.active')</span>
                                            @break
                                        @case('expired')
                                            <span class="badge badge-danger">@lang('app.expired')</span>
                                            @break
                                        @case('suspended')
                                            <span class="badge badge-warning">@lang('app.suspended')</span>
                                            @break
                                        @default
                                            <span class="badge badge-secondary">{{ ucfirst($domain->status) }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    <div class="col-md-2 col-2 text-right">
                                        <div class="dropdown">
                                           <button class="btn f-14 px-0 py-0 text-dark-grey dropdown-toggle" type="button"
                                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-ellipsis-v"></i>
                                                </button>

                                                <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                                    aria-labelledby="dropdownMenuLink" tabindex="0">

                                                    @if ($editDomainPermission == 'all' || ($editDomainPermission == 'added' && $domain->created_by == user()->id))
                                                    <a class="dropdown-item" href="{{ route('domain.show', $domain->id) }}">
                                                        <i class="mr-2 fas fa-eye"></i>@lang('app.view')
                                                    </a>
                                                    @endif
                                                    @if ($deleteDomainPermission == 'all' || ($deleteDomainPermission == 'added' && $domain->created_by == user()->id))
                                                    <a class="dropdown-item" href="{{ route('domain.edit', $domain->id) }}">
                                                        <i class="mr-2 fas fa-edit"></i>@lang('app.edit')
                                                    </a>
                                                    @endif
                                                    @if ($deleteDomainPermission == 'all' || ($deleteDomainPermission == 'added' && $domain->created_by == user()->id))
                                                    <a class="dropdown-item delete-domain" href="javascript:;" data-domain-id="{{ $domain->id }}">
                                                        <i class="mr-2 fa fa-trash"></i>@lang('app.delete')
                                                        </a>
                                                    @endif
                                                </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
