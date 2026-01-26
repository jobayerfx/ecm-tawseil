<?php

namespace Modules\ServerManager\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\ServerManager\Entities\ServerDomain;
use Modules\ServerManager\Entities\ServerHosting;
use Modules\ServerManager\Entities\ServerProvider;
use Modules\ServerManager\Entities\ServerSetting;
use Modules\ServerManager\DataTables\DomainDataTable;
use Modules\ServerManager\Http\Requests\Domain\StoreDomainRequest;
use Modules\ServerManager\Http\Requests\Domain\UpdateDomainRequest;
use App\Helper\Reply;
use App\Models\User;
use App\Models\Project;
use App\Models\ClientDetails;
use Maatwebsite\Excel\Facades\Excel;
use Modules\ServerManager\Exports\DomainExport;
use Carbon\Carbon;
use Modules\ServerManager\Services\DnsLookupService;

class DomainController extends AccountBaseController
{
    public $pageTitle;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('servermanager::app.menu.domains');
        $this->middleware(function ($request, $next) {
            abort_403(!in_array(ServerSetting::MODULE_NAME, $this->user->modules));

            return $next($request);
        });
    }

    public function index(DomainDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_domain');
        abort_403(!in_array($viewPermission, ['all', 'added']));

        $companyId = company()->id;

        $this->users = User::where('company_id', $companyId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $this->hostings = ServerHosting::where('company_id', $companyId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $this->providers = ServerProvider::where('company_id', $companyId)
            ->where('status', 'active')
            ->whereIn('type', ['domain', 'both'])
            ->orderBy('name')
            ->get();

        $this->data['pageTitle'] = $this->pageTitle;
        $this->clients = User::allClients();

        return $dataTable->render('servermanager::domain.index', $this->data);
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_domain');
        abort_403(!in_array($this->addPermission, ['all']));

        $viewProviderPermission = user()->permission('view_provider');
        $this->pageTitle = __('servermanager::app.domain.addDomain');

        $this->users = User::where('company_id', company()->id)
            ->where('status', 'active')
            ->get();

        $this->hostings = ServerHosting::where('company_id', company()->id)
            ->where('status', 'active')
            ->get();

        $this->projects = Project::where('company_id', company()->id)
            ->where('status', '!=', 'canceled')
            ->orderBy('project_name')
            ->get();

        $this->clients = User::allClients(null, overRidePermission:($this->addPermission == 'all' ? 'all' : null));

        if ($viewProviderPermission == 'none') {
            $this->providers = collect([]);
        } else {
            $this->providers = ServerProvider::where('company_id', company()->id)
                ->where('status', 'active')
                ->whereIn('type', ['domain', 'both'])
                ->orderBy('name');

            if ($viewProviderPermission == 'added') {
                $this->providers = $this->providers->where('created_by', user()->id);
            }

            $this->providers = $this->providers->get();
        }

        $this->data['pageTitle'] = $this->pageTitle;

        if (request()->ajax()) {
            $html = view('servermanager::domain.ajax.create', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'servermanager::domain.ajax.create';

        return view('servermanager::domain.create', $this->data);
    }

    public function store(StoreDomainRequest $request)
    {
        $addPermission = user()->permission('add_domain');
        abort_403(!in_array($addPermission, ['all']));

        ServerDomain::create([
            'company_id' => company()->id,
            'domain_name' => $request->domain_name,
            'domain_provider' => $request->domain_provider,
            'provider_url' => $request->provider_url,
            'domain_type' => $request->domain_type,
            'registration_date' => $request->registration_date,
            'expiry_date' => $request->expiry_date,
            'renewal_date' => $request->renewal_date,
            'username' => $request->username,
            'password' => $request->password ?: null,
            'annual_cost' => $request->annual_cost,
            'billing_cycle' => $request->billing_cycle,
            'status' => $request->status,
            'hosting_id' => $request->hosting_id,
            'registrar_url' => $request->registrar_url,
            'registrar_username' => $request->registrar_username,
            'registrar_password' => $request->registrar_password ?: null,
            'registrar_status' => $request->registrar_status,
            'project_id' => $request->project_id,
            'client_id' => $request->client_id,
            'dns_provider' => $request->dns_provider,
            'dns_status' => $request->dns_status,
            'nameservers' => $request->nameservers,
            'dns_records' => $request->dns_records,
            'auto_renewal' => $request->auto_renewal == 1 ? 'enabled' : 'disabled',
            'whois_protection' => $request->whois_protection == 1 ? 'enabled' : 'disabled',
            'expiry_notification' => $request->expiry_notification == 1 ? 1 : 0,
            'notification_days_before' => $request->notification_days_before,
            'notification_time_unit' => $request->notification_time_unit,
            'notes' => $request->notes,
            'created_by' => user()->id,
        ]);

        return Reply::successWithData('Domain created successfully', ['redirectUrl' => route('domain.index')]);
    }

    public function show($id)
    {
        $viewPermission = user()->permission('view_domain');
        abort_403(!in_array($viewPermission, ['all', 'added']));

        $domain = ServerDomain::where('company_id', company()->id)
            ->with(['assignedTo', 'createdBy', 'hosting', 'project', 'client.user'])
            ->findOrFail($id);

        $editPermission = user()->permission('edit_domain');
        $deletePermission = user()->permission('delete_domain');

        $this->domain = $domain;
        $this->editPermission = $editPermission;
        $this->deletePermission = $deletePermission;
        $this->data['pageTitle'] = $this->pageTitle;
        $this->data['editPermission'] = $editPermission;
        $this->data['deletePermission'] = $deletePermission;

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'servermanager::domain.ajax.show';

        return view('servermanager::domain.show', $this->data);
    }

    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_domain');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $viewProviderPermission = user()->permission('view_provider');

        $this->pageTitle = __('servermanager::app.domain.editDomain');

        $this->domain = ServerDomain::where('company_id', company()->id)->findOrFail($id);

        $this->users = User::where('company_id', company()->id)
            ->where('status', 'active')
            ->get();

        $this->hostings = ServerHosting::where('company_id', company()->id)
            ->where('status', 'active')
            ->get();

        $this->projects = Project::where('company_id', company()->id)
            ->where('status', '!=', 'canceled')
            ->orderBy('project_name')
            ->get();

        $this->clients = User::allClients(null, overRidePermission:($this->editPermission == 'all' ? 'all' : null));

        if ($viewProviderPermission == 'none') {
            $this->providers = collect([]);
        } else {
            $this->providers = ServerProvider::where('company_id', company()->id)
                ->where('status', 'active')
                ->whereIn('type', ['domain', 'both'])
                ->orderBy('name');

            if ($viewProviderPermission == 'added') {
                $this->providers = $this->providers->where('created_by', user()->id);
            }

            $this->providers = $this->providers->get();
        }

        $this->data['pageTitle'] = $this->pageTitle;

        if (request()->ajax()) {
            $html = view('servermanager::domain.ajax.edit', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'servermanager::domain.ajax.edit';

        return view('servermanager::domain.create', $this->data);
    }

    public function update(UpdateDomainRequest $request, $id)
    {
        $editPermission = user()->permission('edit_domain');
        abort_403(!in_array($editPermission, ['all', 'added']));

        $domain = ServerDomain::where('company_id', company()->id)->findOrFail($id);

        $updateData = [
            'domain_name' => $request->domain_name,
            'domain_provider' => $request->domain_provider,
            'provider_url' => $request->provider_url,
            'domain_type' => $request->domain_type,
            'registration_date' => $request->registration_date,
            'expiry_date' => $request->expiry_date,
            'renewal_date' => $request->renewal_date,
            'username' => $request->username,
            'annual_cost' => $request->annual_cost,
            'billing_cycle' => $request->billing_cycle,
            'status' => $request->status,
            'hosting_id' => $request->hosting_id,
            'registrar_url' => $request->registrar_url,
            'registrar_username' => $request->registrar_username,
            'registrar_status' => $request->registrar_status,
            'project_id' => $request->project_id,
            'client_id' => $request->client_id,
            'dns_provider' => $request->dns_provider,
            'dns_status' => $request->dns_status,
            'nameservers' => $request->nameservers,
            'dns_records' => $request->dns_records,
            'auto_renewal' => $request->auto_renewal ? 'enabled' : 'disabled',
            'whois_protection' => $request->whois_protection ? 'enabled' : 'disabled',
            'expiry_notification' => $request->expiry_notification,
            'notification_days_before' => $request->notification_days_before,
            'notification_time_unit' => $request->notification_time_unit,
            'notes' => $request->notes,
            'updated_by' => user()->id,
        ];

        // Only update password fields if they are provided
        if (!empty($request->password)) {
            $updateData['password'] = $request->password;
        }
        if (!empty($request->registrar_password)) {
            $updateData['registrar_password'] = $request->registrar_password;
        }

        $domain->update($updateData);

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('domain.index')]);
    }

    public function destroy($id)
    {
        $deletePermission = user()->permission('delete_domain');
        abort_403(!in_array($deletePermission, ['all', 'added']));

        $domain = ServerDomain::where('company_id', company()->id)->findOrFail($id);
        $domain->delete();

        return Reply::successWithData('Domain deleted successfully', ['redirectUrl' => route('domain.index')]);
    }

    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
        case 'delete':
            $this->deleteRecords($request);

            return Reply::success(__('messages.deleteSuccess'));
        case 'change-status':
            $this->changeBulkStatus($request);

            return Reply::success(__('messages.updateSuccess'));
        default:
            return Reply::error(__('messages.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_domain') != 'all');

        $ids = explode(',', $request->row_ids);

        ServerDomain::whereIn('id', $ids)->delete();
    }

    protected function changeBulkStatus($request)
    {
        abort_403(user()->permission('edit_domain') != 'all');

        // Update tasks based on the requested status
        $domainIds = explode(',', $request->row_ids);

        ServerDomain::whereIn('id', $domainIds)->update(['status' => $request->status]);

    }

    public function exportAllDomains(Request $request)
    {
        abort_403(!canDataTableExport());

        $startDate = $request->query('startDate', 'null');
        $endDate = $request->query('endDate', 'null');
        $dateFilterOn = $request->query('dateFilterOn', 'null');

        if($dateFilterOn == "null"){
            $dateFilterOn = null;
        }

        $exportAll = false;
        if($startDate == "null" && $endDate == "null"){
            $exportAll = true;
        }

        $startDate = $startDate !== "null" ? Carbon::createFromFormat(company()->date_format, $startDate) : now();
        $endDate = $endDate !== "null" ? Carbon::createFromFormat(company()->date_format, $endDate) : now();
        $today = now();

        if ($startDate->isSameDay($today) && $endDate->isSameDay($today)) {
            $dateRange = 'Today_' . $today->format('d-m-Y');
        } else {
            $dateRange = $startDate->format('d-m-Y') . '_To_' . $endDate->format('d-m-Y');
        }

        return Excel::download(new DomainExport($startDate, $endDate, $exportAll, $dateFilterOn), 'Domain_From_' . $dateRange . '.xlsx');
    }

    /**
     * Get DNS details for a domain
     */
    public function getDnsDetails(Request $request, $id)
    {
        $domain = ServerDomain::where('company_id', company()->id)
            ->where('id', $id)
            ->firstOrFail();

        $dnsService = new DnsLookupService();
        $dnsData = $dnsService->getFormattedDnsRecords($domain->domain_name);

        return Reply::dataOnly([
            'status' => 'success',
            'data' => $dnsData
        ]);
    }

    /**
     * Get DNS health check for a domain
     */
    public function getDnsHealth(Request $request, $id)
    {
        $domain = ServerDomain::where('company_id', company()->id)
            ->where('id', $id)
            ->firstOrFail();

        $dnsService = new DnsLookupService();
        $healthData = $dnsService->checkDnsHealth($domain->domain_name);

        return Reply::dataOnly([
            'status' => 'success',
            'data' => $healthData
        ]);
    }
}
