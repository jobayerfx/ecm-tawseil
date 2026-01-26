<?php

namespace Modules\ServerManager\DataTables;

use App\DataTables\BaseDataTable;
use Modules\ServerManager\Entities\ServerDomain;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Scopes\CompanyScope;

class DomainDataTable extends BaseDataTable
{
    private $addDomainPermission;
    private $editDomainPermission;
    private $deleteDomainPermission;
    private $viewDomainPermission;

    public function __construct()
    {
        parent::__construct();
        $this->addDomainPermission = user()->permission('add_domain');
        $this->editDomainPermission = user()->permission('edit_domain');
        $this->deleteDomainPermission = user()->permission('delete_domain');
        $this->viewDomainPermission = user()->permission('view_domain');
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

        $datatables->editColumn('domain_name', function ($row) {
            return '<a href="' . route('domain.show', $row->id) . '" class="text-darkest-grey">' . $row->domain_name . '</a>';
        });

        $datatables->editColumn('domain_provider', function ($row) {
            return $row->provider ? $row->provider->name : '-';
        });

        $datatables->editColumn('domain_type', function ($row) {
            return '<span class="badge badge-info">' . strtoupper($row->domain_type) . '</span>';
        });

        $datatables->editColumn('status', function ($row) {
            return '<span class="badge ' . $row->getStatusBadgeClass() . '">' . ucfirst($row->status) . '</span>';
        });

        $datatables->editColumn('registration_date', function ($row) {
            return $row->registration_date->format(company()->date_format) ?? '--';
        });

        $datatables->editColumn('expiry_date', function ($row) {

            return $row->expiry_date->format(company()->date_format) ?? '--';
        });

        $datatables->editColumn('client_id', function ($row) {
            return $row->clientDetail ? view('components.client', ['user' => $row->clientDetail]) : '--';
        });

        $datatables->editColumn('assigned_to', function ($row) {
            return $row->assignedTo ? $row->assignedTo->name : '-';
        });

        $datatables->editColumn('hosting', function ($row) {
            return $row->hosting ? $row->hosting->name : '-';
        });

        $datatables->addColumn('action', function ($row) {
            $action = '<div class="task_view">
                <div class="dropdown">
                    <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                        id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-options-vertical icons"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

            $action .= '<a href="' . route('domain.show', $row->id) . '" class="dropdown-item"><i class="mr-2 fa fa-eye"></i>' . __('app.view') . '</a>';

            if (
                $this->editDomainPermission == 'all'
                || ($this->editDomainPermission == 'added' && user()->id == $row->created_by)
            ) {
                $action .= '<a class="dropdown-item openRightModal" href="' . route('domain.edit', $row->id) . '">
                        <i class="mr-2 fa fa-edit"></i>
                        ' . trans('app.edit') . '
                    </a>';
            }

            if (
                $this->deleteDomainPermission == 'all'
                || ($this->deleteDomainPermission == 'added' && user()->id == $row->created_by)
            ) {
                $action .= '<a class="dropdown-item delete-domain" href="javascript:;" data-domain-id="' . $row->id . '">
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

        $datatables->rawColumns(['check', 'domain_name', 'domain_type', 'status', 'expiry_date', 'action']);

        return $datatables;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \Modules\ServerManager\Entities\ServerDomain $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(ServerDomain $model)
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

        $domains = $model->withoutGlobalScope(CompanyScope::class)
            ->where('company_id', company()->id)
            ->with(['assignedTo', 'createdBy', 'hosting', 'provider', 'clientDetail']);

        if ($request->searchText != '' && $request->searchText !== null) {
            $domains->where(function ($query) use ($request) {
                $query->where('domain_name', 'like', '%' . $request->searchText . '%')
                    ->orWhereHas('provider', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->searchText . '%');
                    })
                    ->orWhere('domain_type', 'like', '%' . $request->searchText . '%');
            });
        }

        if ($request->status != 'all' && $request->status != '' && $request->status !== null) {
            $domains->where('status', $request->status);
        }

        if ($request->provider != 'all' && $request->provider != '' && $request->provider !== null) {
            $domains->where('domain_provider', $request->provider);
        }

        if ($request->domain_type != 'all' && $request->domain_type != '' && $request->domain_type !== null) {
            $domains->where('domain_type', $request->domain_type);
        }

        if ($request->hosting_id != 'all' && $request->hosting_id != '' && $request->hosting_id !== null) {
            $domains->where('hosting_id', $request->hosting_id);
        }

        if (!is_null($request->client_id) && $request->client_id != 'all') {
            $domains->where('client_id', $request->client_id);
        }

        if ($startDate !== null && $endDate !== null) {
            $domains->where(
                function ($q) use ($startDate, $endDate) {
                    if(request()->date_filter_on == 'expiry_date') {
                        $q->whereBetween(DB::raw('DATE(`expiry_date`)'), [$startDate, $endDate]);
                    } else {
                        $q->whereBetween(DB::raw('DATE(`created_at`)'), [$startDate, $endDate]);
                    }
                }
            );
        }

        if($this->viewDomainPermission == 'added') {
            $domains->where('created_by', user()->id);
        }

        if (!request()->has('order')) {
            $domains->orderBy('id', 'desc');
        }

        return $domains;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('domain-table', 8)
            ->parameters(
                [
                    'order' => [[1, 'desc']],
                    'initComplete' => 'function () {
                    try {
                        if (window.LaravelDataTables && window.LaravelDataTables["domain-table"]) {
                            window.LaravelDataTables["domain-table"].buttons().container()
                            .appendTo( "#table-actions");
                        }
                    } catch (error) {
                        console.error("DataTable init complete error:", error);
                    }
                }',
                    'fnDrawCallback' => 'function( oSettings ) {
                    try {
                        if ($("#domain-table .select-picker").length > 0) {
                            $("#domain-table .select-picker").selectpicker();
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
            __('servermanager::app.domain.domainName') => ['data' => 'domain_name', 'name' => 'domain_name', 'title' => __('servermanager::app.domain.domainName'), 'width' => '15%', 'orderable' => true, 'searchable' => true],
            __('servermanager::app.domain.provider') => ['data' => 'domain_provider', 'name' => 'domain_provider', 'title' => __('servermanager::app.domain.provider'), 'width' => '12%', 'orderable' => true, 'searchable' => true],
            __('servermanager::app.domain.domainType') => ['data' => 'domain_type', 'name' => 'domain_type', 'title' => __('servermanager::app.domain.domainType'), 'width' => '13%', 'orderable' => true, 'searchable' => true],
            __('app.client') => ['data' => 'client_id', 'name' => 'client_id', 'width' => '10%', 'title' => __('app.client'), 'visible' => (!in_array('client', user_roles()) && in_array('clients', user_modules()))],
            __('app.status') => ['data' => 'status', 'name' => 'status', 'width' => '13%', 'exportable' => false, 'title' => __('app.status'), 'orderable' => true, 'searchable' => false],
            __('servermanager::app.domain.purchaseDate') => ['data' => 'registration_date', 'name' => 'registration_date', 'title' => __('servermanager::app.domain.purchaseDate'), 'width' => '15%', 'orderable' => true, 'searchable' => true],
            __('servermanager::app.domain.expiryDate') => ['data' => 'expiry_date', 'name' => 'expiry_date', 'title' => __('servermanager::app.domain.expiryDate'), 'width' => '15%', 'orderable' => true, 'searchable' => true],
            // __('servermanager::app.domain.assignedTo') => ['data' => 'assigned_to', 'name' => 'assigned_to', 'title' => __('servermanager::app.domain.assignedTo'), 'width' => '12%', 'orderable' => true, 'searchable' => true],
            __('servermanager::app.domain.hosting') => ['data' => 'hosting', 'name' => 'hosting', 'title' => __('servermanager::app.domain.hosting'), 'width' => '12%', 'orderable' => false, 'searchable' => true],
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
}
