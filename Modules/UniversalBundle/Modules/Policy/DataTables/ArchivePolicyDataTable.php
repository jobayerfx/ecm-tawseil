<?php

namespace Modules\Policy\DataTables;

use App\Models\Team;
use App\Models\Designation;
use App\Models\EmployeeDetails;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use App\DataTables\BaseDataTable;
use Illuminate\Support\Facades\DB;
use Modules\Policy\Entities\Policy;

class ArchivePolicyDataTable extends BaseDataTable
{

    private $viewPermission;
    private $deletePermission;
    private $canArchivePermission;

    public function __construct()
    {
        parent::__construct();

        $this->viewPermission = user()->permission('view_policy');
        $this->deletePermission = user()->permission('delete_policy');
        $this->canArchivePermission = user()->permission('can_archive_policy');
    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('action', function ($row) {
                $action = '';

                if($this->canArchivePermission == 'all')
                {
                    $action .= '<a href="javascript:;" class="btn btn-sm btn-secondary restore-archived-policy mr-2"
                        data-toggle="tooltip" data-user-id="' . $row->id . '" data-original-title="' . __('app.unarchive') . '"><i class="fa fa-undo" aria-hidden="true"></i>
                    </a>';
                }

                if ($row->status == 'draft' && ($this->deletePermission == 'all' || ($this->deletePermission == 'added' && $row->added_by == user()->id) || $this->deletePermission == 'owned' || $this->deletePermission == 'both')) {
                    $action .= '<a href="javascript:;" class="btn btn-sm btn-secondary delete-archived-policy"
                        data-toggle="tooltip" data-user-id="' . $row->id . '" data-original-title="' . __('app.delete') . '"><i class="fa fa-times" aria-hidden="true"></i>
                    </a>';
                }

                return $action;
            })
            ->addColumn('title', function ($row) {

                if ($this->viewPermission == 'all' || ($this->viewPermission == 'added' && user()->id == $row->added_by) || ($this->viewPermission == 'owned') || ($this->viewPermission == 'both')) {
                    return '<a class="text-darkest-grey" href="' . route('policy.show', [$row->id]) . '">' . $row->title . '</a>';
                }

                return $row->title;
            })
            ->addColumn('department', function ($row) {
                $value = (!empty($row->department_id_json) && $row->department_id_json != 'null') ? collect(Policy::department(json_decode($row->department_id_json)))

                    ->map(function ($val) {
                        return '<ul>' . $val  . '</ul>';
                    })
                    ->implode('') : '--';

                return $value !== '' ? $value : '--';
            })
            ->addColumn('designation', function ($row) {
                $value = (!empty($row->designation_id_json) && $row->designation_id_json != 'null') ? collect(Policy::designation(json_decode($row->designation_id_json)))
                    ->map(function ($val) {
                        return '<ul>' . $val  . '</ul>';
                    })
                    ->implode('') : '--';

                return $value !== '' ? $value : '--';
            })
            ->addColumn('employment_type', function ($row) {
                $value = !empty($row->employment_type_json) ? collect(json_decode($row->employment_type_json))
                    ->map(function ($employmentType) {
                        return '<ul>' . __('modules.employees.' . $employmentType) . '</ul>';
                    })
                    ->implode('') : '--';
                return $value !== '' ? $value : '--';
            })
            ->addColumn('publish_date', function ($row) {
                return $row->publish_date ? $row->publish_date->format(company()->date_format) : '--';
            })
            ->addColumn('signature_required', function ($row) {
                if ($row->signature_required == 'yes') {
                    $signature = '<span class="badge badge-success">' . __('app.yes') . '</span> ';
                }
                else {
                    $signature = '<span class="badge badge-danger">' . __('app.no')  . '</span> ';
                }

                return $signature;
            })
            ->addColumn('acknowledged', function ($row) {
                if ($row->is_acknowledged > 0) {
                    return '<span class="badge badge-success">' . __('app.yes') . '</span> ';
                }
                else {
                    return '<span class="badge badge-danger">' . __('app.no') . '</span> ';
                }
            })
            ->addColumn('employee_action', function ($row) {
                $totalEmployees = 0;

                $department = $row->department_id_json ? json_decode($row->department_id_json) : [];
                $designation = $row->designation_id_json ? json_decode($row->designation_id_json) : [];
                $employmentType = $row->employment_type_json ? json_decode($row->employment_type_json) : [];

                $totalEmployees = EmployeeDetails::where(function ($q) use ($department, $designation, $employmentType) {
                    if (!empty($department)) {
                        $q->whereIn('department_id', $department);
                    }

                    if (!empty($designation)) {
                        $q->whereIn('designation_id', $designation);
                    }

                    if (!empty($employmentType)) {
                        $q->whereIn('employment_type', $employmentType);
                    }
                })->count();

                return $row->employee_acknowledged . '/' . $totalEmployees;
            })
            ->addColumn('status', function ($row) {
                if($row->status == 'published')
                {
                    $status = '<span class="badge badge-success">' . __('policy::app.published') . '</span> ';
                }
                else {
                    $status = '<span class="badge badge-danger">' . __('policy::app.draft')  . '</span> ';
                }

                return $status;
            })
            ->addIndexColumn()
            ->rawColumns(['title', 'action', 'employment_type', 'department', 'designation', 'signature_required', 'status', 'acknowledged'])
            ->setRowId('id');
    }

    /**
     * @param Policy $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Policy $model)
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

        $model = $model->selectRaw('id, title, publish_date, department_id_json, designation_id_json, employment_type_json, signature_required, added_by, status,
        (select count("id") from policy_employee_acknowledged where policy_employee_acknowledged.policy_id = policies.id) as employee_acknowledged,
        (select count("packn.id") from policy_employee_acknowledged as packn where packn.policy_id = policies.id AND packn.user_id = ' . user()->id . ') as is_acknowledged');

        if ($startDate !== null && $endDate !== null) {
            $model->whereBetween(DB::raw('DATE(policies.`publish_date`)'), [$startDate, $endDate]);
        }

        if ($this->viewPermission == 'added') {
            $model->where('added_by', user()->id);
        }

        if ($this->viewPermission == 'owned') {
            $model->where(function ($query) {
                $query->where(function ($q) {
                    $q->orWhere('department_id_json', 'like', '%"' . user()->employeeDetails->department_id . '"%')
                        ->orWhereNull('department_id_json');
                });
                $query->where(function ($q) {
                    $q->orWhere('designation_id_json', 'like', '%"' . user()->employeeDetails->designation_id . '"%')
                        ->orWhereNull('designation_id_json');
                });
                $query->where(function ($q) {
                    $q->orWhere('employment_type_json', 'like', '%"' . user()->employeeDetails->employment_type . '"%')
                        ->orWhereNull('employment_type_json');
                });
            });
        }

        if ($this->viewPermission == 'both') {
            $model->where(function ($query) {
                $query->where('added_by', user()->id)
                    ->orWhere(function ($query) {
                        $query->where(function ($q) {
                            $q->orWhere('department_id_json', 'like', '%"' . user()->employeeDetails->department_id . '"%')
                                ->orWhereNull('department_id_json');
                        });
                        $query->where(function ($q) {
                            $q->orWhere('designation_id_json', 'like', '%"' . user()->employeeDetails->designation_id . '"%')
                                ->orWhereNull('designation_id_json');
                        });
                        $query->where(function ($q) {
                            $q->orWhere('employment_type_json', 'like', '%"' . user()->employeeDetails->employment_type . '"%')
                                ->orWhereNull('employment_type_json');
                        });
                        $query->where('status', 'published');
                    });
            });
        }

        if ($request->department != null && $request->department != '' && $request->department != 'all') {
            $model->where('department_id_json', 'like', '%"' . $request->department . '"%');
        }

        if ($request->designation != null && $request->designation != '' && $request->designation != 'all') {
            $model->where('designation_id_json', 'like', '%"' . $request->designation . '"%');
        }

        if ($request->employmentType != null && $request->employmentType != '' && $request->employmentType != 'all') {
            $model->where('employment_type_json', 'like', '%"' . $request->employmentType . '"%');
        }

        if ($request->signatureRequired != null && $request->signatureRequired != '' && $request->signatureRequired != 'all') {
            $model->where('signature_required', 'like', '%' . $request->signatureRequired . '%');
        }

        if ($request->searchText != '') {
            $teams = Team::where('team_name', 'like', '%' . request('searchText') . '%')->get();
            $designations = Designation::where('name', 'like', '%' . request('searchText') . '%')->get();
            $model->where(
                function ($query) use ($teams, $designations) {
                    $query->where('policies.title', 'like', '%' . request('searchText') . '%');
                    $query->orWhere('policies.employment_type_json', 'like', '%' . request('searchText') . '%');

                    foreach ($teams as $team) {
                        $query->orWhere('policies.department_id_json', 'like', '%' . $team->id . '%');
                    }

                    foreach ($designations as $designation) {
                        $query->orWhere('policies.designation_id_json', 'like', '%' . $designation->id . '%');
                    }
                }
            );
        }

        $model->onlyTrashed();

        return $model;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('archive-policy-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["archive-policy-table"].buttons().container()
                        .appendTo( "#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("#archive-policy-table .select-picker").selectpicker();

                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    })
                }',
            ]
        );

        if (canDataTableExport()) {
            $dataTable->buttons(Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]));
        }

        return $dataTable;
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        $visible = $this->viewPermission == 'all' ? true : false;

        $data = [
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('app.id') => ['data' => 'id', 'name' => 'id', 'visible' => true, 'exportable' => false, 'title' => __('app.id')],
            __('policy::app.policyTitle') => ['data' => 'title', 'name' => 'title', 'exportable' => false, 'title' => __('policy::app.policyTitle')],
            __('policy::app.publishDate') => ['data' => 'publish_date', 'name' => 'publish_date', 'title' => __('policy::app.publishDate')],
            __('app.department') => ['data' => 'department', 'name' => 'department', 'title' => __('app.department')],
            __('app.designation') => ['data' => 'designation', 'name' => 'designation', 'title' => __('app.designation')],
            __('modules.employees.employmentType') => ['data' => 'employment_type', 'name' => 'employment_type', 'title' => __('modules.employees.employmentType')],
            __('policy::app.signatureRequired') => ['data' => 'signature_required', 'name' => 'signature_required', 'title' => __('policy::app.signatureRequired')],
            __('policy::app.acknowledged') => ['data' => 'acknowledged', 'name' => 'acknowledged', 'title' => __('policy::app.acknowledged')],
            __('policy::app.employeeAction') => ['data' => 'employee_action', 'name' => 'employee_action', 'title' => __('policy::app.employeeAction'), 'exportable' => $visible, 'visible' => $visible],
            __('app.status') => ['data' => 'status', 'name' => 'status', 'title' => __('app.status')],

            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];

        return $data;
    }

}
