<x-form id="save-maintenance-form">
    <div class="add-client bg-white rounded">
        <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
            @lang('asset::app.addMaintenance')</h4>
        <div class="row p-20">
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-md-6">
                        <x-forms.select fieldId="asset_id" :fieldLabel="__('asset::app.assetName')" fieldName="asset_id"
                                        fieldRequired="true" search="true">
                            <option value="">--</option>
                            @foreach ($assets as $asset)
                                <option value="{{ $asset->id }}">{{ $asset->name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-6">
                        <x-forms.select fieldId="type" :fieldLabel="__('asset::app.maintenanceType')" fieldName="type"
                                        fieldRequired="true">
                            <option value="planned">@lang('asset::app.planned')</option>
                            <option value="reactive">@lang('asset::app.reactive')</option>
                        </x-forms.select>
                    </div>

                    <div class="col-md-12">
                        <x-forms.text fieldId="title" :fieldLabel="__('asset::app.maintenanceTitle')" fieldName="title"
                                      fieldRequired="true" :fieldPlaceholder="__('asset::app.maintenanceTitle')">
                        </x-forms.text>
                    </div>

                    <div class="col-md-12">
                        <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('asset::app.description')"
                                          fieldName="description" fieldId="description" :fieldPlaceholder="__('asset::app.description')">
                        </x-forms.textarea>
                    </div>

                    <div class="col-md-4">
                        <x-forms.datepicker fieldId="scheduled_date" :fieldLabel="__('asset::app.scheduledDate')"
                                            fieldName="scheduled_date" fieldRequired="true" :fieldValue="now()->format(company()->date_format)"
                                            :fieldPlaceholder="__('placeholders.date')">
                        </x-forms.datepicker>
                    </div>

                    <div class="col-md-4">
                        <x-forms.datepicker fieldId="due_date" :fieldLabel="__('asset::app.dueDate')"
                                            fieldName="due_date" :fieldValue="''"
                                            :fieldPlaceholder="__('placeholders.date')">
                        </x-forms.datepicker>
                    </div>

                    <div class="col-md-4">
                        <x-forms.select fieldId="assigned_to" :fieldLabel="__('asset::app.assignedTo')" fieldName="assigned_to"
                                        search="true">
                            <option value="">--</option>
                            @foreach ($employees as $employee)
                                <x-user-option :user="$employee"></x-user-option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-4">
                        <x-forms.select fieldId="status" :fieldLabel="__('asset::app.status')" fieldName="status"
                                        fieldRequired="true">
                            <option value="scheduled" selected>@lang('asset::app.scheduled')</option>
                            <option value="inprogress">@lang('asset::app.inprogress')</option>
                            <option value="completed">@lang('asset::app.completed')</option>
                            <option value="overdue">@lang('asset::app.overdue')</option>
                            <option value="cancelled">@lang('asset::app.cancelled')</option>
                        </x-forms.select>
                    </div>

                    <div class="col-md-12">
                        <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('asset::app.notes')"
                                          fieldName="notes" fieldId="notes" :fieldPlaceholder="__('asset::app.notes')">
                        </x-forms.textarea>
                    </div>
                </div>
            </div>
        </div>

        <x-form-actions>
            <x-forms.button-primary id="save-maintenance" class="mr-3" icon="check">@lang('app.save')
            </x-forms.button-primary>
            <x-forms.button-cancel :link="route('asset-maintenance.index')" class="border-0">@lang('app.cancel')
            </x-forms.button-cancel>
        </x-form-actions>
    </div>
</x-form>

<script>
    $(document).ready(function() {
        $(".select-picker").selectpicker();
        
        datepicker('#scheduled_date', {
            position: 'bl',
            ...datepickerConfig
        });

        datepicker('#due_date', {
            position: 'bl',
            ...datepickerConfig
        });
        
        $('#save-maintenance').click(function() {
            // Ensure selectpicker values are included in form serialization
            $('.select-picker').selectpicker('refresh');
            
            var url = "{{ route('asset-maintenance.store') }}";
            $.easyAjax({
                url: url,
                container: '#save-maintenance-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-maintenance",
                data: $('#save-maintenance-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        if (response.redirectUrl) {
                            window.location.href = response.redirectUrl;
                        } else {
                            if ($(MODAL_XL).hasClass('show')) {
                                $(MODAL_XL).modal('hide');
                            }
                            if (typeof window.LaravelDataTables !== 'undefined' && window.LaravelDataTables["asset-maintenance-table"]) {
                                window.LaravelDataTables["asset-maintenance-table"].draw(true);
                            } else {
                                window.location.href = "{{ route('asset-maintenance.index') }}";
                            }
                        }
                    }
                }
            });
        });
        
        init(RIGHT_MODAL);
    });
</script>

