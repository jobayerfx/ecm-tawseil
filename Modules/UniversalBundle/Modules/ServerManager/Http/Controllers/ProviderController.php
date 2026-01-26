<?php

namespace Modules\ServerManager\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\ServerManager\Entities\ServerProvider;
use Modules\ServerManager\Entities\ServerSetting;
use Modules\ServerManager\DataTables\ProviderDataTable;
use Modules\ServerManager\Http\Requests\Provider\StoreProviderRequest;
use Modules\ServerManager\Http\Requests\Provider\UpdateProviderRequest;
use App\Helper\Reply;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use Modules\ServerManager\Exports\ProviderExport;
use Carbon\Carbon;

class ProviderController extends AccountBaseController
{
    public $pageTitle;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('servermanager::app.menu.providerList');
        $this->middleware(function ($request, $next) {
            abort_403(!in_array(ServerSetting::MODULE_NAME, $this->user->modules));

            return $next($request);
        });
    }

    public function index(ProviderDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_provider');
        abort_403(!in_array($viewPermission, ['all', 'added']));

        $companyId = company()->id;

        $users = User::where('company_id', $companyId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $this->users = $users;
        $this->data['pageTitle'] = $this->pageTitle;

        return $dataTable->render('servermanager::provider.index', $this->data);
    }

    public function create()
    {
        $addPermission = user()->permission('add_provider');
        abort_403(!in_array($addPermission, ['all']));

        $this->pageTitle = __('servermanager::app.provider.addProvider');

        $this->users = User::where('company_id', company()->id)
            ->where('status', 'active')
            ->get();

        $this->data['pageTitle'] = $this->pageTitle;

        if (request()->model == true) {
            return view('servermanager::provider.ajax.create_provider', $this->data);
        }

        if (request()->ajax()) {
            $html = view('servermanager::provider.ajax.create', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'servermanager::provider.ajax.create';

        return view('servermanager::provider.create', $this->data);
    }

    public function store(StoreProviderRequest $request)
    {
        $addPermission = user()->permission('add_provider');
        abort_403(!in_array($addPermission, ['all']));

        $provider = ServerProvider::create([
            'company_id' => company()->id,
            'name' => $request->name,
            'url' => $request->url,
            'type' => $request->type,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('provider.index')]);
    }

    public function show($id)
    {
        $viewPermission = user()->permission('view_provider');
        abort_403(!in_array($viewPermission, ['all', 'added']));

        $provider = ServerProvider::where('company_id', company()->id)
            ->with(['createdBy', 'updatedBy'])
            ->findOrFail($id);

        $editPermission = user()->permission('edit_provider');
        $deletePermission = user()->permission('delete_provider');

        $this->provider = $provider;
        $this->editPermission = $editPermission;
        $this->deletePermission = $deletePermission;
        $this->pageTitle = __('servermanager::app.provider.viewProvider');

        $this->data['pageTitle'] = $this->pageTitle;

        if (request()->ajax()) {
            $html = view('servermanager::provider.ajax.show', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'servermanager::provider.ajax.show';

        return view('servermanager::provider.show', $this->data);
    }

    public function edit($id)
    {
        $editPermission = user()->permission('edit_provider');
        abort_403(!in_array($editPermission, ['all', 'added']));

        $this->pageTitle = __('servermanager::app.provider.editProvider');

        $provider = ServerProvider::where('company_id', company()->id)->findOrFail($id);

        $this->provider = $provider;
        $this->data['pageTitle'] = $this->pageTitle;

        if (request()->ajax()) {
            $html = view('servermanager::provider.ajax.edit', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'servermanager::provider.ajax.edit';

        return view('servermanager::provider.create', $this->data);
    }

    public function update(UpdateProviderRequest $request, $id)
    {
        $editPermission = user()->permission('edit_provider');
        abort_403(!in_array($editPermission, ['all', 'added']));

        $provider = ServerProvider::where('company_id', company()->id)->findOrFail($id);

        $updateData = [
            'name' => $request->name,
            'url' => $request->url,
            'type' => $request->type,
            'description' => $request->description,
            'status' => $request->status,
        ];

        $provider->update($updateData);

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('provider.index')]);
    }

    public function destroy($id)
    {
        $deletePermission = user()->permission('delete_provider');
        abort_403(!in_array($deletePermission, ['all', 'added']));

        $provider = ServerProvider::where('company_id', company()->id)->findOrFail($id);
        $provider->delete();

        return Reply::successWithData('Provider deleted successfully', ['redirectUrl' => route('provider.index')]);
    }

    public function applyQuickAction(Request $request)
    {
        if ($request->action_type === 'delete') {
            $this->deleteRecords($request);
            return Reply::success(__('messages.deleteSuccess'));
        }

        if ($request->action_type === 'change-status') {
            $this->changeBulkStatus($request);
            return Reply::success(__('messages.statusUpdatedSuccessfully'));
        }

        return Reply::error(__('messages.selectAction'));
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_provider') != 'all');

        ServerProvider::whereIn('id', explode(',', $request->row_ids))->delete();
    }

    protected function changeBulkStatus($request)
    {
        abort_403(user()->permission('edit_provider') != 'all');

        $providerIds = explode(',', $request->row_ids);

        ServerProvider::whereIn('id', $providerIds)->update(['status' => $request->status]);
    }

    public function exportAllProviders(Request $request)
    {
        $exportPermission = user()->permission('export_provider');
        abort_403(!in_array($exportPermission, ['all', 'added', 'owned', 'both']));

        $startDate = null;
        $endDate = null;
        $exportAll = false;

        if ($request->start_date && $request->end_date) {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
        } else {
            $exportAll = true;
        }

        $dateRange = $exportAll ? 'All_Data' : $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d');

        return Excel::download(new ProviderExport($startDate, $endDate, $exportAll), 'Provider_From_' . $dateRange . '.xlsx');
    }

    public function getProviderUrl(Request $request)
    {
        $providerId = $request->provider_id;

        $provider = ServerProvider::where('company_id', company()->id)
            ->where('id', $providerId)
            ->first();

        if ($provider) {
            return Reply::dataOnly([
                'status' => 'success',
                'url' => $provider->url
            ]);
        }

        return Reply::dataOnly([
            'status' => 'error',
            'message' => 'Provider not found'
        ]);
    }
}
