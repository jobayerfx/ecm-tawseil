<!DOCTYPE html>
<!--
  Invoice template by invoicebus.com
  To customize this template consider following this guide https://invoicebus.com/how-to-create-invoice-template/
  This template is under Invoicebus Template License, see https://invoicebus.com/templates/license/
-->
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>@lang('app.proposal')</title>
    @includeIf('invoices.pdf.invoice_pdf_css')
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Invoice">
</head>
<body>
<div id="container" class="descriptionFont">
    <div class="invoice-top">
        <section id="memo">
            <div class="logo">
                <img src="{{ $invoiceSetting->logo_url }}" alt="{{ $company->company_name }}"/>

            </div>

            <div class="company-info descriptionFont">
                <span class="company-name descriptionFont">
                    {{ $company->company_name }}
                </span>

                <span class="spacer"></span>

                <div>{!! nl2br($company->defaultAddress->address) !!}</div>


                <span class="clearfix"></span>

                <div>{{ $company->company_phone }}</div>

                <span class="clearfix"></span>

                @if($invoiceSetting->show_gst == 'yes' && !is_null($invoiceSetting->gst_number))
                    <div>@lang('app.gstIn'): {{ $invoiceSetting->gst_number }}</div>
                @endif
            </div>

        </section>

        <section id="invoice-info" class="descriptionFont">
            <table class="descriptionFont">
                <tr>
                    <td>@lang('app.status'):</td>
                    <td>{{ $proposal->status }}</td>
                </tr>
                <tr>
                    <td>@lang('modules.estimates.validTill'):</td>
                    <td>{{ $proposal->valid_till->translatedFormat($company->date_format) }}</td>
                </tr>
                <tr>
                    <td>@lang('app.date'):</td>
                    <td>{{ $proposal->created_at->translatedFormat($company->date_format) }}</td>
                </tr>
            </table>


            <div class="clearfix"></div>

            <section id="invoice-title-number">

                <span id="number">{{ $proposal->proposal_number }}</span>

            </section>
        </section>

        @if ($proposal->lead->contact && ($proposal->lead->contact->client_name || $proposal->lead->contact->client_email || $proposal->lead->contact->mobile || $proposal->lead->contact->company_name || $proposal->lead->contact->address) && ($invoiceSetting->show_client_name == 'yes' || $invoiceSetting->show_client_email == 'yes' || $invoiceSetting->show_client_phone == 'yes' || $invoiceSetting->show_client_company_name == 'yes' || $invoiceSetting->show_client_company_address == 'yes'))
            <section id="client-info">
                <span class="descriptionFont">@lang('modules.invoices.billedTo')</span>
                <div class="descriptionFont">
                    @if ($proposal->deal && !empty($proposal->deal->name))
                        <div class="descriptionFont">{{ $proposal->deal->name }}</div>
                    @endif
                    @if ($proposal->lead->contact && $proposal->lead->contact->client_name && $invoiceSetting->show_client_name == 'yes')
                        <span class="bold descriptionFont">{{ $proposal->lead->contact->client_name_salutation }}</span>
                    @endif
                    @if ($proposal->lead->contact && $proposal->lead->email && $invoiceSetting->show_client_email == 'yes')
                        <div class="descriptionFont">{{ $proposal->lead->contact->client_email }}</div>
                    @endif
                    @if ($proposal->lead->contact && $proposal->lead->contact->mobile && $invoiceSetting->show_client_phone == 'yes')
                        <div class="descriptionFont">{{ $proposal->lead->contact->mobile }}</div>
                    @endif
                    @if ($proposal->lead->contact && $proposal->lead->contact->company_name && $invoiceSetting->show_client_company_name == 'yes')
                        <div class="descriptionFont">{{ $proposal->lead->contact->company_name }}</div>
                    @endif
                    @if ($proposal->lead->contact && $proposal->lead->contact->address && $invoiceSetting->show_client_company_address == 'yes')
                        <div class="descriptionFont">{!! nl2br($proposal->lead->contact->address) !!}</div>
                    @endif
                </div>
            </section>
        @endif

        <div class="clearfix"></div>
    </div>

    <div class="invoice-body descriptionFont">

        @if ($proposal->description)
            <div class="f-13 mb-3 description descriptionFont">{!! nl2br(pdfStripTags($proposal->description)) !!}</div>
        @endif

        @if (count($proposal->items) > 0)

            <section id="items">

                <table cellpadding="0" cellspacing="0">

                    <tr>
                        <th class="descriptionFont">#</th> <!-- Dummy cell for the row number and row commands -->
                        <th class="descriptionFont">@lang("modules.invoices.item")</th>
                        @if ($invoiceSetting->hsn_sac_code_show)
                            <th class="descriptionFont">@lang("app.hsnSac")</th>
                        @endif
                        <th class="descriptionFont">@lang('modules.invoices.qty')</th>
                        <th class="descriptionFont">@lang("modules.invoices.unitPrice")</th>
                        <th class="descriptionFont">@lang("modules.invoices.tax")</th>
                        <th class="descriptionFont">@lang("modules.invoices.price")
                            ({!! htmlentities($proposal->currency->currency_code)  !!})
                        </th>
                    </tr>

                        <?php $count = 0; ?>
                    @foreach($proposal->items->sortBy('field_order') as $item)
                        @if($item->type == 'item')
                            <tr data-iterate="item">
                                <td>{{ ++$count }}</td>
                                <!-- Don't remove this column as it's needed for the row commands -->
                                <td class="word-break">
                                    {{ $item->item_name }}
                                    @if(!is_null($item->item_summary))
                                        <p class="item-summary descriptionFont word-break">{!! nl2br(pdfStripTags($item->item_summary)) !!}</p>
                                    @endif
                                    @if ($item->proposalItemImage)
                                        <p class="mt-2">
                                            <img src="{{ $item->proposalItemImage->file_url }}" width="60" height="60"
                                                 class="img-thumbnail">
                                        </p>
                                    @endif
                                </td>
                                @if ($invoiceSetting->hsn_sac_code_show)
                                    <td>{{ $item->hsn_sac_code ? $item->hsn_sac_code : '--' }}</td>
                                @endif
                                <td>{{ $item->quantity }}@if($item->unit)
                                        <br><span class="f-11 text-dark-grey">{{ $item->unit->unit_type }}</span>
                                    @endif</td>
                                <td>{{ currency_format($item->unit_price, $proposal->currency_id, false) }}</td>
                                <td>{{ $item->tax_list }}</td>
                                <td>{{ currency_format($item->amount, $proposal->currency_id, false) }}</td>
                            </tr>
                        @endif
                    @endforeach

                </table>

                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <td colspan="{{ $invoiceSetting->hsn_sac_code_show ? '5' : '4' }}">@lang("modules.invoices.subTotal")
                            :
                        </td>
                        <td>{{ currency_format($proposal->sub_total, $proposal->currency_id, false) }}</td>
                    </tr>
                    @if($discount != 0 && $discount != '')
                        <tr data-iterate="tax">
                            <td colspan="{{ $invoiceSetting->hsn_sac_code_show ? '5': '4' }}">@lang("modules.invoices.discount"):
                                @if($proposal->discount_type == 'percent')
                                    {{$proposal->discount}}%
                                @else
                                    {{ currency_format($proposal->discount, $proposal->currency_id) }}
                                @endif
                            </td>
                            <td>-{{ currency_format($discount, $proposal->currency_id, false) }}</td>
                        </tr>
                    @endif
                    @foreach($taxes as $key=>$tax)
                        <tr data-iterate="tax">
                            <td colspan="{{ $invoiceSetting->hsn_sac_code_show ? '5': '4' }}">{{ $key }}:</td>
                            <td>{{ currency_format($tax, $proposal->currency_id, false) }}</td>
                        </tr>
                    @endforeach
                    <tr class="amount-total">
                        <td colspan="{{ $invoiceSetting->hsn_sac_code_show ? '5': '4' }}">
                            @lang("modules.invoices.total"):
                        </td>
                        <td>
                            {{ currency_format($proposal->total, $proposal->currency_id, false) }}
                        </td>
                    </tr>
                </table>


            </section>

        @endif


        <section id="terms" class="descriptionFont">
            @if(!is_null($proposal->note))
                <div class="word-break item-summary">@lang('app.note') <br>{!! nl2br($proposal->note) !!}</div>
            @endif

            <div class="word-break item-summary">@lang('modules.invoiceSettings.invoiceTerms')
                <br>{!! nl2br($invoiceSetting->invoice_terms) !!}</div>

            @if (isset($invoiceSetting->other_info))
                <div class="word-break item-summary description">
                    {!! nl2br($invoiceSetting->other_info) !!}
                </div>
            @endif
        </section>

        @if (isset($taxes) && $invoiceSetting->tax_calculation_msg == 1)
            <p class="text-dark-grey descriptionFont">
                @if ($proposal->calculate_tax == 'after_discount')
                    @lang('messages.calculateTaxAfterDiscount')
                @else
                    @lang('messages.calculateTaxBeforeDiscount')
                @endif
            </p>
        @endif

        <div class="clearfix"></div>
        <br><br>
        <section>
            @if ($proposal->signature)
                @if (!is_null($proposal->signature->signature))
                    <img src="{{ $proposal->signature->signature }}" style="width: 200px;">
                    <h6 class="descriptionFont">@lang('modules.estimates.signature')</h6>
                @else
                    <h6 class="descriptionFont">@lang('modules.estimates.signedBy')</h6>
                @endif
                <p class="descriptionFont">({{ $proposal->signature->full_name }})</p>
            @endif
        </section>
    </div>

</div>

</body>
</html>
