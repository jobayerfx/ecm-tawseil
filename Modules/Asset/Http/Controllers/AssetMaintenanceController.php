<?php

namespace Modules\Asset\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\User;
use Carbon\Carbon;
use Modules\Asset\DataTables\AssetMaintenanceDataTable;
use Modules\Asset\Entities\Asset;
use Modules\Asset\Entities\AssetMaintenance;
use Modules\Asset\Entities\AssetSetting;
use Modules\Asset\Http\Requests\AssetMaintenance\StoreRequest;
use Modules\Asset\Http\Requests\AssetMaintenance\UpdateRequest;

class AssetMaintenanceController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(function ($request, $next) {
            abort_403(!in_array(AssetSetting::MODULE_NAME, $this->user->modules));
            $this->pageTitle = __('asset::app.assetMaintenance');

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(AssetMaintenanceDataTable $dataTable)
    {
        $this->viewMaintenancePermission = user()->permission('view_asset');
        abort_403($this->viewMaintenancePermission == 'none');

        // Update status for overdue items (skip completed and cancelled)
        AssetMaintenance::whereNotIn('status', ['completed', 'cancelled'])
            ->get()
            ->each(function ($maintenance) {
                $maintenance->updateStatus();
            });

        // Calculate statistics for cards
        $this->overdueMaintenance = AssetMaintenance::where('status', 'overdue')->count();
        $this->todaySchedule = AssetMaintenance::whereDate('scheduled_date', today())->count();
        $this->activeAssets = Asset::where('status', 'available')->count();

        $this->assets = Asset::all();
        $this->employees = User::allEmployees();
        $this->statuses = array_keys(AssetMaintenance::STATUSES);
        $this->types = array_keys(AssetMaintenance::TYPES);

        return $dataTable->render('asset::maintenance.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->addPermission = user()->permission('add_asset');
        abort_403($this->addPermission !== 'all');

        $this->maintenance = new AssetMaintenance;
        $this->assets = Asset::all();
        $this->employees = User::allEmployees();

        $this->view = 'asset::maintenance.ajax.create';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('asset::maintenance.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $this->addPermission = user()->permission('add_asset');
        abort_403($this->addPermission !== 'all');

        $maintenance = new AssetMaintenance;
        $maintenance->company_id = company()->id;
        $maintenance->asset_id = $request->asset_id;
        $maintenance->type = $request->type;
        $maintenance->title = $request->title;
        $maintenance->description = $request->description;
        $maintenance->scheduled_date = Carbon::createFromFormat($this->company->date_format, $request->scheduled_date)->format('Y-m-d');
        
        if ($request->has('due_date') && $request->due_date != '') {
            $maintenance->due_date = Carbon::createFromFormat($this->company->date_format, $request->due_date)->format('Y-m-d');
        }

        if ($request->has('assigned_to') && $request->assigned_to != '') {
            $maintenance->assigned_to = $request->assigned_to;
        }

        // Always set status - use request value or default to 'scheduled'
        $maintenance->status = $request->input('status', 'scheduled');
        $maintenance->created_by = user()->id;

        if ($request->has('notes')) {
            $maintenance->notes = $request->notes;
        }

        $maintenance->save();
        $maintenance->updateStatus();

        return Reply::successWithData(__('asset::app.maintenanceStoreSuccess'), ['redirectUrl' => route('asset-maintenance.index')]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $this->viewMaintenancePermission = user()->permission('view_asset');
        abort_403($this->viewMaintenancePermission == 'none');

        $this->maintenance = AssetMaintenance::with('asset', 'assignedTo', 'addedBy')->findOrFail($id);
        $this->view = 'asset::maintenance.ajax.show';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('asset::maintenance.create', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_asset');
        abort_403($this->editPermission !== 'all');

        $this->maintenance = AssetMaintenance::findOrFail($id);
        $this->assets = Asset::all();
        $this->employees = User::allEmployees();

        $this->view = 'asset::maintenance.ajax.edit';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('asset::maintenance.create', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, $id)
    {
        $this->editPermission = user()->permission('edit_asset');
        abort_403($this->editPermission !== 'all');

        $maintenance = AssetMaintenance::findOrFail($id);
        $maintenance->asset_id = $request->asset_id;
        $maintenance->type = $request->type;
        $maintenance->title = $request->title;
        $maintenance->description = $request->description;
        $maintenance->scheduled_date = Carbon::createFromFormat($this->company->date_format, $request->scheduled_date)->format('Y-m-d');
        
        if ($request->has('due_date') && $request->due_date != '') {
            $maintenance->due_date = Carbon::createFromFormat($this->company->date_format, $request->due_date)->format('Y-m-d');
        } else {
            $maintenance->due_date = null;
        }

        if ($request->has('assigned_to') && $request->assigned_to != '') {
            $maintenance->assigned_to = $request->assigned_to;
        } else {
            $maintenance->assigned_to = null;
        }

        // Always set status - use request value or keep existing
        $maintenance->status = $request->input('status', $maintenance->status);
        
        if ($maintenance->status == 'inprogress' && !$maintenance->started_at) {
            $maintenance->started_at = now();
        }
        
        if ($maintenance->status == 'completed') {
            $maintenance->completed_at = now();
            if ($request->has('completion_notes')) {
                $maintenance->completion_notes = $request->completion_notes;
            }
        } elseif ($maintenance->status != 'completed' && $maintenance->completed_at) {
            // If status changed from completed to something else, clear completed_at
            $maintenance->completed_at = null;
        }

        if ($request->has('notes')) {
            $maintenance->notes = $request->notes;
        }

        $maintenance->save();
        $maintenance->updateStatus();

        return Reply::successWithData(__('asset::app.maintenanceUpdateSuccess'), ['redirectUrl' => route('asset-maintenance.index')]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $deletePermission = user()->permission('delete_asset');
        abort_403(!in_array($deletePermission, ['all', 'added']));

        AssetMaintenance::destroy($id);

        return Reply::success(__('asset::app.maintenanceDeleteSuccess'));
    }
}

