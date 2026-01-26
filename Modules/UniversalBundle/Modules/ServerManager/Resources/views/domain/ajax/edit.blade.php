@php
    $addProviderPermission = user()->permission('add_provider');
@endphp

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-domain-data-form">
            @include('sections.password-autocomplete-hide')
            @method('PUT')

            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                    @lang('servermanager::app.domain.editDomain')</h4>
                <div class="row p-20">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-lg-3 col-md-6">
                                <x-forms.text fieldId="domain_name" :fieldLabel="__('servermanager::app.domain.domainName')" fieldName="domain_name"
                                              fieldRequired="true" :fieldPlaceholder="__('servermanager::placeholders.domainName')" :fieldValue="$domain->domain_name">
                                </x-forms.text>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <div class="form-group my-3">
                                    <label for="domain_provider" class="f-14 text-dark-grey mb-12 text-capitalize">
                                        @lang('servermanager::app.domain.provider') <span class="text-danger">*</span>
                                    </label>
                                    <x-forms.input-group>
                                        <select class="form-control select-picker" id="domain_provider" name="domain_provider" required data-live-search="true" data-size="8">
                                            <option value="" disabled selected>--</option>
                                            @foreach($providers as $provider)
                                                <option value="{{ $provider->id }}" data-url="{{ $provider->url }}" {{ $domain->domain_provider == $provider->id ? 'selected' : '' }}>{{ $provider->name }}</option>
                                            @endforeach
                                        </select>
                                        @if ($addProviderPermission == 'all')
                                        <x-slot name="append">
                                            <button id="domain-provider-setting-add" type="button"
                                                class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                        </x-slot>
                                        @endif
                                    </x-forms.input-group>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-forms.text fieldId="provider_url" :fieldLabel="__('servermanager::app.domain.providerUrl')" fieldName="provider_url"
                                              :fieldPlaceholder="__('servermanager::placeholders.providerUrl')" :fieldValue="$domain->provider_url">
                                </x-forms.text>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-forms.select fieldId="domain_type" :fieldLabel="__('servermanager::app.domain.type')" fieldName="domain_type"
                                                fieldRequired="true">
                                    <option value="">@lang('app.selectDomainType')</option>
                                    <option value="com" {{ $domain->domain_type == 'com' ? 'selected' : '' }}>.com</option>
                                    <option value="net" {{ $domain->domain_type == 'net' ? 'selected' : '' }}>.net</option>
                                    <option value="org" {{ $domain->domain_type == 'org' ? 'selected' : '' }}>.org</option>
                                    <option value="info" {{ $domain->domain_type == 'info' ? 'selected' : '' }}>.info</option>
                                    <option value="biz" {{ $domain->domain_type == 'biz' ? 'selected' : '' }}>.biz</option>
                                    <option value="co" {{ $domain->domain_type == 'co' ? 'selected' : '' }}>.co</option>
                                    <option value="io" {{ $domain->domain_type == 'io' ? 'selected' : '' }}>.io</option>
                                    <option value="me" {{ $domain->domain_type == 'me' ? 'selected' : '' }}>.me</option>
                                    <option value="tv" {{ $domain->domain_type == 'tv' ? 'selected' : '' }}>.tv</option>
                                    <option value="app" {{ $domain->domain_type == 'app' ? 'selected' : '' }}>.app</option>
                                    <option value="dev" {{ $domain->domain_type == 'dev' ? 'selected' : '' }}>.dev</option>
                                    <option value="tech" {{ $domain->domain_type == 'tech' ? 'selected' : '' }}>.tech</option>
                                    <option value="online" {{ $domain->domain_type == 'online' ? 'selected' : '' }}>.online</option>
                                    <option value="site" {{ $domain->domain_type == 'site' ? 'selected' : '' }}>.site</option>
                                    <option value="store" {{ $domain->domain_type == 'store' ? 'selected' : '' }}>.store</option>
                                    <option value="blog" {{ $domain->domain_type == 'blog' ? 'selected' : '' }}>.blog</option>
                                    <option value="cc" {{ $domain->domain_type == 'cc' ? 'selected' : '' }}>.cc</option>
                                    <option value="ws" {{ $domain->domain_type == 'ws' ? 'selected' : '' }}>.ws</option>
                                    <option value="other" {{ $domain->domain_type == 'other' ? 'selected' : '' }}>Other</option>
                                </x-forms.select>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-forms.select fieldId="hosting_id" :fieldLabel="__('servermanager::app.domain.hosting')" fieldName="hosting_id">
                                    <option value="">@lang('servermanager::app.selectHosting')</option>
                                    @foreach($hostings as $hosting)
                                        <option value="{{ $hosting->id }}" {{ $domain->hosting_id == $hosting->id ? 'selected' : '' }}>{{ $hosting->name }}</option>
                                    @endforeach
                                </x-forms.select>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-forms.text fieldId="username" :fieldLabel="__('servermanager::app.domain.username')" fieldName="username"
                                              :fieldPlaceholder="__('servermanager::placeholders.username')" :fieldValue="$domain->username">
                                </x-forms.text>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <div class="form-group my-3">
                                    <label for="password" class="f-14 text-dark-grey mb-12 text-capitalize">
                                        @lang('servermanager::app.domain.password')
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control height-35 f-14"
                                               id="password" name="password"
                                               placeholder="@lang('servermanager::placeholders.password')"
                                               value="{{ $domain->password }}">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary border-grey height-35"
                                                    type="button" id="toggle-password">
                                                <i class="fa fa-eye" id="password-eye-icon"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-forms.select fieldId="status" :fieldLabel="__('app.status')" fieldName="status" fieldRequired="true">
                                    <option value="" disabled selected>@lang('app.selectStatus')</option>
                                    <option value="active" {{ $domain->status == 'active' ? 'selected' : '' }}>@lang('app.active')</option>
                                    <option value="inactive" {{ $domain->status == 'inactive' ? 'selected' : '' }}>@lang('app.inactive')</option>
                                    <option value="expired" {{ $domain->status == 'expired' ? 'selected' : '' }}>@lang('servermanager::app.expired')</option>
                                    <option value="suspended" {{ $domain->status == 'suspended' ? 'selected' : '' }}>@lang('servermanager::app.suspended')</option>
                                    <option value="transferred" {{ $domain->status == 'transferred' ? 'selected' : '' }}>@lang('servermanager::app.transferred')</option>
                                    <option value="pending" {{ $domain->status == 'pending' ? 'selected' : '' }}>@lang('app.pending')</option>
                                </x-forms.select>
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-lg-3 col-md-6">
                                <x-forms.datepicker fieldId="registration_date" :fieldLabel="__('servermanager::app.domain.purchaseDate')" fieldName="registration_date"
                                                    fieldRequired="true" :fieldPlaceholder="__('servermanager::placeholders.purchaseDate')" :fieldValue="$domain->registration_date ? $domain->registration_date->format(company()->date_format) : ''">
                                </x-forms.datepicker>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-forms.datepicker fieldId="expiry_date" :fieldLabel="__('servermanager::app.domain.expiryDate')" fieldName="expiry_date"
                                                    fieldRequired="true" :fieldPlaceholder="__('servermanager::placeholders.expiryDate')" :fieldValue="$domain->expiry_date ? $domain->expiry_date->format(company()->date_format) : ''">
                                </x-forms.datepicker>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-forms.number fieldId="annual_cost" :fieldLabel="__('servermanager::app.domain.price')" fieldName="annual_cost"
                                                :fieldPlaceholder="__('servermanager::placeholders.price')" :fieldValue="$domain->annual_cost">
                                </x-forms.number>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-forms.select fieldId="billing_cycle" :fieldLabel="__('servermanager::app.domain.plan')" fieldName="billing_cycle">
                                    <option value="" disabled selected>@lang('servermanager::app.domain.selectPlan')</option>
                                    <option value="monthly" {{ $domain->billing_cycle == 'monthly' ? 'selected' : '' }}>@lang('servermanager::app.monthly')</option>
                                    <option value="quarterly" {{ $domain->billing_cycle == 'quarterly' ? 'selected' : '' }}>@lang('servermanager::app.quarterly')</option>
                                    <option value="semi_annually" {{ $domain->billing_cycle == 'semi_annually' ? 'selected' : '' }}>@lang('servermanager::app.semiAnnually')</option>
                                    <option value="annually" {{ $domain->billing_cycle == 'annually' ? 'selected' : '' }}>@lang('servermanager::app.annually')</option>
                                    <option value="biennially" {{ $domain->billing_cycle == 'biennially' ? 'selected' : '' }}>@lang('servermanager::app.biennially')</option>
                                </x-forms.select>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-forms.text fieldId="registrar_url" :fieldLabel="__('servermanager::app.domain.registrarUrl')" fieldName="registrar_url"
                                              :fieldPlaceholder="__('servermanager::placeholders.registrarUrl')" :fieldValue="$domain->registrar_url">
                                </x-forms.text>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-forms.text fieldId="registrar_username" :fieldLabel="__('servermanager::app.domain.registrarUsername')" fieldName="registrar_username"
                                              :fieldPlaceholder="__('servermanager::placeholders.registrarUsername')" :fieldValue="$domain->registrar_username">
                                </x-forms.text>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <div class="form-group my-3">
                                    <label for="registrar_password" class="f-14 text-dark-grey mb-12 text-capitalize">
                                        @lang('servermanager::app.domain.registrarPassword')
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control height-35 f-14"
                                               id="registrar_password" name="registrar_password"
                                               placeholder="@lang('servermanager::placeholders.registrarPassword')"
                                               value="{{ $domain->registrar_password }}">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary border-grey height-35"
                                                    type="button" id="toggle-registrar-password">
                                                <i class="fa fa-eye" id="registrar-password-eye-icon"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-forms.select fieldId="registrar_status" :fieldLabel="__('servermanager::app.domain.registrarStatus')" fieldName="registrar_status">
                                    <option value="">@lang('app.selectStatus')</option>
                                    <option value="active" {{ $domain->registrar_status == 'active' ? 'selected' : '' }}>@lang('app.active')</option>
                                    <option value="inactive" {{ $domain->registrar_status == 'inactive' ? 'selected' : '' }}>@lang('app.inactive')</option>
                                    <option value="expired" {{ $domain->registrar_status == 'expired' ? 'selected' : '' }}>@lang('servermanager::app.expired')</option>
                                    <option value="suspended" {{ $domain->registrar_status == 'suspended' ? 'selected' : '' }}>@lang('servermanager::app.suspended')</option>
                                    <option value="transferred" {{ $domain->registrar_status == 'transferred' ? 'selected' : '' }}>@lang('servermanager::app.transferred')</option>
                                    <option value="pending" {{ $domain->registrar_status == 'pending' ? 'selected' : '' }}>@lang('servermanager::app.pending')</option>
                                </x-forms.select>
                            </div>



                            <div class="col-lg-3 col-md-6">
                                <x-forms.select fieldId="project_id" :fieldLabel="__('servermanager::app.domain.project')" fieldName="project_id">
                                    <option value="">@lang('servermanager::app.domain.selectProject')</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" {{ $domain->project_id == $project->id ? 'selected' : '' }}>{{ $project->project_name }}</option>
                                    @endforeach
                                </x-forms.select>
                            </div>

                            <div class="col-lg-3 col-md-6 my-3">
                                <x-client-selection-dropdown :clients="$clients" :selected="$domain->client_id" :fieldRequired="false" />
                            </div>

                            {{-- <div class="col-lg-3 col-md-6">
                                <x-forms.text fieldId="dns_provider" :fieldLabel="__('servermanager::app.domain.dnsProvider')" fieldName="dns_provider"
                                              :fieldPlaceholder="__('placeholders.dnsProvider')" :fieldValue="$domain->dns_provider">
                                </x-forms.text>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-forms.select fieldId="dns_status" :fieldLabel="__('servermanager::app.domain.dnsStatus')" fieldName="dns_status">
                                    <option value="">@lang('app.selectStatus')</option>
                                    <option value="enabled" {{ $domain->dns_status == 'enabled' ? 'selected' : '' }}>@lang('servermanager::app.enabled')</option>
                                    <option value="disabled" {{ $domain->dns_status == 'disabled' ? 'selected' : '' }}>@lang('servermanager::app.disabled')</option>
                                </x-forms.select>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-forms.textarea fieldId="nameservers" :fieldLabel="__('servermanager::app.domain.nameservers')" fieldName="nameservers"
                                                  :fieldPlaceholder="__('placeholders.nameservers')" :fieldValue='$domain->nameservers'>
                                </x-forms.textarea>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-forms.textarea fieldId="dns_records" :fieldLabel="__('servermanager::app.domain.dnsRecords')" fieldName="dns_records"
                                                  :fieldPlaceholder="__('placeholders.dnsRecords')" :fieldValue="$domain->dns_records">
                                </x-forms.textarea>
                            </div> --}}

                        </div>

                        <div class="row">

                            <div class="col-lg-3 col-md-6 my-3">
                                <x-forms.checkbox fieldId="auto_renewal" :fieldLabel="__('servermanager::app.domain.autoRenewal')" fieldName="auto_renewal"
                                                  fieldValue="1" :checked="$domain->auto_renewal == 'enabled'">
                                </x-forms.checkbox>
                            </div>

                            <div class="col-lg-3 col-md-6 my-3">
                                <x-forms.checkbox fieldId="whois_protection" :fieldLabel="__('servermanager::app.domain.whoisProtection')" fieldName="whois_protection"
                                                  fieldValue="1" :checked="$domain->whois_protection == 'enabled'">
                                </x-forms.checkbox>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-lg-2 col-md-6 my-3">
                                <x-forms.checkbox fieldId="expiry_notification" :fieldLabel="__('servermanager::app.domain.expiryNotification')" fieldName="expiry_notification"
                                                  fieldValue="1" :checked="$domain->expiry_notification == 1">
                                </x-forms.checkbox>
                            </div>

                            <div class="col-lg-10" id="notification-settings" style="display: none;">
                                <div class="row">
                                    <div class="col-lg-4 col-md-6">
                                        <x-forms.number fieldId="notification_days_before" :fieldLabel="__('servermanager::app.domain.notificationDaysBefore')" fieldName="notification_days_before"
                                                        :fieldPlaceholder="__('servermanager::placeholders.notificationDaysBefore')" :fieldValue="$domain->notification_days_before ?? 30">
                                        </x-forms.number>
                                    </div>
                                    <div class="col-lg-4 col-md-6">
                                        <x-forms.select fieldId="notification_time_unit" :fieldLabel="__('servermanager::app.domain.notificationTimeUnit')" fieldName="notification_time_unit">
                                            <option value="days" {{ ($domain->notification_time_unit ?? 'days') == 'days' ? 'selected' : '' }}>@lang('servermanager::app.domain.days')</option>
                                            <option value="weeks" {{ ($domain->notification_time_unit ?? 'days') == 'weeks' ? 'selected' : '' }}>@lang('servermanager::app.domain.weeks')</option>
                                            <option value="months" {{ ($domain->notification_time_unit ?? 'days') == 'months' ? 'selected' : '' }}>@lang('servermanager::app.domain.months')</option>
                                        </x-forms.select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <x-forms.textarea fieldId="notes" :fieldLabel="__('servermanager::app.domain.description')" fieldName="notes"
                                                  :fieldPlaceholder="__('servermanager::placeholders.notes')" :fieldValue="$domain->notes">
                                </x-forms.textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <x-form-actions>
                    <x-forms.button-primary id="save-domain-form" class="mr-3" icon="check">@lang('app.update')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('domain.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.custom-date-picker').each(function(ind, el) {
            datepicker(el, {
                position: 'bl',
                ...datepickerConfig
            });
        });

        datepicker('#registration_date', {
            position: 'bl',
            @if (!is_null($domain->registration_date))
            dateSelected: new Date("{{ str_replace('-', '/', $domain->registration_date) }}"),
            @endif
            ...datepickerConfig
        });

        datepicker('#expiry_date', {
            position: 'bl',
            @if (!is_null($domain->expiry_date))
            dateSelected: new Date("{{ str_replace('-', '/', $domain->expiry_date) }}"),
            @endif
            ...datepickerConfig
        });

        $('#domain-provider-setting-add').click(function() {
            const url = "{{ route('provider.create') }}?model=true";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#save-domain-form').click(function() {
            const url = "{{ route('domain.update', $domain->id) }}";

            $.easyAjax({
                url: url,
                container: '#save-domain-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-domain-form",
                data: $('#save-domain-data-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        if ($(MODAL_XL).hasClass('show')) {
                            $(MODAL_XL).modal('hide');
                            window.location.reload();
                        } else {
                            window.location.href = response.redirectUrl;
                        }
                    }
                }
            });
        });

        // Handle expiry notification checkbox
        $('#expiry_notification').change(function() {
            if ($(this).is(':checked')) {
                $('#notification-settings').show();
            } else {
                $('#notification-settings').hide();
            }
        });

        // Show notification settings if checkbox is already checked
        if ($('#expiry_notification').is(':checked')) {
            $('#notification-settings').show();
        }

        // Password toggle functionality
        $('#toggle-registrar-password').click(function() {
            const passwordField = $('#registrar_password');
            const eyeIcon = $('#registrar-password-eye-icon');

            if (passwordField.attr('type') === 'password') {
                passwordField.attr('type', 'text');
                eyeIcon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                passwordField.attr('type', 'password');
                eyeIcon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        $('#toggle-password').click(function() {
            const passwordField = $('#password');
            const eyeIcon = $('#password-eye-icon');

            if (passwordField.attr('type') === 'password') {
                passwordField.attr('type', 'text');
                eyeIcon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                passwordField.attr('type', 'password');
                eyeIcon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

                // Provider selection handler
        $('#domain_provider').change(function() {
            const selectedProviderId = $(this).val();
            const providerUrlField = $('#provider_url');

            if (selectedProviderId) {
                // Find the selected option and get the provider URL
                const selectedOption = $(this).find('option:selected');
                const providerUrl = selectedOption.data('url');

                if (providerUrl) {
                    providerUrlField.val(providerUrl);
                } else {
                    // If no URL in data attribute, fetch from server
                    $.ajax({
                        url: "{{ route('server-manager.provider.get-url') }}",
                        type: 'GET',
                        data: { provider_id: selectedProviderId },
                        success: function(response) {
                            if (response.status === 'success' && response.url) {
                                providerUrlField.val(response.url);
                            }
                        },
                        error: function() {
                            console.log('Could not fetch provider URL');
                        }
                    });
                }
            } else {
                providerUrlField.val('');
            }
        });

        // Add Provider Button Click Handler
        $('#add-provider-btn').click(function() {
            $('#add-provider-modal').modal('show');
        });

        // Add Provider Form Submit Handler
        $('#add-provider-form').submit(function(e) {
            e.preventDefault();

            const formData = $(this).serialize();
            const saveBtn = $('#save-provider-btn');
            const originalText = saveBtn.html();

            saveBtn.html('<i class="fa fa-spinner fa-spin mr-1"></i>@lang("app.saving")');
            saveBtn.prop('disabled', true);

            $.ajax({
                url: "{{ route('provider.store') }}",
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status === 'success') {
                        // Add new provider to dropdown
                        const newOption = new Option(response.data.name, response.data.id, false, true);
                        newOption.setAttribute('data-url', response.data.url);
                        $('#domain_provider').append(newOption);

                        // Select the new provider
                        $('#domain_provider').val(response.data.id).trigger('change');

                        // Close modal and reset form
                        $('#add-provider-modal').modal('hide');
                        $('#add-provider-form')[0].reset();

                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            text: response.message,
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = '@lang("messages.somethingWentWrong")';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        text: errorMessage,
                        toast: true,
                        position: 'top-end',
                        timer: 3000,
                        showConfirmButton: false
                    });
                },
                complete: function() {
                    saveBtn.html(originalText);
                    saveBtn.prop('disabled', false);
                }
            });
        });

        // Reset form when modal is closed
        $('#add-provider-modal').on('hidden.bs.modal', function() {
            $('#add-provider-form')[0].reset();
        });

        init(RIGHT_MODAL);
    });
</script>
