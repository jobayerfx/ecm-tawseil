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
                @if ($proposal->lead->contact && ($proposal->lead->contact->client_name || $proposal->lead->contact->client_email || $proposal->lead->contact->mobile || $proposal->lead->contact->company_name || $proposal->lead->contact->address))
                    <small>@lang("modules.invoices.billedTo"):</small>
                    <table>
                        @if ($proposal->deal && !empty($proposal->deal->name))
                            <tr><td>{{ $proposal->deal->name }}</td></tr>
                        @endif
                        @if ($proposal->lead->contact->client_name)
                            <tr><td><b>{{ $proposal->lead->contact->client_name_salutation ?? '' }}</b></td></tr>
                        @endif
                        @if ($proposal->lead->contact->client_email)
                            <tr><td>{{ $proposal->lead->contact->client_email }}</td></tr>
                        @endif
                        @if ($proposal->lead->contact->mobile)
                            <tr><td>{{ $proposal->lead->contact->mobile }}</td></tr>
                        @endif
                        @if ($proposal->lead->contact->company_name)
                            <tr><td>{{ $proposal->lead->contact->company_name }}</td></tr>
                        @endif
                        @if ($proposal->lead->contact->address)
                            <tr><td>{!! nl2br($proposal->lead->contact->address ?? '') !!}</td></tr>
                        @endif
                    </table>
                @endif
            </td>
            <td>
                <div id="company">
                    <div id="logo">
                        @if($invoiceSetting->logo_url)
                            <img src="{{ $invoiceSetting->logo_url }}" height="50" alt="Logo" style="margin-bottom: 15px;"/>
                        @endif
                    </div>
                    <small>@lang("modules.invoices.generatedBy"):</small>
                    <div id="description">
                        <h3>{{ $company->company_name }}</h3>
                        @if (!is_null($company))
                            <div>{!! nl2br($company->defaultAddress->address) !!}</div>
                            <div>{{ $company->company_phone }}</div>
                        @endif
                        @if ($invoiceSetting->show_gst == 'yes' && !is_null($invoiceSetting->gst_number))
                            <div>@lang('app.gstIn'): {{ $invoiceSetting->gst_number }}</div>
                        @endif
                    </div>
                </div>
            </td>
        </tr>
    </table>
</header>

<main>
    <div id="details">
        <table>
            <tr>
                <td>
                    <h1>{{ $proposal->proposal_number }}</h1>
                    <div>@lang('app.status'): {{ $proposal->status }}</div>
                    <div>@lang('modules.estimates.validTill'): {{ $proposal->valid_till->translatedFormat($company->date_format) }}</div>
                    <div>@lang('app.date'): {{ $proposal->created_at->translatedFormat($company->date_format) }}</div>
                </td>
            </tr>
        </table>
    </div>

    @if ($proposal->description)
        <p>{!! pdfStripTags($proposal->description) !!}</p>
    @endif

    @if (count($proposal->items) > 0)
        <table cellspacing="0" cellpadding="0" id="invoice-table">
            <tfoot>
                <tr>
                    <td colspan="{{ $invoiceSetting->hsn_sac_code_show ? '6' : '5' }}">@lang("modules.invoices.subTotal")</td>
                    <td>{{ currency_format($proposal->sub_total, $proposal->currency_id, false) }}</td>
                </tr>
                <tr>
                    <td colspan="{{ $invoiceSetting->hsn_sac_code_show ? '6' : '5' }}">@lang("modules.invoices.discount")</td>
                    <td>{{ currency_format($discount, $proposal->currency_id, false) }}</td>
                </tr>
                <tr>
                    <td colspan="{{ $invoiceSetting->hsn_sac_code_show ? '6' : '5' }}">@lang("modules.invoices.total")</td>
                    <td>{{ currency_format($proposal->total, $proposal->currency_id, false) }}</td>
                </tr>
            </tfoot>
            <thead>
                <tr>
                    <th class="no">#</th>
                    <th class="desc">@lang("modules.invoices.item")</th>
                    @if ($invoiceSetting->hsn_sac_code_show)
                        <th class="qty">@lang("app.hsnSac")</th>
                    @endif
                    <th class="qty">@lang('modules.invoices.qty')</th>
                    <th class="qty">@lang("modules.invoices.unitPrice")</th>
                    <th class="qty">@lang("modules.invoices.tax")</th>
                    <th class="unit">@lang("modules.invoices.price") ({!! htmlentities($proposal->currency->currency_code) !!})</th>
                </tr>
            </thead>
            <tbody>
                @php $count = 0; @endphp
                @foreach ($proposal->items->sortBy('field_order') as $item)
                    @if ($item->type == 'item')
                        <tr>
                            <td>{{ ++$count }}</td>
                            <td>
                                {{ $item->item_name }}
                                @if (!is_null($item->item_summary))
                                    <div>{!! nl2br(pdfStripTags($item->item_summary)) !!}</div>
                                @endif
                                @if($item->proposalItemImage && $item->proposalItemImage->file_url)
                                    <img src="{{ $item->proposalItemImage->file_url }}" width="60" height="60"/>
                                @endif
                            </td>
                            @if ($invoiceSetting->hsn_sac_code_show)
                                <td>{{ $item->hsn_sac_code ?: '--' }}</td>
                            @endif
                            <td>{{ $item->quantity }} @if($item->unit)<br>{{ $item->unit->unit_type }}@endif</td>
                            <td>{{ currency_format($item->unit_price, $proposal->currency_id, false) }}</td>
                            <td>{{ $item->tax_list }}</td>
                            <td>{{ currency_format($item->amount, $proposal->currency_id, false) }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    @endif

    <p id="notes">
        @if (!is_null($proposal->note))
            @lang('app.note')<br>{!! nl2br($proposal->note) !!}<br>
        @endif
        <br>@lang('modules.invoiceSettings.invoiceTerms')<br>{!! nl2br($invoiceSetting->invoice_terms) !!}
    </p>

    @if ($proposal->signature)
        <p>
            @if (!is_null($proposal->signature->signature))
                <img src="{{ $proposal->signature->signature }}" style="width: 200px;" alt="Signature"><br>
                @lang('modules.estimates.signature')
            @else
                @lang('modules.estimates.signedBy')
            @endif
            <br>({{ $proposal->signature->full_name }})
        </p>
    @endif
</main>
</body>
</html>
