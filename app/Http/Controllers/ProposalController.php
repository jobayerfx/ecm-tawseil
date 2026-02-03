<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Tax;
use App\Models\Deal;
use App\Helper\Files;
use App\Helper\Reply;
use App\Models\Product;
use App\Models\Currency;
use App\Models\Proposal;
use App\Models\UnitType;
use App\Models\ProposalItem;
use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Events\NewProposalEvent;
use App\Models\ProposalTemplate;
use App\Models\ProposalItemImage;
use Illuminate\Support\Facades\App;
use App\Models\ProposalTemplateItem;
use App\Models\InvoiceSetting;
use App\DataTables\ProposalDataTable;
use App\Http\Requests\Proposal\StoreRequest;
use App\Models\Lead;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Log;

class ProposalController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.proposal';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('leads', $this->user->modules));

            return $next($request);
        });
    }

    public function index(ProposalDataTable $dataTable)
    {
        abort_403($this->sidebarUserPermissions['view_lead_proposals'] == 5);

        if (!request()->ajax()) {
            $this->leads = Lead::allLeads();
        }

        return $dataTable->render('proposals.index', $this->data);
    }

    public function create()
    {
        $this->pageTitle = __('modules.proposal.createProposal');

        $this->addPermission = user()->permission('add_lead_proposals');
        $this->viewLeadPermission = user()->permission('view_lead');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        if (request('proposal') != '') {
            $this->proposalId = request('proposal');
            $this->type = 'proposal';
            $this->proposal = Proposal::with('items', 'items.proposalItemImage', 'lead', 'unit', 'deal')->findOrFail($this->proposalId);
        }

        $this->taxes = Tax::all();

        if (request('deal_id') != '') {
            $this->deal = Deal::findOrFail(request('deal_id'));
        }
        else {
            $leadContact = Lead::query();

            if($this->viewLeadPermission == 'added') {
                $this->leadContacts = $leadContact->where('added_by', user()->id)->get();
            }
            elseif($this->viewLeadPermission == 'both') {
                $this->leadContacts = $leadContact->where('added_by', user()->id)
                            ->orWhere('lead_owner', user()->id)->get();
            }
            elseif($this->viewLeadPermission == 'owned') {
                $this->leadContacts = $leadContact->where('lead_owner', user()->id)->get();
            }
            elseif($this->viewLeadPermission == 'none') {
                $this->leadContacts = collect();
            }elseif($this->viewLeadPermission == 'all') {
                $this->leadContacts = $leadContact->get();
            }

            if (count($this->leadContacts) > 0) {
                if (request('proposal') != '') {
                    $this->deals = Deal::allLeads($this->proposal->deal->lead_id);
                }else {
                    $this->deals = Deal::allLeads($this->leadContacts[0]->id);
                }
            }
            else {
                $this->deals = Deal::allLeads();
            }
        }

        $this->units = UnitType::all();
        $this->template = ProposalTemplate::all();
        $this->products = Product::all();
        $this->categories = ProductCategory::all();
        $this->currencies = Currency::all();
        $this->invoiceSetting = invoice_setting();

        $this->template = ProposalTemplate::all();
        $this->proposalTemplate = request('template') ? ProposalTemplate::findOrFail(request('template')) : null;
        $this->proposalTemplateItem = request('template') ? ProposalTemplateItem::with('proposalTemplateItemImage')->where('proposal_template_id', request('template'))->get() : null;

        $this->view = 'proposals.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('proposals.create', $this->data);
    }

    public function store(StoreRequest $request)
    {

        $items = $request->item_name;
        $cost_per_item = $request->cost_per_item;
        $quantity = $request->quantity;
        $amount = $request->amount;

        foreach ($quantity as $qty) {
            if (!is_numeric($qty) && (intval($qty) < 1)) {
                return Reply::error(__('messages.quantityNumber'));
            }
        }

        foreach ($cost_per_item as $rate) {
            if (!is_numeric($rate)) {
                return Reply::error(__('messages.unitPriceNumber'));
            }
        }

        foreach ($amount as $amt) {
            if (!is_numeric($amt)) {
                return Reply::error(__('messages.amountNumber'));
            }
        }

        $lastProposal = Proposal::lastProposalNumber() + 1;
        $invoiceSetting = invoice_setting();
        $zero = str_repeat('0', $invoiceSetting->proposal_digit - strlen($lastProposal));

        $originalNumber = $zero . $lastProposal;
        $proposalNumber = $invoiceSetting->proposal_prefix . $invoiceSetting->proposal_number_separator . $zero . $lastProposal;


        $proposal = new Proposal();
        $proposal->deal_id = $request->deal_id;
        $proposal->valid_till = companyToYmd($request->valid_till);
        $proposal->sub_total = $request->sub_total;
        $proposal->total = $request->total;
        $proposal->currency_id = $request->currency_id;
        $proposal->note = trim_editor($request->note);
        $proposal->discount = round($request->discount_value, 2);
        $proposal->discount_type = $request->discount_type;
        $proposal->status = 'waiting';
        $proposal->signature_approval = ($request->require_signature) ? 1 : 0;
        $proposal->description = trim_editor($request->description);
        $proposal->proposal_number = $proposalNumber;
        $proposal->original_proposal_number = $originalNumber;
        $proposal->save();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('proposals.index');
        }

        $this->logSearchEntry($proposal->id, $proposalNumber, 'proposals.show', 'proposal');

        return Reply::redirect($redirectUrl, __('messages.recordSaved'));
    }

    public function show($id)
    {
        $this->viewLeadProposalsPermission = user()->permission('view_lead_proposals');

        $this->invoice = Proposal::with('deal', 'items', 'unit', 'lead', 'items.proposalItemImage')->findOrFail($id);
        abort_403(!($this->viewLeadProposalsPermission == 'all' || ($this->viewLeadProposalsPermission == 'added' && $this->invoice->added_by == user()->id)));

        $this->pageTitle = $this->invoice->proposal_number;

        if ($this->invoice->discount > 0) {
            if ($this->invoice->discount_type == 'percent') {
                $this->discount = (($this->invoice->discount / 100) * $this->invoice->sub_total);
            }
            else {
                $this->discount = $this->invoice->discount;
            }
        }
        else {
            $this->discount = 0;
        }

        if($this->invoice->discount_type == 'percent') {
            $discountAmount = $this->invoice->discount;
            $this->discountType = $discountAmount.'%';
        }else {
            $discountAmount = $this->invoice->discount;
            $this->discountType = currency_format($discountAmount, $this->invoice->currency_id);
        }

        $taxList = array();

        $this->firstProposal = Proposal::orderBy('id', 'desc')->first();
        $items = ProposalItem::whereNotNull('taxes')
            ->where('proposal_id', $this->invoice->id)
            ->get();

        foreach ($items as $item) {

            foreach (json_decode($item->taxes) as $tax) {
                $this->tax = ProposalItem::taxbyid($tax)->first();

                if (!isset($taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'])) {

                    if ($this->invoice->calculate_tax == 'after_discount' && $this->discount > 0) {
                        $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = ($item->amount - ($item->amount / $this->invoice->sub_total) * $this->discount) * ($this->tax->rate_percent / 100);

                    }
                    else {
                        $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $item->amount * ($this->tax->rate_percent / 100);
                    }

                }
                else {
                    if ($this->invoice->calculate_tax == 'after_discount' && $this->discount > 0) {
                        $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + (($item->amount - ($item->amount / $this->invoice->sub_total) * $this->discount) * ($this->tax->rate_percent / 100));

                    }
                    else {
                        $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + ($item->amount * ($this->tax->rate_percent / 100));
                    }
                }
            }
        }

        $this->taxes = $taxList;

        $this->settings = company();
        $this->invoiceSetting = invoice_setting();

        return view('proposals.show', $this->data);
    }

    public function edit($id)
    {
        $this->pageTitle = __('modules.proposal.updateProposal');
        $this->taxes = Tax::all();
        $this->currencies = Currency::all();
        $this->proposal = Proposal::with('items', 'lead')->findOrFail($id);

        $this->units = UnitType::all();
        $this->products = Product::all();
        $this->categories = ProductCategory::all();
        $this->invoiceSetting = invoice_setting();

        $this->view = 'proposals.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('proposals.create', $this->data);
    }

    public function update(StoreRequest $request, $id)
    {
        $items = $request->item_name;
        $cost_per_item = $request->cost_per_item;
        $hsn_sac_code = $request->hsn_sac_code;
        $quantity = $request->quantity;
        $amount = $request->amount;
        $itemsSummary = $request->item_summary;
        $tax = $request->taxes;

        if ($request->has('item_name') && (trim($items[0]) == '' || trim($cost_per_item[0]) == '')) {
            return Reply::error(__('messages.addItem'));
        }

        if ($request->has('quantity')) {
            foreach ($quantity as $qty) {
                if (!is_numeric($qty)) {
                    return Reply::error(__('messages.quantityNumber'));
                }
            }
        }

        if ($request->has('cost_per_item')) {
            foreach ($cost_per_item as $rate) {
                if (!is_numeric($rate)) {
                    return Reply::error(__('messages.unitPriceNumber'));
                }
            }
        }

        if ($request->has('amount')) {
            foreach ($amount as $amt) {
                if (!is_numeric($amt)) {
                    return Reply::error(__('messages.amountNumber'));
                }
            }
        }

        if ($request->has('item_name')) {
            foreach ($items as $itm) {
                if (is_null($itm)) {
                    return Reply::error(__('messages.itemBlank'));
                }
            }
        }

        $proposal = Proposal::findOrFail($id);
        $proposal->deal_id = $request->deal_id;
        $proposal->valid_till = companyToYmd($request->valid_till);
        $proposal->sub_total = $request->sub_total;
        $proposal->total = $request->total;
        $proposal->currency_id = $request->currency_id;
        $proposal->status = $request->status;
        $proposal->note = trim_editor($request->note);
        $proposal->discount = round($request->discount_value, 2);
        $proposal->discount_type = $request->discount_type;
        $proposal->signature_approval = ($request->require_signature) ? 1 : 0;
        $proposal->description = trim_editor($request->description);
        $proposal->save();

        return Reply::redirect(route('proposals.show', $proposal->id), __('messages.updateSuccess'));
    }

    public function destroy($id)
    {
        $proposal = Proposal::findOrFail($id);
        $this->deleteLeadProposalsPermission = user()->permission('delete_lead_proposals');
        abort_403(!($this->deleteLeadProposalsPermission == 'all' || ($this->deleteLeadProposalsPermission == 'added' && $proposal->added_by == user()->id)));

        Proposal::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function sendProposal($id)
    {
        $proposal = Proposal::findOrFail($id);

        if (request()->data_type != 'mark_as_send') {
            event(new NewProposalEvent($proposal, 'new'));
        }

        $proposal->send_status = 1;

        $proposal->save();

        if (request()->data_type == 'mark_as_send') {
            return Reply::success(__('messages.proposalMarkAsSent'));
        }

        return Reply::success(__('messages.proposalSendSuccess'));

    }

    public function download($id)
    {
        $this->proposal = Proposal::with('unit')->findOrFail($id);
        $this->viewLeadProposalsPermission = user()->permission('view_lead_proposals');
        abort_403(!($this->viewLeadProposalsPermission == 'all' || ($this->viewLeadProposalsPermission == 'added' && $this->proposal->added_by == user()->id)));

        try {
            // Debug: Log proposal data
            $userId = user() ? user()->id : null;
            Log::info('Proposal download started', [
                'proposal_id' => $id,
                'company_id' => $this->proposal->company_id,
                'user_id' => $userId,
                'proposal_data' => $this->proposal->toArray()
            ]);

            $pdfOption = $this->domPdfObjectForDownload($id);
            
            // Debug: Log PDF generation success
            Log::info('PDF generation successful', [
                'proposal_id' => $id,
                'filename' => $pdfOption['fileName'] ?? 'unknown'
            ]);

            $pdf = $pdfOption['pdf'];
            $filename = $pdfOption['fileName'];

            // Clear any existing output buffer
            if (ob_get_level()) {
                ob_end_clean();
            }

            // Set headers manually for better control
            $headers = [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '.pdf"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ];

            return response($pdf->output(), 200, $headers);
        } catch (\Exception $e) {
            // Log the error for debugging with full stack trace
            $userId = user() ? user()->id : null;
            Log::error('Proposal PDF download failed: ' . $e->getMessage(), [
                'proposal_id' => $id,
                'user_id' => $userId,
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            // Return a user-friendly error response
            return response()->json([
                'error' => true,
                'message' => 'Failed to generate PDF. Please try again or contact support.',
                'details' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    public function domPdfObjectForDownload($id)
    {
        $this->proposal = Proposal::with('items', 'lead', 'lead.contact', 'currency')->findOrFail($id);
        $this->invoiceSetting = InvoiceSetting::withoutGlobalScopes()->where('company_id', $this->proposal->company_id)->first();

        // Validate invoice setting exists
        if (!$this->invoiceSetting) {
            throw new \Exception('Invoice settings not found for company ID: ' . $this->proposal->company_id);
        }

        // Validate template exists
        if (empty($this->invoiceSetting->template)) {
            throw new \Exception('Invoice template not configured for company ID: ' . $this->proposal->company_id);
        }

        // Set locale with fallback
        $locale = $this->invoiceSetting->locale ?? 'en';
        App::setLocale($locale);
        Carbon::setLocale($locale);

        if ($this->proposal->discount > 0) {
            if ($this->proposal->discount_type == 'percent') {
                $this->discount = (($this->proposal->discount / 100) * $this->proposal->sub_total);
            }
            else {
                $this->discount = $this->proposal->discount;
            }
        }
        else {
            $this->discount = 0;
        }

        $taxList = array();

        $items = ProposalItem::whereNotNull('taxes')
            ->where('proposal_id', $this->proposal->id)
            ->get();

        foreach ($items as $item) {

            // Validate taxes is not null or empty
            if (!empty($item->taxes)) {
                $taxes = json_decode($item->taxes);
                
                if (is_array($taxes)) {
                    foreach ($taxes as $tax) {
                        $this->tax = ProposalItem::taxbyid($tax)->first();

                        if ($this->tax) {
                            if (!isset($taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'])) {

                                if ($this->proposal->calculate_tax == 'after_discount' && $this->discount > 0) {
                                    $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = ($item->amount - ($item->amount / $this->proposal->sub_total) * $this->discount) * ($this->tax->rate_percent / 100);

                                }
                                else {
                                    $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $item->amount * ($this->tax->rate_percent / 100);
                                }

                            }
                            else {
                                if ($this->proposal->calculate_tax == 'after_discount' && $this->discount > 0) {
                                    $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + (($item->amount - ($item->amount / $this->proposal->sub_total) * $this->discount) * ($this->tax->rate_percent / 100));

                                }
                                else {
                                    $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] = $taxList[$this->tax->tax_name . ': ' . $this->tax->rate_percent . '%'] + ($item->amount * ($this->tax->rate_percent / 100));
                                }
                            }
                        }
                    }
                }
            }
        }

        $this->taxes = $taxList;

        $this->company = $this->proposal->company;

        // $pdf = app('dompdf.wrapper');

        $pdf = new Dompdf([
            'defaultFont' => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'tempDir' => storage_path('app/dompdf'),
            'logOutputFile' => storage_path('logs/dompdf.log'),
            'fontCache' => storage_path('fonts'),
            'chroot' => base_path(),
            'allowedProtocols' => [
                'file://' => ['rules' => []],
                'http://' => ['rules' => []],
                'https://' => ['rules' => []]
            ],
            'enableFontSubsetting' => false,
            'pdfBackend' => 'CPDF',
            'dpi' => 100,
            'defaultMediaType' => 'screen',
            'defaultPaperSize' => 'a4',
            'defaultPaperOrientation' => 'portrait',
            'enablePhp' => false,
            'enableJavascript' => true,
            'enableRemote' => true,
            'fontHeightRatio' => 1.0,
            'enableHtml5Parser' => true,
            'isFontSubsettingEnabled' => true,
        ]);

        // $pdf->setOption('enable_php', true);
        // $pdf->setOption('isHtml5ParserEnabled', true);
        // $pdf->setOption('isRemoteEnabled', true);
        // $pdf->set_option('defaultFont', 'DejaVu Sans');
        // $pdf->setOption('tempDir', '/tmp/dompdf');

        // Validate template file exists
        $templateView = 'proposals.pdf.' . $this->invoiceSetting->template;
        
        if (!view()->exists($templateView)) {
            throw new \Exception('Template view not found: ' . $templateView);
        }

        $customCss = '<style>
                @page { margin: 50px; }
                body { 
                    font-family: DejaVu Sans, Arial, sans-serif !important; 
                    font-size: 18px;
                    line-height: 1.2;
                    direction: rtl;
                    unicode-bidi: embed;
                }
                * { 
                    text-transform: none !important; 
                    font-family: DejaVu Sans, Arial, sans-serif !important; 
                }
                h1, h2, h3, h4, h5, h6 {
                    font-family: DejaVu Sans, Arial, sans-serif !important;
                    font-weight: bold;
                }
 
                .description { font-family: DejaVu Sans, Arial, sans-serif !important; }
                .word-break { word-wrap: break-word; word-break: break-all; }
            </style>';

        $pdf->set_option('defaultFont', 'Amiri');

        // $pdf->loadHTML($customCss . view($templateView, $this->data)->render());
        $pdf->loadHTML('<h1>Test</h1>');
        
         // Set paper size and orientation
         $pdf->setPaper('a4', 'portrait');
 
         // Render the PDF
         $pdf->render();

        $filename = __('modules.lead.proposal') . '-' . $this->proposal->id;

        return [
            'pdf' => $pdf,
            'fileName' => $filename
        ];

    }

    public function deleteProposalItemImage(Request $request)
    {
        $item = ProposalItemImage::where('proposal_item_id', $request->invoice_item_id)->first();

        if ($item) {
            Files::deleteFile($item->hashname, 'proposal-files/' . $item->id . '/');
            $item->delete();
        }

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function getclients($id)
    {
        $client_data = Product::where('unit_id', $id)->get();
        $unitId = UnitType::where('id', $id)->first();

        return Reply::dataOnly(['status' => 'success', 'data' => $client_data, 'type' => $unitId]);
    }

    public function addItem(Request $request)
    {
        $this->items = Product::findOrFail($request->id);
        $this->invoiceSetting = invoice_setting();

        $exchangeRate = Currency::findOrFail($request->currencyId);

        if (!is_null($exchangeRate) && !is_null($exchangeRate->exchange_rate) && $exchangeRate->exchange_rate > 0) {
            if ($this->items->total_amount != '') {
                /** @phpstan-ignore-next-line */
                $this->items->price = floor($this->items->total_amount / $exchangeRate->exchange_rate);
            }
            else {

                $this->items->price = floatval($this->items->price) / floatval($exchangeRate->exchange_rate);
            }
        }
        else {
            if ($this->items->total_amount != '') {
                $this->items->price = $this->items->total_amount;
            }
        }

        $this->items->price = number_format((float)$this->items->price, 2, '.', '');
        $this->taxes = Tax::all();
        $this->units = UnitType::all();
        $view = view('proposals.ajax.add_item', $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'view' => $view]);
    }

}
