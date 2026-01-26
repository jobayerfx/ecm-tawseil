@php
    $addProviderPermission = user()->permission('add_provider');
@endphp

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-hosting-data-form">
            @include('sections.password-autocomplete-hide')

            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                    @lang('servermanager::app.hosting.addHosting')</h4>
                <div class="row p-20">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-lg-3 col-md-6">
                                <x-forms.text fieldId="name" :fieldLabel="__('servermanager::app.hosting.name')" fieldName="name"
                                              fieldRequired="true" :fieldPlaceholder="__('servermanager::placeholders.hostingName')">
                                </x-forms.text>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <div class="form-group my-3">
                                    <label for="hosting_provider" class="f-14 text-dark-grey mb-12 text-capitalize">
                                        @lang('servermanager::app.hosting.provider') <span class="text-danger">*</span>
                                    </label>
                                    <x-forms.input-group>
                                        <select class="form-control select-picker" id="hosting_provider" name="hosting_provider" required data-live-search="true" data-size="8">
                                            <option value="" disabled selected>--</option>
                                            @foreach($providers as $provider)
                                                <option value="{{ $provider->id }}" data-url="{{ $provider->url }}">{{ $provider->name }}</option>
                                            @endforeach
                                        </select>
                                        @if ($addProviderPermission == 'all')
                                        <x-slot name="append">
                                            <button id="hosting-provider-setting-add" type="button"
                                                class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                        </x-slot>
                                        @endif
                                    </x-forms.input-group>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-forms.text fieldId="provider_url" :fieldLabel="__('servermanager::app.hosting.providerUrl')" fieldName="provider_url"
                                              :fieldPlaceholder="__('servermanager::placeholders.providerUrl')">
                                </x-forms.text>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <div class="form-group my-3">
                                    <label for="server_type" class="f-14 text-dark-grey mb-12 text-capitalize">
                                        @lang('servermanager::app.hosting.serverType') <span class="text-danger">*</span>
                                    </label>
                                    <x-forms.input-group>
                                <select class="form-control select-picker" id="server_type" name="server_type" required data-live-search="true" data-size="8">
                                    <option value="" disabled selected>--</option>
                                    @foreach ($serverTypes as $serverType)
                                        <option value="{{ $serverType->id }}">{{ $serverType->name }}</option>
                                    @endforeach
                                </select>
                                </x-forms.input-group>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-forms.text fieldId="server_location" :fieldLabel="__('servermanager::app.hosting.serverLocation')" fieldName="server_location"
                                              :fieldPlaceholder="__('servermanager::placeholders.serverLocation')">
                                </x-forms.text>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-forms.text fieldId="ip_address" :fieldLabel="__('servermanager::app.hosting.ipAddress')" fieldName="ip_address"
                                              :fieldPlaceholder="__('servermanager::placeholders.ipAddress')">
                                </x-forms.text>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-forms.text fieldId="cpanel_url" :fieldLabel="__('servermanager::app.hosting.cpanelUrl')" fieldName="cpanel_url"
                                              :fieldPlaceholder="__('servermanager::placeholders.cpanelUrl')">
                                </x-forms.text>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-forms.text fieldId="username" :fieldLabel="__('servermanager::app.hosting.username')" fieldName="username"
                                              :fieldPlaceholder="__('servermanager::placeholders.username')">
                                </x-forms.text>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <div class="form-group my-3">
                                    <label for="password" class="f-14 text-dark-grey mb-12 text-capitalize">
                                        @lang('servermanager::app.hosting.password')
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control height-35 f-14"
                                               id="password" name="password"
                                               placeholder="@lang('servermanager::placeholders.password')">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary border-grey height-35"
                                                    type="button" id="toggle-password">
                                                <i class="fa fa-eye" id="password-eye-icon"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- <hr>@lang('app.billingInformation')<hr> --}}

                            {{-- <h4 class="mb-0 p-20 f-21 font-weight-normal  border-top-grey">
                                @lang('modules.client.companyDetails')</h4> --}}

                            <div class="col-lg-3 col-md-6">
                                <x-forms.datepicker fieldId="purchase_date" :fieldLabel="__('servermanager::app.hosting.purchaseDate')" fieldName="purchase_date"
                                                    fieldRequired="true" :fieldPlaceholder="__('servermanager::placeholders.purchaseDate')">
                                </x-forms.datepicker>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-forms.datepicker fieldId="renewal_date" :fieldLabel="__('servermanager::app.hosting.expiryDate')" fieldName="renewal_date"
                                                    fieldRequired="true" :fieldPlaceholder="__('servermanager::placeholders.expiryDate')">
                                </x-forms.datepicker>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-forms.select fieldId="billing_cycle" :fieldLabel="__('servermanager::app.hosting.plan')" fieldName="billing_cycle" fieldRequired="true">
                                    <option value="" disabled selected>@lang('servermanager::app.hosting.selectPlan')</option>
                                    <option value="monthly">@lang('servermanager::app.monthly')</option>
                                    <option value="quarterly">@lang('servermanager::app.quarterly')</option>
                                    <option value="semi_annually">@lang('servermanager::app.semiAnnually')</option>
                                    <option value="annually">@lang('servermanager::app.annually')</option>
                                    <option value="biennially">@lang('servermanager::app.biennially')</option>
                                </x-forms.select>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-forms.number fieldId="annual_cost" :fieldLabel="__('servermanager::app.hosting.price')" fieldName="annual_cost"
                                                :fieldPlaceholder="__('servermanager::placeholders.annualCost')" fieldValue="0">
                                </x-forms.number>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-forms.select fieldId="project" :fieldLabel="__('servermanager::app.hosting.project')" fieldName="project">
                                    <option value="">@lang('servermanager::app.hosting.selectProject')</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                                    @endforeach
                                </x-forms.select>
                            </div>

                            <div class="col-lg-3 col-md-6 my-3">
                                <x-client-selection-dropdown :clients="$clients" :fieldRequired="false" />
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <x-forms.select fieldId="status" :fieldLabel="__('app.status')" fieldName="status" fieldRequired="true">
                                    <option value="" disabled selected>@lang('app.selectStatus')</option>
                                    <option value="active">@lang('app.active')</option>
                                    <option value="inactive">@lang('app.inactive')</option>
                                    <option value="expired">@lang('servermanager::app.expired')</option>
                                    <option value="suspended">@lang('servermanager::app.suspended')</option>
                                    <option value="cancelled">@lang('servermanager::app.cancelled')</option>
                                    <option value="pending">@lang('servermanager::app.pending')</option>
                                </x-forms.select>
                            </div>

                            {{-- <div class="col-lg-6 col-md-6">
                                <x-forms.text fieldId="disk_space" :fieldLabel="__('servermanager::app.hosting.diskSpace')" fieldName="disk_space"
                                              :fieldPlaceholder="__('placeholders.diskSpace')">
                                </x-forms.text>
                            </div>

                            <div class="col-lg-6 col-md-6">
                                <x-forms.text fieldId="bandwidth" :fieldLabel="__('servermanager::app.hosting.bandwidth')" fieldName="bandwidth"
                                              :fieldPlaceholder="__('placeholders.bandwidth')">
                                </x-forms.text>
                            </div> --}}

                            {{-- <div class="col-lg-6 col-md-6">
                                <x-forms.text fieldId="control_panel" :fieldLabel="__('servermanager::app.hosting.controlPanel')" fieldName="control_panel"
                                              :fieldPlaceholder="__('placeholders.controlPanel')">
                                </x-forms.text>
                            </div> --}}


                            {{-- <div class="col-lg-6 col-md-6">
                                <x-forms.number fieldId="database_limit" :fieldLabel="__('servermanager::app.hosting.databaseLimit')" fieldName="database_limit"
                                                :fieldPlaceholder="__('placeholders.databaseLimit')" fieldValue="0">
                                </x-forms.number>
                            </div>

                            <div class="col-lg-6 col-md-6">
                                <x-forms.number fieldId="email_limit" :fieldLabel="__('servermanager::app.hosting.emailLimit')" fieldName="email_limit"
                                                :fieldPlaceholder="__('placeholders.emailLimit')" fieldValue="0">
                                </x-forms.number>
                            </div> --}}

                        </div>

                        <div class="row">


                            <div class="col-lg-4 col-md-6">
                                <x-forms.text fieldId="ftp_username" :fieldLabel="__('servermanager::app.hosting.ftpUsername')" fieldName="ftp_username"
                                              :fieldPlaceholder="__('servermanager::placeholders.ftpUsername')">
                                </x-forms.text>
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <div class="form-group my-3">
                                    <label for="ftp_password" class="f-14 text-dark-grey mb-12 text-capitalize">
                                        @lang('servermanager::app.hosting.ftpPassword')
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control height-35 f-14"
                                               id="ftp_password" name="ftp_password"
                                               placeholder="@lang('servermanager::placeholders.ftpPassword')">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary border-grey height-35"
                                                    type="button" id="toggle-ftp-password">
                                                <i class="fa fa-eye" id="ftp-password-eye-icon"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <h5 class="mb-3">@lang('servermanager::app.hosting.sslSettings')</h5>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-2 col-md-6 my-3">
                                <x-forms.checkbox fieldId="ssl_certificate" :fieldLabel="__('servermanager::app.hosting.sslCertificate')" fieldName="ssl_certificate"
                                                  fieldValue="1">
                                </x-forms.checkbox>
                            </div>
                            <div class="col-lg-3 col-md-6" id="ssl-expiry-date" style="display: none;">
                                <x-forms.datepicker fieldId="ssl_expiry_date" :fieldLabel="__('servermanager::app.hosting.sslExpiryDate')" fieldName="ssl_expiry_date"
                                                    :fieldPlaceholder="__('servermanager::placeholders.sslExpiryDate')">
                                </x-forms.datepicker>
                            </div>
                            <div class="col-lg-3 col-md-6" id="ssl-type" style="display: none;">
                                <x-forms.select fieldId="ssl_type" :fieldLabel="__('servermanager::app.hosting.sslType')" fieldName="ssl_type">
                                    <option value="">@lang('app.select')</option>
                                    <option value="free">@lang('servermanager::app.hosting.freeSSL')</option>
                                    <option value="paid">@lang('servermanager::app.hosting.paidSSL')</option>
                                    <option value="wildcard">@lang('servermanager::app.hosting.wildcardSSL')</option>
                                    <option value="ev">@lang('servermanager::app.hosting.evSSL')</option>
                                </x-forms.select>
                            </div>
                        </div>

                        <div class="row" id="ssl-details" style="display: none;">
                            <div class="col-lg-8">
                                <x-forms.textarea fieldId="ssl_certificate_info" :fieldLabel="__('servermanager::app.hosting.sslCertificateInfo')" fieldName="ssl_certificate_info"
                                                  :fieldPlaceholder="__('servermanager::placeholders.sslCertificateInfo')">
                                </x-forms.textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-2 col-md-6 my-3">
                                <x-forms.checkbox fieldId="expiry_notification" :fieldLabel="__('servermanager::app.hosting.expiryNotification')" fieldName="expiry_notification"
                                                  fieldValue="1" checked="true">
                                </x-forms.checkbox>
                            </div>

                            <div class="col-lg-10" id="notification-settings" style="display: none;">
                                <div class="row">
                                    <div class="col-lg-4 col-md-6">
                                        <x-forms.number fieldId="notification_days_before" :fieldLabel="__('servermanager::app.hosting.notificationDaysBefore')" fieldName="notification_days_before"
                                                        :fieldPlaceholder="__('servermanager::placeholders.notificationDaysBefore')" fieldValue="7">
                                        </x-forms.number>
                                    </div>
                                    <div class="col-lg-4 col-md-6">
                                        <x-forms.select fieldId="notification_time_unit" :fieldLabel="__('servermanager::app.hosting.notificationTimeUnit')" fieldName="notification_time_unit">
                                            <option value="days">@lang('servermanager::app.hosting.days')</option>
                                            <option value="weeks">@lang('servermanager::app.hosting.weeks')</option>
                                            <option value="months">@lang('servermanager::app.hosting.months')</option>
                                        </x-forms.select>
                                    </div>
                                </div>
                            </div>

                            {{-- <div class="col-lg-6 col-md-6">
                                <x-forms.checkbox fieldId="backup_enabled" :fieldLabel="__('servermanager::app.hosting.backupEnabled')" fieldName="backup_enabled"
                                                  fieldValue="1">
                                </x-forms.checkbox>
                            </div> --}}

                            <div class="col-lg-12">
                                <x-forms.textarea fieldId="notes" :fieldLabel="__('servermanager::app.hosting.description')" fieldName="notes"
                                                  :fieldPlaceholder="__('servermanager::placeholders.notes')">
                                </x-forms.textarea>
                            </div>

                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-hosting-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('hosting.index')" class="border-0">@lang('app.cancel')
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

        datepicker('#purchase_date', {
            position: 'bl',
            ...datepickerConfig
        });

        datepicker('#renewal_date', {
            position: 'bl',
            ...datepickerConfig
        });

        $('#hosting-provider-setting-add').click(function() {
            const url = "{{ route('provider.create') }}?model=true";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#save-hosting-form').click(function() {
            const url = "{{ route('hosting.store') }}";

            $.easyAjax({
                url: url,
                container: '#save-hosting-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-hosting-form",
                data: $('#save-hosting-data-form').serialize(),
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

        // Handle SSL certificate checkbox
        $('#ssl_certificate').change(function() {
            if ($(this).is(':checked')) {
                $('#ssl-details').show();
                $('#ssl-expiry-date').show();
                $('#ssl-type').show();
            } else {
                $('#ssl-details').hide();
                $('#ssl-expiry-date').hide();
                $('#ssl-type').hide();
            }
        });

        // Show SSL details if checkbox is already checked
        if ($('#ssl_certificate').is(':checked')) {
            $('#ssl-details').show();
            $('#ssl-expiry-date').show();
            $('#ssl-type').show();
        }

        // Initialize SSL expiry date picker
        datepicker('#ssl_expiry_date', {
            position: 'bl',
            ...datepickerConfig
        });

        // Password toggle functionality
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

        $('#toggle-ftp-password').click(function() {
            const ftpPasswordField = $('#ftp_password');
            const eyeIcon = $('#ftp-password-eye-icon');

            if (ftpPasswordField.attr('type') === 'password') {
                ftpPasswordField.attr('type', 'text');
                eyeIcon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                ftpPasswordField.attr('type', 'password');
                eyeIcon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

                // Provider selection handler
        $('#hosting_provider').change(function() {
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
                        $('#hosting_provider').append(newOption);

                        // Select the new provider
                        $('#hosting_provider').val(response.data.id).trigger('change');

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
