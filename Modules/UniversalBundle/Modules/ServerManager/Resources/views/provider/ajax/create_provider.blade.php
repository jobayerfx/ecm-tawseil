<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">@lang('servermanager::app.provider.addProvider')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">×</span>
    </button>
</div>
<div class="modal-body">
    <x-form id="save-provider-data-form">
        <div class="add-client bg-white rounded">
            <div class="row p-20">
                <div class="col-lg-6 col-md-6">
                    <x-forms.text
                        fieldId="provider_name"
                        :fieldLabel="__('servermanager::app.provider.name')"
                        fieldName="name"
                        fieldRequired="true"
                        :fieldPlaceholder="__('servermanager::placeholders.providerName')">
                    </x-forms.text>
                </div>
                <div class="col-lg-6 col-md-6">
                    <x-forms.text
                        fieldId="provider_url"
                        :fieldLabel="__('servermanager::app.provider.url')"
                        fieldName="url"
                        fieldRequired="true"
                        :fieldPlaceholder="__('servermanager::placeholders.providerUrl')">
                    </x-forms.text>
                </div>
                <div class="col-lg-6 col-md-6">
                    <x-forms.select
                        fieldId="provider_type"
                        :fieldLabel="__('servermanager::app.provider.type')"
                        fieldName="type"
                        fieldRequired="true">
                        <option value="">@lang('servermanager::placeholders.type')</option>
                        <option value="domain">@lang('servermanager::app.provider.domain')</option>
                        <option value="hosting">@lang('servermanager::app.provider.hosting')</option>
                        <option value="both">@lang('servermanager::app.provider.both')</option>
                    </x-forms.select>
                </div>
                <div class="col-lg-6 col-md-6">
                    <x-forms.select
                        fieldId="provider_status"
                        :fieldLabel="__('app.status')"
                        fieldName="status"
                        fieldRequired="true">
                        <option value="active">@lang('app.active')</option>
                        <option value="inactive">@lang('app.inactive')</option>
                    </x-forms.select>
                </div>
                <div class="col-md-12">
                    <x-forms.textarea
                        fieldId="provider_description"
                        :fieldLabel="__('app.description')"
                        fieldName="description"
                        :fieldPlaceholder="__('servermanager::placeholders.description')">
                    </x-forms.textarea>
                </div>
            </div>
        </div>
    </x-form>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-provider-form" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    $(document).ready(function () {
        $(".select-picker").selectpicker();

        $('#save-provider-form').click(function () {
            const url = "{{ route('provider.store') }}";
            $.easyAjax({
                url: url,
                container: '#save-provider-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-provider-form",
                data: $('#save-provider-data-form').serialize(),
                success: function (response) {
                    if (response.status === 'success') {
                        if (typeof showTable !== 'undefined' && typeof showTable === 'function') {
                            showTable();
                        }
                        if (response.data && response.data.redirectUrl) {
                            window.location.href = response.data.redirectUrl;
                        } else {
                            window.location.reload();
                        }
                    }
                }
            });
        });
    });
</script>