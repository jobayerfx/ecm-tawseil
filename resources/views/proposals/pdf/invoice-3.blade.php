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
<div id="container">
    <section id="memo" class="description">
        <div class="logo">
            <img src="{{ $invoiceSetting->logo_url }}"/>
        </div>

        <div class="company-info">
            <div class="description">
                {{ $company->company_name }}
            </div>

            <br/>

            <span class="description">{!! nl2br($company->defaultAddress->address) !!}</span>

            <br/>

            <span class="description">{{ $company->company_phone }}</span>

            <br/>

            @if($invoiceSetting->show_gst == 'yes' && !is_null($invoiceSetting->gst_number))
                <div class="description" @lang('app.gstIn'): {{ $invoiceSetting->gst_number }}</div>
    @endif
</div>

</section>

<section id="invoice-title-number">
    <span id="title" class="description">{{ str($proposal->proposal_number)->replace($proposal->original_proposal_number, '') }}</span>
    <span id="number">{{ $proposal->original_proposal_number }}</span>
</section>

<div class="clearfix"></div>

@if ($proposal->lead->contact && ($proposal->lead->contact->client_name || $proposal->lead->contact->client_email || $proposal->lead->contact->mobile || $proposal->lead->contact->company_name || $proposal->lead->contact->address) && ($invoiceSetting->show_client_name == 'yes' || $invoiceSetting->show_client_email == 'yes' || $invoiceSetting->show_client_phone == 'yes' || $invoiceSetting->show_client_company_name == 'yes' || $invoiceSetting->show_client_company_address == 'yes'))
    <section id="client-info" class="description">
        <span>@lang('modules.invoices.billedTo'):</span>
        <div>
            @if ($proposal->deal && !empty($proposal->deal->name))
                <div>{{ $proposal->deal->name }}</div>
            @endif
            @if ($proposal->lead->contact && $proposal->lead->contact->client_name && $invoiceSetting->show_client_name == 'yes')
                <span class="bold">{{ $proposal->lead->contact->client_name_salutation }}</span>
            @endif
            @if ($proposal->lead->contact && $proposal->lead->contact->client_email && $invoiceSetting->show_client_email == 'yes')
                <div>{{ $proposal->lead->contact->client_email }}</div>
            @endif
            @if ($proposal->lead->contact && $proposal->lead->contact->mobile && $invoiceSetting->show_client_phone == 'yes')
                <div>{{ $proposal->lead->contact->mobile }}</div>
            @endif
            @if ($proposal->lead->contact && $proposal->lead->contact->company_name && $invoiceSetting->show_client_company_name == 'yes')
                <div>{{ $proposal->lead->contact->company_name }}</div>
            @endif
            @if ($proposal->lead->contact && $proposal->lead->contact->address && $invoiceSetting->show_client_company_address == 'yes')
                <div>{!! nl2br($proposal->lead->contact->address) !!}</div>
            @endif
        </div>
    </section>
@endif

<div class="clearfix"></div>
<br>
<section id="items">
    @if ($proposal->description)
        <div class="f-13 mb-3 description">{!! nl2br(pdfStripTags($proposal->description)) !!}</div>
    @endif

    @if (count($proposal->items) > 0)

        <table cellpadding="0" cellspacing="0">

            <tr>
                <th>#</th> <!-- Dummy cell for the row number and row commands -->
                <th class="description">@lang("modules.invoices.item")</th>
                @if ($invoiceSetting->hsn_sac_code_show)
                    <th class="description">@lang("app.hsnSac")</th>
                @endif
                <th class="description">@lang('modules.invoices.qty')</th>
                <th class="description">@lang("modules.invoices.unitPrice")</th>
                <th class="description">@lang("modules.invoices.tax")</th>
                <th class="description">@lang("modules.invoices.price")
                    ({!! htmlentities($proposal->currency->currency_code)  !!})
                </th>
            </tr>

            @foreach($proposal->items->sortBy('field_order') as $index=>$item)
                @if($item->type == 'item')
                    <tr data-iterate="item">
                        <td>{{ $index+1  }}</td> <!-- Don't remove this column as it's needed for the row commands -->
                        <td>
                            <div class="mb-3 description word-break">{{ $item->item_name }}</div>
                            @if(!is_null($item->item_summary))
                                <p class="item-summary mb-3 description word-break">{!! nl2br(pdfStripTags($item->item_summary)) !!}</p>
                            @endif
                            @if ($item->proposalItemImage)
                                <p class="mt-2">
                                    <img src="{{ $item->proposalItemImage->file_url }}" width="60" height="60"
                                         class="img-thumbnail">
                                </p>
                            @endif
                        </td>
                        @if ($invoiceSetting->hsn_sac_code_show)
                            <td>{{ $item->hsn_sac_code ?  : '--' }}</td>
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

    @endif

</section>

@if (count($proposal->items) > 0)

    <section id="sums">

        <table cellpadding="0" cellspacing="0">
            <tr>
                <th>@lang("modules.invoices.subTotal"):</th>
                <td>{{ currency_format($proposal->sub_total, $proposal->currency_id, false) }}</td>
            </tr>
            @if($discount != 0 && $discount != '')
                <tr data-iterate="tax">
                    <th>@lang("modules.invoices.discount"):
                        @if($proposal->discount_type == 'percent')
                            {{$proposal->discount}}%
                        @else
                            {{ currency_format($proposal->discount, $proposal->currency_id) }}
                        @endif
                    </th>
                    <td>{{ currency_format($discount, $proposal->currency_id, false) }}</td>
                </tr>
            @endif
            @foreach($taxes as $key=>$tax)
                <tr data-iterate="tax">
                    <th>{{ $key }}:</th>
                    <td>{{ currency_format($tax, $proposal->currency_id, false) }}</td>
                </tr>
            @endforeach
            <tr class="amount-total">
                <th>@lang("modules.invoices.total"):</th>
                <td>{{ currency_format($proposal->total, $proposal->currency_id, false) }}</td>
            </tr>
        </table>


    </section>

    <div class="clearfix"></div>
    <br>
    <section id="terms" class="description">
        <div class="notes">
            <div class="description">
                <span>@lang('app.status'):</span>
                <span>{{ $proposal->status }}</span>
            </div>
            <div class="description">
                <span>@lang('modules.estimates.validTill'):</span>
                <span>{{ $proposal->valid_till->translatedFormat($company->date_format) }}</span>
            </div>
            <div class="description">
                <span>@lang('app.date'):</span>
                <span>{{ $proposal->created_at->translatedFormat($company->date_format) }}</span>
            </div>

            @if(!is_null($proposal->note))
                <br> @lang('app.note') <br>{!! nl2br($proposal->note) !!}
            @endif
            <br><br>@lang('modules.invoiceSettings.invoiceTerms') <br>{!! nl2br($invoiceSetting->invoice_terms) !!}

            @if (isset($invoiceSetting->other_info))
                <br><br>{!! nl2br($invoiceSetting->other_info) !!}
            @endif

            @if (isset($taxes) && $invoiceSetting->tax_calculation_msg == 1)
                <div class="clearfix"></div>
                <br>
                <section>
                    <div class="invoice-info" width="100%" class="description">
                        <p class="text-dark-grey description">
                            @if ($proposal->calculate_tax == 'after_discount')
                                @lang('messages.calculateTaxAfterDiscount')
                            @else
                                @lang('messages.calculateTaxBeforeDiscount')
                            @endif
                        </p>
                    </div>
                </section>
            @endif

            <div class="clearfix"></div>
            <br>
            <div>
                <p class="description">
                    @if ($proposal->signature)
                        @if (!is_null($proposal->signature->signature))
                            <img src="{{ $proposal->signature->signature }}" style="width: 200px;">
                <h6>@lang('modules.estimates.signature')</h6>
                @else
                    <h6 class="description">@lang('modules.estimates.signedBy')</h6>
                @endif
                <p class="description">({{ $proposal->signature->full_name }})</p>
                @endif
                </p>
            </div>
        </div>
    </section>

    @endif


    </div>
</body>
</html>
