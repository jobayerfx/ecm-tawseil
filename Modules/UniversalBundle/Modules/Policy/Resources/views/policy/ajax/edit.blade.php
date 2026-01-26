@php
$addProductPermission = user()->permission('add_product');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">

<!-- CREATE INVOICE START -->
<div class="bg-white rounded b-shadow-4 create-inv">
    <!-- HEADING START -->
    <div class="px-3 py-3 px-lg-4 px-md-4">
        <h4 class="mb-0 f-21 font-weight-normal text-capitalize">@lang('policy::app.policyCenter')</h4>
    </div>
    <!-- HEADING END -->
    <hr class="m-0 border-top-grey">
    <!-- FORM START -->
    <x-form class="c-inv-form" id="savePolicyForm" method="PUT">
        <!-- INVOICE NUMBER, DATE, DUE DATE, FREQUENCY START -->
        <div class="px-3 py-3 row px-lg-4 px-md-4">
            <input type="hidden" name="status" value="{{ $policy->status }}">
            <div class="col-md-4">
                <div class="mb-4 form-group">
                    <x-forms.text fieldId="title" :fieldLabel="__('app.title')" fieldName="title" fieldRequired="true" :fieldValue="$policy->title">
                    </x-forms.text>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-4 form-group">
                    <x-forms.datepicker fieldId="date"
                    :fieldLabel="__('policy::app.effectiveDate')"
                    fieldName="date"
                    fieldRequired="true"
                    :fieldPlaceholder="__('placeholders.date')"
                    :fieldValue="(($policy->date) ? $policy->date->format(company()->date_format) : '')">
                    </x-forms.text>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-4 form-group">
                    <x-forms.select fieldId="employee_department" :fieldLabel="__('app.department')"
                    fieldName="department[]" multiple data-size="5">
                        @foreach ($teams as $team)
                                <option {{ in_array($team->id, $departmentArray) ? 'selected' : '' }} value="{{ $team->id }}">
                                    {{ $team->team_name }}</option>
                        @endforeach
                    </x-forms.select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="mb-4 form-group">
                    <x-forms.select fieldId="employee_designation" :fieldLabel="__('app.designation')"
                    fieldName="designation[]" multiple data-size="5">
                    @foreach ($designations as $designation)
                        <option {{ in_array($designation->id, $designationArray) ? 'selected' : '' }} value="{{ $designation->id }}">
                            {{ $designation->name }}</option>
                    @endforeach
                    </x-forms.select>
                </div>
            </div>
            <div class="col-md-4">
                <x-forms.select fieldId="gender" :fieldLabel="__('modules.employees.gender')"
                    fieldName="gender">
                    <option value="">--</option>
                    <option @if ($policy->gender == 'male') selected @endif value="male">@lang('app.male')</option>
                    <option @if ($policy->gender == 'female') selected @endif value="female">@lang('app.female')</option>
                    <option @if ($policy->gender == 'others') selected @endif value="others">@lang('app.others')</option>
                </x-forms.select>
            </div>

            <div class="col-md-4">
                <div class="mb-4 form-group">
                    <x-forms.select fieldId="employment_type" :fieldLabel="__('modules.employees.employmentType')"
                    fieldName="employment_type[]" :fieldPlaceholder="__('placeholders.date')" multiple data-size="5">
                    <option value="full_time" @if (is_array($employmentTypeArray) && in_array('full_time', $employmentTypeArray)) selected @endif>
                        @lang('app.fullTime')</option>
                    <option value="part_time" @if (is_array($employmentTypeArray) && in_array('part_time', $employmentTypeArray)) selected @endif>
                        @lang('app.partTime')</option>
                    <option value="on_contract" @if (is_array($employmentTypeArray) && in_array('on_contract', $employmentTypeArray)) selected @endif>
                        @lang('app.onContract')</option>
                    <option value="internship" @if (is_array($employmentTypeArray) && in_array('internship', $employmentTypeArray)) selected @endif>
                        @lang('app.internship')</option>
                    <option value="trainee" @if (is_array($employmentTypeArray) && in_array('trainee', $employmentTypeArray)) selected @endif>@lang('app.trainee')
                    </option>
                    </x-forms.select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="mt-5 mb-4 form-group">
                    <x-forms.checkbox :checked="$policy->signature_required == 'yes'"
                        :fieldLabel="__('policy::app.signatureRequired')"
                        fieldName="signature_required"
                        fieldId="signature_required" fieldRequired="true"/>
                </div>
            </div>

            @if ($policy->status == 'draft')
                <div class="col-md-4">
                    <div class="mt-2 form-group">
                        <div class="d-flex">
                            <x-forms.radio fieldId="show-description" :fieldLabel="__('app.add') .' '.__('policy::app.policyDescription')"
                                :checked="($policy->description || ($policy->description == null && $policy->file == null))" fieldName="type" fieldValue="description">
                            </x-forms.radio>
                            <x-forms.radio fieldId="show-file" :fieldLabel="__('policy::app.uploadPolicyFile')"
                            :checked="$policy->file" fieldName="type" fieldValue="file"></x-forms.radio>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 description-div @if(is_null($policy->description) || !is_null($policy->file)) d-none @endif">
                    <div class="mt-4 form-group">
                        <x-forms.label fieldId="description" :fieldLabel="__('policy::app.policyDescription')">
                        </x-forms.label>
                        <div id="description">{!! $policy->description !!}</div>
                        <textarea name="description" id="description-text" class="d-none"></textarea>
                    </div>
                </div>
            @endif
        </div>
        <!-- UPLOAD FILES START -->
        @if ($policy->status == 'draft')
            <div class="px-3 col-md-12 py-31 row px-lg-4 px-md-4 file-div @if(is_null($policy->file)) d-none @endif">
                <div class="col-md-12">
                    <input type="hidden" name="policyId" id="policyId">
                    <x-forms.file allowedFileExtensions="pdf" class="mr-0 mr-lg-2 mr-md-2 cropper" :fieldLabel="__('app.menu.addFile')"
                    fieldName="file" fieldId="policy-file-upload-dropzone" :fieldValue=" !is_null($policy->file) ? $policy->file_url : ''"/>
                </div>
            </div>
        @endif
        <!-- UPLOAD FILES END -->

        <!-- UPLOAD FILES START -->
        {{-- <div class="px-3 py-31 row px-lg-4 px-md-4 file-div {{is_null($policy->file) ? 'd-none' : ''}}">
            <div class="col-md-12">
                <x-forms.file allowedFileExtensions="pdf" class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.menu.addFile')"
                fieldName="file" fieldId="policy-file-upload-dropzone" :fieldValue=" !is_null($policy->file) ? $policy->file_url : ''"/>
            </div>
        </div> --}}
        <!-- UPLOAD FILES END -->


         <!-- CANCEL SAVE SEND START -->
         <x-form-actions class="c-inv-btns d-block d-lg-flex d-md-flex">

            @if ($policy->status == 'published')
                <x-forms.button-primary class="mr-3 save-form" data-type="save" icon="check">@lang('app.save')
                </x-forms.button-primary>
            @else
                <div class="d-flex mb-3 mb-lg-0 mb-md-0">
                    <div class="inv-action dropup mr-3">
                        <button class="btn-primary dropdown-toggle" type="button" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            @lang('app.save')
                            <span><i class="fa fa-chevron-up f-15 text-white"></i></span>
                        </button>
                        <!-- DROPDOWN - INFORMATION -->
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuBtn" tabindex="0">
                            <li>
                                <a class="dropdown-item f-14 text-dark save-form" href="javascript:;" data-type="save">
                                    <i class="fa fa-save f-w-500 mr-2 f-11"></i> @lang('policy::app.saveAsDraft')
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item f-14 text-dark save-form" href="javascript:void(0);"
                                    data-type="send">
                                    <i class="fa fa-paper-plane f-w-500  mr-2 f-12"></i> @lang('policy::app.saveAndPublished')
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            @endif

            <x-forms.button-cancel :link="route('policy.index')" class="border-0 ">@lang('app.cancel')
            </x-forms.button-cancel>

        </x-form-actions>
        <!-- CANCEL SAVE SEND END -->

    </x-form>
    <!-- FORM END -->
</div>
<!-- CREATE INVOICE END -->

<script src="{{ asset('vendor/jquery/dropzone.min.js') }}"></script>
<script>
    $(document).ready(function() {

        $("#employee_department").selectpicker({
            actionsBox: true,
            selectAllText: "{{ __('modules.permission.selectAll') }}",
            deselectAllText: "{{ __('modules.permission.deselectAll') }}",
            multipleSeparator: ", ",
            selectedTextFormat: "count > 8",
            countSelectedText: function (selected, total) {
                return selected + " {{ __('app.membersSelected') }} ";
            }
        });

        $("#employee_designation").selectpicker({
            actionsBox: true,
            selectAllText: "{{ __('modules.permission.selectAll') }}",
            deselectAllText: "{{ __('modules.permission.deselectAll') }}",
            multipleSeparator: ", ",
            selectedTextFormat: "count > 8",
            countSelectedText: function (selected, total) {
                return selected + " {{ __('app.membersSelected') }} ";
            }
        });

        $("#employment_type").selectpicker({
            actionsBox: true,
            selectAllText: "{{ __('modules.permission.selectAll') }}",
            deselectAllText: "{{ __('modules.permission.deselectAll') }}",
            multipleSeparator: ", ",
            selectedTextFormat: "count > 8",
            countSelectedText: function (selected, total) {
                return selected + " {{ __('app.membersSelected') }} ";
            }
        });

        @if ($policy->status == 'draft')
            quillMention(null, '#description');
        @endif

        const dp1 = datepicker('#date', {
            position: 'bl',
            ...datepickerConfig
        });


        $('.save-form').click(function() {
            var saveAs = $(this).data('type');
            let policyStatus = '{{ $policy->status }}';
            const url = "{{ route('policy.update', $policy->id) }}" + "?saveAs=" + saveAs;

            if (policyStatus == 'draft') {
                var description = document.getElementById('description').children[0].innerHTML;
                var content = description.replace(/<\/?[^>]+(>|$)/g, "");
                content == '' ? description = '' : document.getElementById('description-text').value = description;
            }

            $.easyAjax({
                url: url,
                container: '#savePolicyForm',
                type: "POST",
                disableButton: true,
                blockUI: true,
                file: true,
                buttonSelector: "#save-form",
                data: $('#savePolicyForm').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        window.location.href = response.redirectUrl;
                    }
                }
            })
        });

        $('body').on('click', '.delete-file', function() {
                var id = $(this).data('row-id');
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
                        var url = "{{ route('policy-file.destroy', ':id') }}";
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
                                    $('#policy-file-list').html(response.view);
                                }
                            }
                        });
                    }
                });
        });

        $('#show-file').click(function(){
            $('.file-div').removeClass('d-none');
            $('.description-div').addClass('d-none');
        })

        $('#show-description').click(function(){
            $('.file-div').addClass('d-none');
            $('.description-div').removeClass('d-none');
        })

        init(RIGHT_MODAL);
    });

</script>
