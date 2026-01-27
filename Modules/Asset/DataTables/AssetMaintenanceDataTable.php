<?php

namespace Modules\Asset\DataTables;

use App\DataTables\BaseDataTable;
use Carbon\Carbon;
use Modules\Asset\Entities\AssetMaintenance;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class AssetMaintenanceDataTable extends BaseDataTable
{
    public function __construct()
    {
        parent::__construct();
        $this->editMaintenancePermission = user()->permission('edit_asset');
        $this->deleteMaintenancePermission = user()->permission('delete_asset');
        $this->viewMaintenancePermission = user()->permission('view_asset');
    }

    /**
     * Build DataTable class.
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $actions = '<div class="task_view">
                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link" id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                $actions .= '<a href="' . route('asset-maintenance.show', [$row->id]) . '" class="dropdown-item openRightModal"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                if ($this->editMaintenancePermission == 'all'
                    || (($this->editMaintenancePermission == 'added' || $this->editMaintenancePermission == 'both') && user()->id == $row->created_by)
                ) {
                    $actions .= '<a class="dropdown-item openRightModal" href="' . route('asset-maintenance.edit', [$row->id]) . '">
                                    <i class="fa fa-edit mr-2"></i>
                                    ' . __('app.edit') . '
                            </a>';
                }

                if ($this->deleteMaintenancePermission == 'all' || ($this->deleteMaintenancePermission == 'added' && user()->id == $row->created_by)) {
                    $actions .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-maintenance-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i>
                                ' . trans('app.delete') . '
                            </a>';
                }

                $actions .= '</div> </div> </div>';

                return $actions;
            })
            ->editColumn('asset_name', function ($row) {
                if ($row->asset) {
                    return '<a href="' . route('assets.show', [$row->asset_id]) . '" class="text-darkest-grey openRightModal">' . $row->asset->name . '</a>';
                }
                return '--';
            })
            ->editColumn('type', function ($row) {
                if (!$row->type) {
                    return '--';
                }
                $typeClass = $row->type == 'planned' ? 'badge-primary' : 'badge-warning';
                return '<span class="badge ' . $typeClass . '">' . __('asset::app.' . $row->type) . '</span>';
            })
            ->addColumn('type_export', function ($row) {
                if (!$row->type) {
                    return '--';
                }
                return __('asset::app.' . $row->type);
            })
            ->editColumn('status', function ($row) {
                if (!$row->status) {
                    return '--';
                }
                $class = AssetMaintenance::STATUSES;
                $statusClass = isset($class[$row->status]) ? $class[$row->status] : 'text-dark-grey';
                $statusText = __('asset::app.' . $row->status);
                return '<i class="fa fa-circle mr-1 ' . $statusClass . ' f-10"></i>' . $statusText;
            })
            ->addColumn('status_export', function ($row) {
                if (!$row->status) {
                    return '--';
                }
                return __('asset::app.' . $row->status);
            })
            ->editColumn('scheduled_date', function ($row) {
                return $row->scheduled_date->translatedFormat($this->company->date_format);
            })
            ->editColumn('due_date', function ($row) {
                if ($row->due_date) {
                    return $row->due_date->translatedFormat($this->company->date_format);
                }
                return '--';
            })
            ->editColumn('assigned_to', function ($row) {
                if ($row->assignedTo) {
                    return view('components.employee', ['user' => $row->assignedTo]);
                }
                return '--';
            })
            ->addColumn('asset_name_export', fn($row) => $row->asset ? $row->asset->name : '--')
            ->rawColumns(['action', 'asset_name', 'type', 'status', 'assigned_to']);
    }

    /**
     * Get query source of dataTable.
     */
    public function query(AssetMaintenance $model)
    {
        $request = $this->request();

        $model = $model->with('asset', 'assignedTo', 'addedBy')
            ->select('asset_maintenance.*');

        if (!is_null($request->asset_id) && $request->asset_id != 'all' && $request->asset_id != '') {
            $model->where('asset_maintenance.asset_id', $request->asset_id);
        }

        if (!is_null($request->status) && $request->status != 'all' && $request->status != '') {
            $model->where('asset_maintenance.status', $request->status);
        }

        if (!is_null($request->type) && $request->type != 'all' && $request->type != '') {
            $model->where('asset_maintenance.type', $request->type);
        }

        if (!is_null($request->startDate) && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = companyToDateString($request->startDate);
            $model->whereDate('asset_maintenance.scheduled_date', '>=', $startDate);
        }

        if (!is_null($request->endDate) && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = companyToDateString($request->endDate);
            $model->whereDate('asset_maintenance.scheduled_date', '<=', $endDate);
        }

        if (!is_null($request->todaySchedule) && $request->todaySchedule == 'true') {
            $model->whereDate('asset_maintenance.scheduled_date', today());
        }

        return $model;
    }

    /**
     * Optional method if you want to use html builder.
     */
    public function html()
    {
        $dataTable = $this->setBuilder('asset-maintenance-table', 0)
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["asset-maintenance-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    })
                }',
            ]);

        if (canDataTableExport()) {
            $dataTable->buttons(Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]));
        }

        return $dataTable;
    }

    /**
     * Get columns.
     */
    protected function getColumns()
    {
        return [
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => !showId(), 'title' => '#'],
            __('app.id') => ['data' => 'id', 'name' => 'id', 'visible' => showId(), 'title' => __('app.id')],
            __('asset::app.assetName') => ['data' => 'asset_name', 'name' => 'asset.name', 'exportable' => false, 'title' => __('asset::app.assetName')],
            __('asset::app.assetName') . ' ' . __('app.export') => ['data' => 'asset_name_export', 'name' => 'asset.name', 'visible' => false, 'title' => __('asset::app.assetName')],
            __('asset::app.maintenanceTitle') => ['data' => 'title', 'name' => 'title', 'title' => __('asset::app.maintenanceTitle')],
            __('asset::app.maintenanceType') => ['data' => 'type', 'name' => 'type', 'exportable' => false, 'title' => __('asset::app.maintenanceType')],
            __('asset::app.maintenanceType') . ' ' . __('app.export') => ['data' => 'type_export', 'name' => 'type_export', 'visible' => false, 'title' => __('asset::app.maintenanceType')],
            __('asset::app.scheduledDate') => ['data' => 'scheduled_date', 'name' => 'scheduled_date', 'title' => __('asset::app.scheduledDate')],
            __('asset::app.dueDate') => ['data' => 'due_date', 'name' => 'due_date', 'title' => __('asset::app.dueDate')],
            __('asset::app.status') => ['data' => 'status', 'name' => 'status', 'exportable' => false, 'title' => __('asset::app.status')],
            __('asset::app.status') . ' ' . __('app.export') => ['data' => 'status_export', 'name' => 'status_export', 'visible' => false, 'title' => __('asset::app.status')],
            __('asset::app.assignedTo') => ['data' => 'assigned_to', 'name' => 'assignedTo.name', 'exportable' => false, 'title' => __('asset::app.assignedTo')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->width(150)
                ->addClass('text-right pr-20')
        ];
    }
}

