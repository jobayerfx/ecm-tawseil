@php
    $addPermission = user()->permission('add_footer_link');
    $editPermission = user()->permission('edit_footer_link');
    $deletePermission = user()->permission('delete_footer_link');
@endphp

<div class="table-responsive p-20">
    <div id="table-actions" class="d-block d-lg-flex align-items-center">

        @if ($addPermission == 'all')
            <x-forms.button-primary icon="plus" id="addField" class="mb-2">
                @lang('app.add') @lang('recruit::modules.setting.field')
            </x-forms.button-primary>
        @endif

    </div>
    <x-table class="table-bordered">
        <x-slot name="thead">
            <th>@lang('modules.tasks.category')</th>
            <th>@lang('modules.customFields.moduleLabel')</th>
            <th>@lang('modules.customFields.type')</th>
            <th>@lang('modules.customFields.values')</th>
            <th>@lang('modules.customFields.required')</th>
            <th>@lang('modules.customFields.showInTable')</th>
            <th>@lang('modules.customFields.export')</th>
            <th>@lang('app.action')</th>
        </x-slot>
        @forelse($customFields as $field)
            <tr class="row{{ $field->id }}">
                <td>{{ $field->fieldGroup->name }}</td>
                <td>{{ $field->label }}</td>
                <td>{{ $field->type }}</td>
                <td>
                    @if(isset($field->values) && $field->values != '[null]')
                        <ul class="value-list">
                            @foreach(json_decode($field->values) as $value)
                                <li>{{ $value }}</li>
                            @endforeach
                        </ul>
                    @else
                        --
                    @endif
                </td>
                <td>
                    @if($field->required === 'yes')
                        <span class="badge badge-danger disabled color-palette">@lang('app.yes')</span>
                    @else
                        <span class="badge badge-secondary disabled color-palette">@lang('app.no')</span>
                    @endif
                </td>
                <td>
                    @if($field->visible == 'true')
                        <span class="badge badge-danger disabled color-palette">@lang('app.yes')</span>
                    @else
                        <span class="badge badge-secondary disabled color-palette">@lang('app.no')</span>
                    @endif
                </td>
                <td>
                    @if($field->export == 1)
                        <span class="badge badge-danger disabled color-palette">@lang('app.yes')</span>
                    @else
                        <span class="badge badge-secondary disabled color-palette">@lang('app.no')</span>
                    @endif
                </td>

                <td>
                    <div class="task_view">
                        <a data-user-id="{{ $field->id }}" class="task_view_more d-flex align-items-center justify-content-center edit-custom-field" href="javascript:;">
                            <i class="fa fa-edit icons mr-2"></i> {{ __('app.edit') }}
                        </a>
                    </div>
                    <div class="task_view">
                        <a data-user-id="{{ $field->id }}" data-module="{{ $field->custom_field_group_id }}" class="task_view_more d-flex align-items-center justify-content-center sa-params" href="javascript:;">
                            <i class="fa fa-trash icons mr-2"></i> {{ __('app.delete') }}
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7">
                    <x-cards.no-record icon="list" :message="__('messages.noCustomField')" />
                </td>
            </tr>
        @endforelse
    </x-table>
</div>

<script>

    /* open add agent modal */
    $('body').off('click', "#addField").on('click', '#addField', function () {
        var url = "{{ route('custom-field-settings.create') }}";
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

    $(function () {

    // Hide all custom field tables initially
    $('.recruit-custom-fields-table').hide();

        // Toggle visibility of the custom fields table on module header click
        $('.module-header').click(function() {
            var module = $(this).data('module');
            var table = $('.custom-fields-table[data-module="' + module + '"]');
            table.toggle();
        });

        $('body').on('click', '.sa-params', function () {
            const id = $(this).data('user-id');
            var module = $(this).data('module');

            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.deleteField')",
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

                    let url = "{{ route('custom-field-settings.destroy',':id') }}";
                    url = url.replace(':id', id);

                    const token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        blockUI: true,
                        data: {'_token': token, '_method': 'DELETE'},
                        success: function (response) {
                            if (response.status == "success") {
                                $('.row'+id).fadeOut();
                                const updatedCount = response.updatedCount;
                                $('#moduleCount' + module).html(updatedCount);
                                if (updatedCount == 0) {
                                    $('#removeModule' + module).fadeOut().remove();
                                    $('#removeModuleColumns' + module).fadeOut().remove();
                                }
                            }
                        }
                    });
                }
            });
        });

    });

    function updateFieldCount(module) {
        let fieldCount = $('.custom-fields-table[data-module="' + module + '"] tr').length - 1;
        let fieldText = fieldCount === 1 ? '@lang('modules.customFields.field')' : '@lang('modules.customFields.fields')';
        $('.module-header[data-module="' + module + '"]').siblings('.heading-h4').find('.simple-text').text(fieldCount + ' ' + fieldText);
    }

    $('body').on('click', '#add-field', function () {
        const url = "{{ route('custom-field-settings.create')}}";
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

    $('body').on('click', '.edit-custom-field', function () {
        const id = $(this).data('user-id');
        let url = "{{ route('custom-field-settings.edit',':id') }}";
        url = url.replace(':id', id);
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

</script>

