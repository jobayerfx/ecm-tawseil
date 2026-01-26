@php
    $purchaseViewVendorPermission = user()->permission('view_vendor');
    $purchaseViewOrderPermission = user()->permission('view_purchase_order');
    $purchaseViewBillPermission = user()->permission('view_bill');
    $purchaseViewCreditPermission = user()->permission('view_vendor_credit');
    $purchaseViewInventoryPermission = user()->permission('view_inventory');
    $purchaseViewOrderReportPermission = user()->permission('view_order_report');
    $purchaseViewPaymentPermission = user()->permission('view_vendor_payment');
@endphp

<div class="col-lg-12 col-md-12 ntfcn-tab-content-left border-left-grey" style="padding-top:20px;">
    <h4 class="f-16  f-w-500 text-dark-grey">@lang("purchase::app.menu.digitalSignatureSettings")</h4>
    <input type="hidden" name="id" value="{{ $digitalSignatureSettings->id }}" />
    <div class="col-lg-12 mt-5">
        <x-forms.file allowedFileExtensions="png jpg jpeg svg bmp" class="mr-0 mr-lg-2 mr-md-2"
                        :fieldLabel="__('modules.invoiceSettings.authorisedSignatorySignature')"
                        fieldName="signature" fieldId="signature" :fieldValue="$digitalSignatureSettings->authorised_signature_url"
                        />
    </div>

    <div class="col-lg-12">
        <div class="row">
            @if($purchaseViewOrderPermission != 'none' && $purchaseViewOrderPermission != '')
                <div class="col-lg-4 my-3">
                    <x-forms.checkbox :checked="$digitalSignatureSettings->signature_in_purchase_order"
                                    :fieldLabel="__('purchase::app.menu.purchaseOrder')"
                                    fieldName="signature_in_purchase_order" fieldId="signature_in_purchase_order"
                                    fieldValue="true"/>
                </div>
            @endif

            @if($purchaseViewBillPermission != 'none' && $purchaseViewBillPermission != '')
                <div class="col-lg-4 my-3">
                    <x-forms.checkbox :checked="$digitalSignatureSettings->signature_in_bills"
                                    :fieldLabel="__('purchase::app.menu.bills')"
                                    fieldName="signature_in_bills" fieldId="signature_in_bills"
                                    fieldValue="true"/>
                </div>
            @endif

            @if($purchaseViewPaymentPermission != 'none' && $purchaseViewPaymentPermission != '')
                <div class="col-lg-4 my-3">
                    <x-forms.checkbox :checked="$digitalSignatureSettings->signature_in_vendor_payments"
                                    :fieldLabel="__('purchase::app.purchaseOrder.vendorPayments')"
                                    fieldName="signature_in_vendor_payments" fieldId="signature_in_vendor_payments"
                                    fieldValue="true"/>
                </div>
            @endif

            @if($purchaseViewInventoryPermission != 'none' && $purchaseViewInventoryPermission != '')
                <div class="col-lg-4 my-3">
                    <x-forms.checkbox :checked="$digitalSignatureSettings->signature_in_inventory"
                                    :fieldLabel="__('purchase::app.menu.inventory')"
                                    fieldName="signature_in_inventory" fieldId="signature_in_inventory"
                                    fieldValue="true"/>
                </div>
            @endif

            @if($purchaseViewCreditPermission != 'none' && $purchaseViewCreditPermission != '')
                <div class="col-lg-4 my-3">
                    <x-forms.checkbox :checked="$digitalSignatureSettings->signature_in_vendor_credits"
                                    :fieldLabel="__('purchase::app.menu.vendorCredits')"
                                    fieldName="signature_in_vendor_credits" fieldId="signature_in_vendor_credits"
                                    fieldValue="true"/>
                </div>
            @endif
        </div>
    </div>

</div>

<!-- Buttons Start -->
<div class="w-100 border-top-grey set-btns">
    <x-setting-form-actions>
        <x-forms.button-primary id="save-purchase-signature-form" class="mr-3" icon="check">@lang('app.save')
        </x-forms.button-primary>

    </x-setting-form-actions>
</div>
<!-- Buttons End -->

<script>

    function submitForm() {

        const url = "{{ route('purchase-signature-settings.update', $digitalSignatureSettings->id) }}";

        $.easyAjax({
            url: url,
            container: '#editSettings',
            type: "POST",
            redirect: true,
            file: true,
            data: $('#editSettings').serialize(),
            disableButton: true,
            blockUI: true,
            buttonSelector: "#save-purchase-signature-form",
        })
    }

    $('body').on('click', '#save-purchase-signature-form', function () {
        submitForm()
    });

</script>

