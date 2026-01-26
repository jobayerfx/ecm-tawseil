<?php

namespace Modules\ServerManager\DataTables;

use App\DataTables\BaseDataTable;
use Modules\ServerManager\Entities\ServerHosting;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Scopes\CompanyScope;

class HostingDataTable extends BaseDataTable
{
    private $addHostingPermission;
    private $editHostingPermission;
    private $deleteHostingPermission;
    private $viewHostingPermission;

    public function __construct()
    {
        parent::__construct();
        $this->addHostingPermission = user()->permission('add_hosting');
        $this->editHostingPermission = user()->permission('edit_hosting');
        $this->deleteHostingPermission = user()->permission('delete_hosting');
        $this->viewHostingPermission = user()->permission('view_hosting');
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

        // Use editColumn instead of addColumn for sortable columns
        $datatables->editColumn('name', function ($row) {
            return '<a href="' . route('hosting.show', $row->id) . '" class="text-darkest-grey">' . $row->name . '</a>';
        });

        // $datatables->editColumn('domain_name', function ($row) {
        //     return $row->domain_name;
        // });

        $datatables->editColumn('hosting_provider', function ($row) {
            return $row->provider ? $row->provider->name : '-';
        });

        $datatables->editColumn('server_type', function ($row) {
            return $row->serverType ? $row->serverType->name : '-';
        });

        $datatables->editColumn('status', function ($row) {
            return '<span class="badge ' . $row->getStatusBadgeClass() . '">' . ucfirst($row->status) . '</span>';
        });

        $datatables->editColumn('client_id', function ($row) {

            return $row->clientDetail ? view('components.client', ['user' => $row->clientDetail]) : '--';
        });

        $datatables->editColumn('purchase_date', function ($row) {
            return $row->purchase_date ? $row->purchase_date->format(company()->date_format) : '-';
        });

        $datatables->editColumn('renewal_date', function ($row) {
            return $row->renewal_date ? $row->renewal_date->format(company()->date_format) : '-';
        });

        $datatables->addColumn('action', function ($row) {
            $action = '<div class="task_view">
                <div class="dropdown">
                    <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                        id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-options-vertical icons"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

            $action .= '<a href="' . route('hosting.show', $row->id) . '" class="dropdown-item"><i class="mr-2 fa fa-eye"></i>' . __('app.view') . '</a>';

            if (
                $this->editHostingPermission == 'all'
                || ($this->editHostingPermission == 'added' && user()->id == $row->created_by)
            ) {
                $action .= '<a class="dropdown-item openRightModal" href="' . route('hosting.edit', $row->id) . '">
                        <i class="mr-2 fa fa-edit"></i>
                        ' . trans('app.edit') . '
                    </a>';
            }

            if (
                $this->deleteHostingPermission == 'all'
                || ($this->deleteHostingPermission == 'added' && user()->id == $row->created_by)
            ) {
                $action .= '<a class="dropdown-item delete-hosting" href="javascript:;" data-hosting-id="' . $row->id . '">
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

        $datatables->rawColumns(['check', 'name', 'status', 'action']);

        return $datatables;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \Modules\ServerManager\Entities\ServerHosting $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(ServerHosting $model)
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

        $hostings = $model->withoutGlobalScope(CompanyScope::class)
            ->where('company_id', company()->id)
            ->with(['assignedTo', 'createdBy', 'provider', 'clientDetail']);

        if ($request->searchText != '' && $request->searchText !== null) {
            $hostings->where(function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->searchText . '%')
                    ->orWhere('domain_name', 'like', '%' . $request->searchText . '%')
                    ->orWhereHas('provider', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->searchText . '%');
                    });
            });
        }

        if ($request->status != 'all' && $request->status != '' && $request->status !== null) {
            $hostings->where('status', $request->status);
        }

        if ($request->provider != 'all' && $request->provider != '' && $request->provider !== null) {
            $hostings->where('hosting_provider', $request->provider);
        }

        if (!is_null($request->client_id) && $request->client_id != 'all') {
            $hostings->where('client', $request->client_id);
        }

        if ($request->server_type != 'all' && $request->server_type != '' && $request->server_type !== null) {
            $hostings->where('server_type', $request->server_type);
        }

        // Handle date filtering with correct parameter names
        if ($startDate !== null && $endDate !== null) {
            $hostings->where(
                function ($q) use ($startDate, $endDate) {
                    if(request()->date_filter_on == 'renewal_date') {
                        $q->whereBetween(DB::raw('DATE(`renewal_date`)'), [$startDate, $endDate]);
                    } elseif(request()->date_filter_on == 'purchase_date') {
                        $q->whereBetween(DB::raw('DATE(`purchase_date`)'), [$startDate, $endDate]);
                    } else {
                        $q->whereBetween(DB::raw('DATE(`created_at`)'), [$startDate, $endDate]);
                    }
                }
            );
        }

        if($this->viewHostingPermission == 'added') {
            $hostings->where('created_by', user()->id);
        }

        // Ensure default ordering if not set by DataTables
        if (!request()->has('order')) {
            $hostings->orderBy('id', 'desc');
        }

        return $hostings;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('hosting-table', 6)
            ->parameters(
                [
                    // 'order' => [[1, 'desc']], // Default order by name column (index 1)
                    'initComplete' => 'function () {
                    try {
                        if (window.LaravelDataTables && window.LaravelDataTables["hosting-table"]) {
                            window.LaravelDataTables["hosting-table"].buttons().container()
                            .appendTo( "#table-actions");
                        }
                    } catch (error) {
                        console.error("DataTable init complete error:", error);
                    }
                }',
                    'fnDrawCallback' => 'function( oSettings ) {
                    try {
                        if ($("#hosting-table .select-picker").length > 0) {
                            $("#hosting-table .select-picker").selectpicker();
                        }

                        $("body").tooltip({
                            selector: \'[data-toggle="tooltip"]\'
                        });
                    } catch (error) {
                        console.error("DataTable draw callback error:", error);
                    }
                }',
                ]
            );

        // if (canDataTableExport()) {
        //     $dataTable->buttons(Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]));
        // }

        return $dataTable;
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            'check' => [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'data' => 'check',
                'name' => 'check',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false,
                'visible' => !in_array('client', user_roles()),
                'width' => '5%'
            ],
            '#' => ['data' => 'id', 'name' => 'id', 'title' => '#', 'width' => '5%', 'orderable' => true, 'searchable' => false, 'visible' => false],
            __('servermanager::app.hosting.name') => ['data' => 'name', 'name' => 'name', 'title' => __('servermanager::app.hosting.name'), 'width' => '15%', 'orderable' => true, 'searchable' => true],
            // __('servermanager::app.hosting.domainName') => ['data' => 'domain_name', 'name' => 'domain_name', 'title' => __('servermanager::app.hosting.domainName'), 'width' => '20%', 'orderable' => true, 'searchable' => true],
            __('servermanager::app.hosting.provider') => ['data' => 'hosting_provider', 'name' => 'hosting_provider', 'title' => __('servermanager::app.hosting.provider'), 'width' => '15%', 'orderable' => true, 'searchable' => true],
            __('servermanager::app.hosting.serverType') => ['data' => 'server_type', 'name' => 'server_type', 'title' => __('servermanager::app.hosting.serverType'), 'width' => '10%', 'orderable' => true, 'searchable' => true],
            __('app.client') => ['data' => 'client_id', 'name' => 'client_id', 'width' => '10%', 'title' => __('app.client'), 'visible' => (!in_array('client', user_roles()) && in_array('clients', user_modules()))],
            __('app.status') => ['data' => 'status', 'name' => 'status', 'width' => '10%', 'exportable' => false, 'title' => __('app.status'), 'orderable' => true, 'searchable' => false],
            __('servermanager::app.hosting.purchaseDate') => ['data' => 'purchase_date', 'name' => 'purchase_date', 'title' => __('servermanager::app.hosting.purchaseDate'), 'width' => '20%'],
            __('servermanager::app.hosting.expiryDate') => ['data' => 'renewal_date', 'name' => 'renewal_date', 'title' => __('servermanager::app.hosting.expiryDate'), 'width' => '20%'],
           Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->width(200)
                ->addClass('text-right pr-20')
        ];
    }
}
