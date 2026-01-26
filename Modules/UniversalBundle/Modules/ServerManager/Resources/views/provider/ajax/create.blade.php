@php
    $addProviderPermission = user()->permission('add_provider');
@endphp

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-provider-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                    @lang('servermanager::app.provider.addProvider')</h4>
                <div class="row p-20">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-lg-6 col-md-6">
                                <x-forms.text fieldId="name" :fieldLabel="__('servermanager::app.provider.name')" fieldName="name"
                                              fieldRequired="true" :fieldPlaceholder="__('servermanager::placeholders.providerName')">
                                </x-forms.text>
                            </div>

                            <div class="col-lg-6 col-md-6">
                                <x-forms.text fieldId="url" :fieldLabel="__('servermanager::app.provider.url')" fieldName="url"
                                              fieldRequired="true" :fieldPlaceholder="__('servermanager::placeholders.providerUrl')">
                                </x-forms.text>
                            </div>

                            <div class="col-lg-6 col-md-6">
                                <x-forms.select fieldId="type" :fieldLabel="__('servermanager::app.provider.type')" fieldName="type" fieldRequired="true">
                                    <option value="">@lang('app.select')</option>
                                    <option value="domain">@lang('servermanager::app.provider.domain')</option>
                                    <option value="hosting">@lang('servermanager::app.provider.hosting')</option>
                                    <option value="both">@lang('servermanager::app.provider.both')</option>
                                </x-forms.select>
                            </div>

                            <div class="col-lg-6 col-md-6">
                                <x-forms.select fieldId="status" :fieldLabel="__('app.status')" fieldName="status" fieldRequired="true">
                                    <option value="">@lang('app.select')</option>
                                    <option value="active">@lang('app.active')</option>
                                    <option value="inactive">@lang('app.inactive')</option>
                                </x-forms.select>
                            </div>

                            <div class="col-lg-12">
                                <x-forms.textarea fieldId="description" :fieldLabel="__('app.description')" fieldName="description" :fieldPlaceholder="__('servermanager::placeholders.description')" fieldRows="3">
                                </x-forms.textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <x-form-actions>
                    <x-forms.button-primary id="save-provider" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('provider.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    $('#save-provider').click(function() {
        var url = "{{ route('provider.store') }}";
        $.easyAjax({
            url: url,
            container: '#save-provider-data-form',
            type: "POST",
            disableButton: true,
            blockUI: true,
            buttonSelector: "#save-provider",
            data: $('#save-provider-data-form').serialize(),
            success: function(response) {
                if (response.status == 'success') {
                    if (response.redirectUrl) {
                        window.location.href = response.redirectUrl;
                    } else {
                        $(MODAL_LG).modal('hide');
                        showTable();
                    }
                }
            }
        })
    });

    init(RIGHT_MODAL);
</script>
