@php
$addProductPermission = user()->permission('add_product');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">

<div class="bg-white rounded b-shadow-4 create-inv">
    <!-- HEADING START -->
    <div class="px-3 py-3 px-lg-4 px-md-4">
        <h4 class="mb-0 f-21 font-weight-normal text-capitalize">@lang('policy::app.policyCenter')</h4>
    </div>
    <!-- HEADING END -->
    <hr class="m-0 border-top-grey">
    <!-- FORM START -->
    <x-form class="c-inv-form" id="savePolicyForm">
        <div class="px-3 py-3 row px-lg-4 px-md-4">
            <div class="col-md-4">
                <x-forms.text fieldId="title" :fieldLabel="__('app.title')" fieldRequired="true" fieldName="title" />
            </div>
            <div class="col-md-4">
                <x-forms.text fieldId="date" :fieldLabel="__('policy::app.effectiveDate')" fieldRequired="true" fieldName="date" />
            </div>
            <div class="col-md-4">
                <x-forms.select fieldId="employee_department" :fieldLabel="__('app.department')"
                    fieldName="department[]" multiple data-size="5" search="true">
                    @foreach ($teams as $team)
                            <option value="{{ $team->id }}">{{ $team->team_name }}</option>
                    @endforeach
                </x-forms.select>
            </div>

            <div class="col-md-4">
                <div class="mb-4 form-group">
                    <x-forms.select fieldId="employee_designation" :fieldLabel="__('app.designation')"
                    fieldName="designation[]" multiple data-size="5">
                    @foreach ($designations as $designation)
                        <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                    @endforeach
                    </x-forms.select>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <x-forms.select fieldId="gender" :fieldLabel="__('modules.employees.gender')"
                    fieldName="gender">
                    <option value="">--</option>
                    <option value="male">@lang('app.male')</option>
                    <option value="female">@lang('app.female')</option>
                    <option value="others">@lang('app.others')</option>
                </x-forms.select>
            </div>

            <div class="col-md-4">
                <div class="mb-4 form-group">
                    <x-forms.select fieldId="employment_type" :fieldLabel="__('modules.employees.employmentType')"
                    fieldName="employment_type[]" :fieldPlaceholder="__('placeholders.date')" multiple data-size="5">
                        <option value="full_time">@lang('app.fullTime')</option>
                        <option value="part_time">@lang('app.partTime')</option>
                        <option value="on_contract">@lang('app.onContract')</option>
                        <option value="internship">@lang('app.internship')</option>
                        <option value="trainee">@lang('app.trainee')</option>
                    </x-forms.select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="mt-5 mb-4 form-group">
                    <x-forms.checkbox :fieldLabel="__('policy::app.signatureRequired')"
                            fieldName="signature_required" fieldId="signature_required" fieldValue="yes" fieldRequired="true" />
                </div>
            </div>

            <div class="col-md-4 mt-3">
                <x-forms.checkbox :fieldLabel="__('modules.attendance.sendEmail')" fieldName="send_email" fieldId="sendEmail" :checked="true"  />
            </div>

            <div class="col-md-4">
                <div class="mt-2 form-group">
                    <div class="d-flex">
                        <x-forms.radio fieldId="show-description" :fieldLabel="__('app.add') .' '.__('policy::app.policyDescription')"
                            checked="true" fieldName="type" fieldValue="description">
                        </x-forms.radio>
                        <x-forms.radio fieldId="show-file" :fieldLabel="__('policy::app.uploadPolicyFile')"
                            fieldName="type" fieldValue="file"></x-forms.radio>
                    </div>
                </div>
            </div>

            <div class="col-md-12 description-div">
                <div class="mt-4 form-group">
                    <x-forms.label fieldId="description" :fieldLabel="__('policy::app.policyDescription')">
                    </x-forms.label>
                    <div id="description"></div>
                    <textarea name="description" id="description-text" class="d-none"></textarea>
                </div>
            </div>
        </div>
        <!-- UPLOAD FILES START -->
        <div class="px-3 col-md-12 py-31 row px-lg-4 px-md-4 d-none file-div">
            <div class="col-md-12">
                <input type="hidden" name="policyId" id="policyId">
                <x-forms.file allowedFileExtensions="pdf" class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.menu.addFile')"
                fieldName="file" fieldId="policy-file-upload-dropzone"/>
            </div>
        </div>
        <!-- UPLOAD FILES END -->


         <!-- CANCEL SAVE SEND START -->
         <x-form-actions class="c-inv-btns d-block d-lg-flex d-md-flex">
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
            <x-forms.button-cancel :link="route('policy.index')" class="border-0 ">@lang('app.cancel')
            </x-forms.button-cancel>

        </x-form-actions>
        <!-- CANCEL SAVE SEND END -->

    </x-form>
    <!-- FORM END -->
</div>

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
            multipleSeparator: ",",
            selectedTextFormat: "count > 8",
            countSelectedText: function (selected, total) {
                return selected + " {{ __('app.membersSelected') }} ";
            }
        });

        quillMention(null, '#description');

        const dp1 = datepicker('#date', {
            position: 'bl',
            ...datepickerConfig
        });

        $('.save-form').click(function() {
            var description = document.getElementById('description').children[0].innerHTML;
            var content = description.replace(/<\/?[^>]+(>|$)/g, "");
            content == '' ? description = '' : document.getElementById('description-text').value = description;

            var saveAs = $(this).data('type');
            const url = "{{ route('policy.store') }}" + "?saveAs=" + saveAs;

            $.easyAjax({
                url: url,
                container: '#savePolicyForm',
                type: "POST",
                disableButton: true,
                file:true,
                blockUI: true,
                buttonSelector: "#save-form",
                data: $('#savePolicyForm').serialize(),
                success: function(response) {
                    if (response.status === 'success') {
                        window.location.href = response.redirectUrl;
                    }
                }
            })
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
