<?php
$editHostingPermission = user()->permission('edit_hosting');
$deleteHostingPermission = user()->permission('delete_hosting');
?>
<div class="card bg-white border-0 b-shadow-4">
    <div class="card-header bg-white  border-bottom-grey  justify-content-between p-20">
        <div class="row">
            <div class="col-md-10 col-10">
                <h3 class="heading-h1">{{ $hosting->name }}</h3>
            </div>
            <div class="col-md-2 col-2 text-right">
                <div class="dropdown">
                   <button class="btn f-14 px-0 py-0 text-dark-grey dropdown-toggle" type="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-ellipsis-h"></i>
                        </button>

                        <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                            aria-labelledby="dropdownMenuLink" tabindex="0">

                            @if ($editHostingPermission == 'all' || ($editHostingPermission == 'added' && $hosting->created_by == user()->id))
                            <a class="dropdown-item" href="{{ route('hosting.edit', $hosting->id) }}">
                                <i class="mr-2 fas fa-edit"></i>@lang('app.edit')
                            </a>
                            @endif
                            @if ($deleteHostingPermission == 'all' || ($deleteHostingPermission == 'added' && $hosting->created_by == user()->id))
                            <a class="dropdown-item delete-hosting" href="javascript:;" data-id="{{ $hosting->id }}" data-action="delete">
                                <i class="mr-2 fas fa-trash"></i>@lang('app.delete')
                            </a>
                            @endif
                        </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-12 col-md-12 mb-4 mb-lg-4">
                <x-cards.data :title="__('servermanager::app.basicInformation')">
                    <x-cards.data-row :label="__('servermanager::app.hosting.name')" :value="$hosting->name" />
                    {{-- <x-cards.data-row :label="__('servermanager::app.hosting.domainName')" :value="$hosting->domain_name" /> --}}
                    <x-cards.data-row :label="__('servermanager::app.hosting.provider')" :value="$hosting->provider ? $hosting->provider->name : '--'" />
                    <x-cards.data-row :label="__('servermanager::app.hosting.providerUrl')" :value="$hosting->provider_url" />
                    <x-cards.data-row :label="__('servermanager::app.hosting.serverType')" :value="$hosting->serverType ? $hosting->serverType->name : '--'" />
                    <x-cards.data-row :label="__('servermanager::app.hosting.serverLocation')" :value="$hosting->server_location ?? '--'" />
                    <x-cards.data-row :label="__('servermanager::app.hosting.ipAddress')" :value="$hosting->ip_address ?? '--'" />
                    {{-- <x-cards.data-row :label="__('servermanager::app.hosting.assignedTo')" :value="$hosting->assignedTo?->name" /> --}}
                   <x-cards.data-row :label="__('servermanager::app.hosting.cpanelUrl')" :value="$hosting->cpanel_url ?? '--'" />
                    <x-cards.data-row :label="__('servermanager::app.hosting.username')" :value="$hosting->username ?? '--'" />
                    <x-cards.data-row :label="__('servermanager::app.hosting.ftpUsername')" :value="$hosting->ftp_username ?? '--'" />
                    <x-cards.data-row :label="__('servermanager::app.hosting.project')" :value="$hosting?->projectDetails?->project_name ?? '--'" />
                    <x-cards.data-row :label="__('servermanager::app.hosting.client')" :value="$hosting?->clientDetails?->company_name ?? '--'" />
                    <x-cards.data-row :label="__('servermanager::app.hosting.sslCertificate')" :value="$hosting->ssl_certificate ? 'Yes' : 'No'" />
                    <x-cards.data-row :label="__('servermanager::app.hosting.sslExpiryDate')" :value="$hosting->ssl_expiry_date ? $hosting->ssl_expiry_date->format(company()->date_format) : '--'" />
                    <x-cards.data-row :label="__('servermanager::app.hosting.sslType')" :value="$hosting->ssl_type ? $hosting->ssl_type : '--'" />
                    <x-cards.data-row :label="__('servermanager::app.hosting.sslCertificateInfo')" :value="$hosting->ssl_certificate_info" />
                     <x-cards.data-row :label="__('servermanager::app.hosting.description')" :value="$hosting->notes ?? '--'" />
                </x-cards.data>
            </div>
        </div>
    </div>
</div>
