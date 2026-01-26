<?php

namespace Modules\ServerManager\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\ServerManager\Entities\ServerHosting;
use Modules\ServerManager\Entities\ServerLog;
use Modules\ServerManager\Entities\ServerType;
use Modules\ServerManager\Entities\ServerProvider;
use Modules\ServerManager\Entities\ServerSetting;
use Modules\ServerManager\DataTables\HostingDataTable;
use Modules\ServerManager\Http\Requests\Hosting\StoreHostingRequest;
use Modules\ServerManager\Http\Requests\Hosting\UpdateHostingRequest;
use Modules\ServerManager\Services\HostingService;
use Modules\ServerManager\Traits\HasServerManagerPermissions;
use App\Helper\Reply;
use App\Models\User;
use App\Models\Project;
use App\Models\ClientDetails;
use Maatwebsite\Excel\Facades\Excel;
use Modules\ServerManager\Exports\HostingExport;
use Carbon\Carbon;

class HostingController extends AccountBaseController
{
    use HasServerManagerPermissions;

    public $pageTitle;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('servermanager::app.menu.hosting');
        $this->middleware(function ($request, $next) {
            abort_403(!in_array(ServerSetting::MODULE_NAME, $this->user->modules));

            return $next($request);
        });
    }

    public function index(HostingDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_hosting');
        abort_403(!in_array($viewPermission, ['all', 'added']));

        $this->users = User::where('company_id', company()->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $this->providers = ServerProvider::where('company_id', company()->id)
            ->where('status', 'active')
            ->whereIn('type', ['hosting', 'both'])
            ->orderBy('name')
            ->get();

        $this->serverTypes = ServerType::where('company_id', company()->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $this->data['pageTitle'] = $this->pageTitle;
        $this->clients = User::allClients();

        return $dataTable->render('servermanager::hosting.index', $this->data);
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_hosting');
        abort_403(!in_array($this->addPermission, ['all']));

        $viewProviderPermission = user()->permission('view_provider');

        $this->pageTitle = __('servermanager::app.hosting.addHosting');

        $this->users = User::where('company_id', company()->id)
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
                ->whereIn('type', ['hosting', 'both'])
                ->orderBy('name');

            if ($viewProviderPermission == 'added') {
                $this->providers = $this->providers->where('created_by', user()->id);
            }

            $this->providers = $this->providers->get();
        }

        $this->serverTypes = ServerType::where('company_id', company()->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $this->data['pageTitle'] = $this->pageTitle;

        if (request()->ajax()) {
            $html = view('servermanager::hosting.ajax.create', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'servermanager::hosting.ajax.create';

        return view('servermanager::hosting.create', $this->data);
    }

    public function store(StoreHostingRequest $request)
    {
        abort_403(!$this->canAddHosting());

        ServerHosting::create([
            'name' => $request->name,
            'hosting_provider' => $request->hosting_provider,
            'provider_url' => $request->provider_url,
            'server_type' => $request->server_type,
            'purchase_date' => $request->purchase_date,
            'renewal_date' => $request->renewal_date,
            'expiry_notification' => $request->expiry_notification == 1 ? 1 : 0,
            'notification_days_before' => $request->notification_days_before,
            'notification_time_unit' => $request->notification_time_unit,
            'status' => $request->status,
            'notes' => $request->notes,
            'ssl_certificate' => $request->ssl_certificate == 1 ? 1 : 0,
            'ssl_expiry_date' => $request->ssl_expiry_date,
            'ssl_type' => $request->ssl_type,
            'ssl_certificate_info' => $request->ssl_certificate_info,
            'server_location' => $request->server_location,
            'ip_address' => $request->ip_address,
            'annual_cost' => $request->annual_cost,
            'billing_cycle' => $request->billing_cycle,
            'disk_space' => $request->disk_space,
            'bandwidth' => $request->bandwidth,
            'control_panel' => $request->control_panel,
            'cpanel_url' => $request->cpanel_url,
            'username' => $request->username,
            'password' => $request->password ?: null,
            'project' => $request->project,
            'client' => $request->client_id,
            'database_limit' => $request->database_limit,
            'email_limit' => $request->email_limit,
            'ftp_username' => $request->ftp_username,
            'ftp_password' => $request->ftp_password ?: null,
            'created_by' => user()->id,
            'company_id' => company()->id,
        ]);
        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('hosting.index')]);
    }

    public function show($id)
    {
        $viewPermission = user()->permission('view_hosting');
        abort_403(!in_array($viewPermission, ['all', 'added']));

        $hosting = ServerHosting::where('company_id', company()->id)
            ->with(['assignedTo', 'createdBy', 'domains', 'projectDetails', 'client.user', 'clientDetails'])
            ->findOrFail($id);

        $this->hosting = $hosting;
        $this->data['pageTitle'] = $this->pageTitle;

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'servermanager::hosting.ajax.show';

        return view('servermanager::hosting.show', $this->data);
    }

    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_hosting');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $viewProviderPermission = user()->permission('view_provider');

        $this->pageTitle = __('servermanager::app.hosting.editHosting');

        $this->hosting = ServerHosting::where('company_id', company()->id)->findOrFail($id);
        $this->users = User::where('company_id', company()->id)
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
                ->whereIn('type', ['hosting', 'both'])
                ->orderBy('name');

            if ($viewProviderPermission == 'added') {
                $this->providers = $this->providers->where('created_by', user()->id);
            }

            $this->providers = $this->providers->get();
        }

        $this->serverTypes = ServerType::where('company_id', company()->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $this->data['pageTitle'] = $this->pageTitle;

        if (request()->ajax()) {
            $html = view('servermanager::hosting.ajax.edit', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'servermanager::hosting.ajax.edit';

        return view('servermanager::hosting.create', $this->data);
    }

    public function update(UpdateHostingRequest $request, $id)
    {
        $editPermission = user()->permission('edit_hosting');
        abort_403(!in_array($editPermission, ['all', 'added']));

        $hosting = ServerHosting::where('company_id', company()->id)->findOrFail($id);

        $updateData = [
            'name' => $request->name,
            'hosting_provider' => $request->hosting_provider,
            'provider_url' => $request->provider_url,
            'server_type' => $request->server_type,
            'purchase_date' => $request->purchase_date,
            'renewal_date' => $request->renewal_date,
            'expiry_notification' => $request->expiry_notification == 1 ? 1 : 0,
            'notification_days_before' => $request->notification_days_before,
            'notification_time_unit' => $request->notification_time_unit,
            'status' => $request->status,
            'notes' => $request->notes,
            'ssl_certificate' => $request->ssl_certificate == 1 ? 1 : 0,
            'ssl_expiry_date' => $request->ssl_expiry_date,
            'ssl_type' => $request->ssl_type,
            'ssl_certificate_info' => $request->ssl_certificate_info,
            'server_location' => $request->server_location,
            'ip_address' => $request->ip_address,
            'annual_cost' => $request->annual_cost,
            'billing_cycle' => $request->billing_cycle,
            'disk_space' => $request->disk_space,
            'bandwidth' => $request->bandwidth,
            'control_panel' => $request->control_panel,
            'cpanel_url' => $request->cpanel_url,
            'username' => $request->username,
            'project' => $request->project,
            'client' => $request->client_id,
            'database_limit' => $request->database_limit,
            'email_limit' => $request->email_limit,
            'ftp_username' => $request->ftp_username,
            'updated_by' => user()->id,
        ];

        // Only update password fields if they are provided
        if (!empty($request->password)) {
            $updateData['password'] = $request->password;
        }
        if (!empty($request->ftp_password)) {
            $updateData['ftp_password'] = $request->ftp_password;
        }

        $hosting->update($updateData);

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('hosting.index')]);
    }

    public function destroy($id)
    {
        $deletePermission = user()->permission('delete_hosting');
        abort_403(!in_array($deletePermission, ['all', 'added']));

        $hosting = ServerHosting::where('company_id', company()->id)->findOrFail($id);
        $hosting->delete();

        return Reply::successWithData('Hosting deleted successfully', ['redirectUrl' => route('hosting.index')]);
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
        abort_403(user()->permission('delete_hosting') != 'all');

        $ids = explode(',', $request->row_ids);

        ServerHosting::whereIn('id', $ids)->delete();
    }

    protected function changeBulkStatus($request)
    {
        abort_403(user()->permission('edit_hosting') != 'all');

        // Update tasks based on the requested status
        $hostingIds = explode(',', $request->row_ids);

        ServerHosting::whereIn('id', $hostingIds)->update(['status' => $request->status]);

    }

    public function exportAllHostings(Request $request)
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

        return Excel::download(new HostingExport($startDate, $endDate, $exportAll, $dateFilterOn), 'Hosting_From_' . $dateRange . '.xlsx');
    }
}
