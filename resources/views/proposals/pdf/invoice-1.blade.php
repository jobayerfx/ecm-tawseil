<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>@lang('app.proposal')</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>

<body>
<header class="description clearfix">

    <table cellpadding="0" cellspacing="0" class="billing">
        <tr>
            <td colspan="2"><h1>@lang('app.proposal')</h1></td>
        </tr>
        <tr>
            <td id="invoiced_to">
                @if ($proposal->lead->contact && ($proposal->lead->contact->client_name || $proposal->lead->contact->client_email || $proposal->lead->contact->mobile || $proposal->lead->contact->company_name || $proposal->lead->contact->address) && ($invoiceSetting->show_client_name == 'yes' || $invoiceSetting->show_client_email == 'yes' || $invoiceSetting->show_client_phone == 'yes' || $invoiceSetting->show_client_company_name == 'yes' || $invoiceSetting->show_client_company_address == 'yes'))
                    <div>
                        <small>@lang("modules.invoices.billedTo"):</small>
                        <div class="mb-3 description">
                            @if ($proposal->deal && !empty($proposal->deal->name))
                                <div>{{ $proposal->deal->name }}</div>
                            @endif
                            @if ($proposal->lead->contact && $proposal->lead->contact->client_name && $invoiceSetting->show_client_name == 'yes')
                                <b>{{ $proposal->lead->contact->client_name_salutation }}</b>
                            @endif
                            @if ($proposal->lead && $proposal->lead->contact->client_email && $invoiceSetting->show_client_email == 'yes')
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
                    </div>
                @endif
            </td>
            <td>
                <div id="company" class="description">
                    <div id="logo">
                        <img src="{{ $invoiceSetting->logo_url }}" alt="home" class="dark-logo"/>
                    </div>
                    <small>@lang("modules.invoices.generatedBy"):</small>
                    <div id="description" class="description">
                        <h3 class="name">{{ $company->company_name }}</h3>
                        @if (!is_null($company))
                            <div>{!! nl2br($company->defaultAddress->address) !!}</div>
                            <div>{{ $company->company_phone }}</div>
                        @endif
                        @if ($invoiceSetting->show_gst == 'yes' && !is_null($invoiceSetting->gst_number))
                            <div class="description">@lang('app.gstIn'): {{ $invoiceSetting->gst_number }}</div>
                        @endif
                    </div>
                </div>
            </td>
        </tr>
    </table>
</header>
<main>
    <div id="details">

        <div id="invoice" class="description">
            <h1 class="description">{{ $proposal->proposal_number }}</h1>
            <div class="description">@lang('app.status'): {{ $proposal->status }}</div>
            <div class="description">@lang('modules.estimates.validTill'):
                {{ $proposal->valid_till->translatedFormat($company->date_format) }}</div>
            <div class="description">@lang('app.date'):
                {{ $proposal->created_at->translatedFormat($company->date_format) }}</div>
        </div>

    </div>
    @if ($proposal->description)
        <div class="description">
            {!! pdfStripTags($proposal->description) !!}
        </div>
    @endif

    @if (count($proposal->items) > 0)
        <table cellspacing="0" cellpadding="0" id="invoice-table">
            <thead>
            <tr>
                <th class="no">#</th>
                <th class="desc description">@lang("modules.invoices.item")</th>
                @if ($invoiceSetting->hsn_sac_code_show)
                    <th class="qty description">@lang("app.hsnSac")</th>
                @endif
                <th class="qty description">@lang('modules.invoices.qty')</th>
                <th class="qty description">@lang("modules.invoices.unitPrice")</th>
                <th class="qty description">@lang("modules.invoices.tax")</th>
                <th class="unit description">@lang("modules.invoices.price")
                    ({!! htmlentities($proposal->currency->currency_code) !!})
                </th>
            </tr>
            </thead>
            <tbody>
                <?php $count = 0; ?>
            @foreach ($proposal->items->sortBy('field_order') as $item)
                @if ($item->type == 'item')
                    <tr style="page-break-inside: avoid;">
                        <td class="no">{{ ++$count }}</td>
                        <td class="desc">
                            <h3 class="description word-break">{{ $item->item_name }}</h3>
                            @if (!is_null($item->item_summary))
                                <table>
                                    <tr>
                                        <td class="item-summary word-break border-top-0 border-right-0 border-left-0 border-bottom-0 description">{!! nl2br(pdfStripTags($item->item_summary)) !!}</td>
                                    </tr>
                                </table>
                            @endif
                            @if ($item->proposalItemImage)
                                <p class="mt-2">
                                    <img src="{{ $item->proposalItemImage->file_url }}" width="60" height="60"
                                         class="img-thumbnail"/>
                                </p>
                            @endif
                        </td>
                        @if ($invoiceSetting->hsn_sac_code_show)
                            <td class="qty">
                                <h3>{{ $item->hsn_sac_code ?  : '--' }}</h3>
                            </td>
                        @endif
                        <td class="qty">
                            <h3>{{ $item->quantity }}
                                @if($item->unit)<br>
                                    <span class="f-11 text-dark-grey">{{ $item->unit->unit_type }}</span>
                                @endif
                            </h3>
                        </td>
                        <td class="qty">
                            <h3>{{ currency_format($item->unit_price, $proposal->currency_id, false) }}</h3>
                        </td>
                        <td>
                            {{ $item->tax_list }}
                        </td>
                        <td class="unit">{{ currency_format($item->amount, $proposal->currency_id, false) }}</td>
                    </tr>
                @endif
            @endforeach
            <tr style="page-break-inside: avoid;" class="subtotal">
                <td class="no">&nbsp;</td>
                <td class="qty">&nbsp;</td>
                <td class="qty">&nbsp;</td>
                @if ($invoiceSetting->hsn_sac_code_show)
                    <td class="qty">&nbsp;</td>
                @endif
                <td class="qty">&nbsp;</td>
                <td class="desc">@lang("modules.invoices.subTotal")</td>
                <td class="unit">{{ currency_format($proposal->sub_total, $proposal->currency_id, false) }}</td>
            </tr>

            @if ($discount != 0 && $discount != '')
                <tr style="page-break-inside: avoid;" class="discount">
                    <td class="no">&nbsp;</td>
                    <td class="qty">&nbsp;</td>
                    <td class="qty">&nbsp;</td>
                    @if ($invoiceSetting->hsn_sac_code_show)
                        <td class="qty">&nbsp;</td>
                    @endif
                    <td class="qty">&nbsp;</td>
                    <td class="desc">@lang("modules.invoices.discount"):
                        @if($proposal->discount_type == 'percent')
                            {{$proposal->discount}}%
                        @else
                            {{ currency_format($proposal->discount, $proposal->currency_id) }}
                        @endif
                    </td>
                    <td class="unit">{{ currency_format($discount, $proposal->currency_id, false) }}</td>
                </tr>
            @endif
            @foreach ($taxes as $key => $tax)
                <tr style="page-break-inside: avoid;" class="tax">
                    <td class="no">&nbsp;</td>
                    <td class="qty">&nbsp;</td>
                    <td class="qty">&nbsp;</td>
                    @if ($invoiceSetting->hsn_sac_code_show)
                        <td class="qty">&nbsp;</td>
                    @endif
                    <td class="qty">&nbsp;</td>
                    <td class="desc">{{ $key }}</td>
                    <td class="unit">{{ currency_format($tax, $proposal->currency_id, false) }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr dontbreak="true">
                <td colspan="{{ $invoiceSetting->hsn_sac_code_show ? '6' : '5' }}">
                    @lang("modules.invoices.subTotal")</td>
                <td style="text-align: center">{{ currency_format($proposal->sub_total, $proposal->currency_id, false) }}</td>
            </tr>
            <tr dontbreak="true">
                <td colspan="{{ $invoiceSetting->hsn_sac_code_show ? '6' : '5' }}">
                    @lang("modules.invoices.discount")</td>
                <td style="text-align: center">{{ currency_format($discount, $proposal->currency_id, false) }}</td>
            </tr>
            <tr dontbreak="true">
                <td colspan="{{ $invoiceSetting->hsn_sac_code_show ? '6' : '5' }}">
                    @lang("modules.invoices.total")</td>
                <td style="text-align: center">{{ currency_format($proposal->total, $proposal->currency_id, false) }}</td>
            </tr>
            </tfoot>
        </table>


        <p id="notes" class="word-break description">
            @if (!is_null($proposal->note))
                @lang('app.note') <br>{!! nl2br($proposal->note) !!}<br>
            @endif
            <br>@lang('modules.invoiceSettings.invoiceTerms') <br>{!! nl2br($invoiceSetting->invoice_terms) !!}
        </p>

        @if (isset($invoiceSetting->other_info))
            <p id="notes" class="word-break description">
                {!! nl2br($invoiceSetting->other_info) !!}
            </p>
        @endif

        @if (isset($taxes) && $invoiceSetting->tax_calculation_msg == 1)
            <p class="text-dark-grey description">
                @if ($proposal->calculate_tax == 'after_discount')
                    @lang('messages.calculateTaxAfterDiscount')
                @else
                    @lang('messages.calculateTaxBeforeDiscount')
                @endif
            </p>
        @endif

        @if ($proposal->signature)
            <br>
            <span>
                @if (!is_null($proposal->signature->signature))
                    <img src="{{ $proposal->signature->signature }}" style="width: 200px;" alt="Signature">
                    <h6 class="description">@lang('modules.estimates.signature')</h6>
                @else
                    <h6>@lang('modules.estimates.signedBy')</h6>
                @endif
                <span class="description">({{ $proposal->signature->full_name }})</span>
            </span>
        @endif

    @endif
</main>
</body>

</html>
