<?php

namespace Modules\ServerManager\DataTables;

use App\DataTables\BaseDataTable;
use Modules\ServerManager\Entities\ServerProvider;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Scopes\CompanyScope;

class ProviderDataTable extends BaseDataTable
{
    private $addProviderPermission;
    private $editProviderPermission;
    private $deleteProviderPermission;
    private $viewProviderPermission;

    public function __construct()
    {
        parent::__construct();
        $this->addProviderPermission = user()->permission('add_provider');
        $this->editProviderPermission = user()->permission('edit_provider');
        $this->deleteProviderPermission = user()->permission('delete_provider');
        $this->viewProviderPermission = user()->permission('view_provider');
    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $datatables = datatables()->eloquent($query);

        $datatables->addColumn('check', function ($row) {
            return $this->checkBox($row);
        });

        $datatables->editColumn('name', function ($row) {
            return '<a href="' . route('provider.show', $row->id) . '" class="text-darkest-grey">' . $row->name . '</a>';
        });

        $datatables->editColumn('url', function ($row) {
            if ($row->url) {
                return '<a href="' . $row->url . '" target="_blank" class="text-darkest-grey">' . $row->url . '</a>';
            }
            return '-';
        });

        $datatables->editColumn('type', function ($row) {
            return '<span class="badge ' . $row->getTypeBadgeClass() . '">' . ucfirst($row->type) . '</span>';
        });

        $datatables->editColumn('status', function ($row) {
            return '<span class="badge ' . $row->getStatusBadgeClass() . '">' . ucfirst($row->status) . '</span>';
        });

        $datatables->addColumn('action', function ($row) {
            $action = '<div class="task_view">
                <div class="dropdown">
                    <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                        id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-options-vertical icons"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

            $action .= '<a href="' . route('provider.show', $row->id) . '" class="openRightModal dropdown-item"><i class="mr-2 fa fa-eye"></i>' . __('app.view') . '</a>';

            if (
                $this->editProviderPermission == 'all'
                || ($this->editProviderPermission == 'added' && user()->id == $row->created_by)
            ) {
                $action .= '<a class="dropdown-item openRightModal" href="' . route('provider.edit', $row->id) . '">
                        <i class="mr-2 fa fa-edit"></i>
                        ' . trans('app.edit') . '
                    </a>';
            }

            if (
                $this->deleteProviderPermission == 'all'
                || ($this->deleteProviderPermission == 'added' && user()->id == $row->created_by)
            ) {
                $action .= '<a class="dropdown-item delete-table-row delete-provider" href="javascript:;" data-provider-id="' . $row->id . '">
                        <i class="mr-2 fa fa-trash"></i>
                        ' . trans('app.delete') . '
                    </a>';
            }

            $action .= '</div>
                </div>
            </div>';

            return $action;
        });

        $datatables->setRowId(function ($row) {
            return 'row-' . $row->id;
        });

        $datatables->rawColumns(['check', 'name', 'url', 'type', 'status', 'action']);

        return $datatables;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \Modules\ServerManager\Entities\ServerProvider $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(ServerProvider $model)
    {
        $request = $this->request();

        $startDate = null;
        $endDate = null;

        if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
            $startDate = companyToDateString($request->startDate);
        }

        if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
            $endDate = companyToDateString($request->endDate);
        }

        $providers = $model->withoutGlobalScope(CompanyScope::class)
            ->where('company_id', company()->id)
            ->with(['createdBy']);

        if ($request->searchText != '' && $request->searchText !== null) {
            $providers->where(function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->searchText . '%')
                    ->orWhere('url', 'like', '%' . $request->searchText . '%')
                    ->orWhere('description', 'like', '%' . $request->searchText . '%');
            });
        }

        if ($request->status != 'all' && $request->status != '' && $request->status !== null) {
            $providers->where('status', $request->status);
        }

        if ($request->type != 'all' && $request->type != '' && $request->type !== null) {
            $providers->where('type', $request->type);
        }

       if ($startDate !== null && $endDate !== null) {
            $providers->where(
                function ($q) use ($startDate, $endDate) {
                    if(request()->date_filter_on == 'created_at') {
                        $q->whereBetween(DB::raw('DATE(`created_at`)'), [$startDate, $endDate]);
                    } elseif(request()->date_filter_on == 'updated_at') {
                        $q->whereBetween(DB::raw('DATE(`updated_at`)'), [$startDate, $endDate]);
                    } else {
                        $q->whereBetween(DB::raw('DATE(`created_at`)'), [$startDate, $endDate]);
                    }
                }
            );
        }

        if($this->viewProviderPermission == 'added') {
            $providers->where('created_by', user()->id);
        }

        $providers->orderBy('id', 'desc');

        return $providers;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('provider-table', 1)
            ->parameters([
                'initComplete' => 'function () {
                    try {
                        if (window.LaravelDataTables && window.LaravelDataTables["provider-table"]) {
                            window.LaravelDataTables["provider-table"].buttons().container()
                            .appendTo( "#table-actions");
                        }
                    } catch (error) {
                        console.error("DataTable init complete error:", error);
                    }
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    try {
                        if ($("#provider-table .select-picker").length > 0) {
                            $("#provider-table .select-picker").selectpicker();
                        }

                        $("body").tooltip({
                            selector: \'[data-toggle="tooltip"]\'
                        });
                    } catch (error) {
                        console.error("DataTable draw callback error:", error);
                    }
                }',
            ]);

        return $dataTable;
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        $data = [
            'check' => [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'data' => 'check',
                'name' => 'check',
                'orderable' => false,
                'searchable' => false,
                'visible' => !in_array('client', user_roles())
            ],
            __('servermanager::app.provider.name') => ['data' => 'name', 'name' => 'name', 'title' => __('servermanager::app.provider.name'), 'width' => '20%', 'orderable' => true, 'searchable' => true],
            __('servermanager::app.provider.url') => ['data' => 'url', 'name' => 'url', 'title' => __('servermanager::app.provider.url'), 'width' => '30%', 'orderable' => true, 'searchable' => true],
            __('servermanager::app.provider.type') => ['data' => 'type', 'name' => 'type', 'title' => __('servermanager::app.provider.type'), 'width' => '25%', 'orderable' => true, 'searchable' => true],
            __('app.status') => ['data' => 'status', 'name' => 'status', 'width' => '25%', 'exportable' => false, 'title' => __('app.status'), 'orderable' => true, 'searchable' => false],
        ];

        $action = [
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];

        return array_merge($data, $action);
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'providers_' . date('Y_m_d_H_i_s');
    }
}
