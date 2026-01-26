<?php

namespace Modules\Policy\DataTables;

use App\Models\Team;
use App\Models\Designation;
use App\DataTables\BaseDataTable;
use App\Models\EmployeeDetails;
use App\Models\User;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use Modules\Policy\Entities\Policy;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class PolicyDataTable extends BaseDataTable
{

    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */

    private $addPermission;
    private $viewPermission;
    private $deletePermission;
    private $editPermission;
    private $canArchivePermission;

    public function __construct()
    {
        parent::__construct();

        $this->addPermission = user()->permission('add_policy');
        $this->viewPermission = user()->permission('view_policy');
        $this->deletePermission = user()->permission('delete_policy');
        $this->editPermission = user()->permission('edit_policy');
        $this->canArchivePermission = user()->permission('can_archive_policy');
    }

    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('action', function ($row) {
                $action = '<div class="task_view">';

                $action .= '<div class="dropdown">
                    <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                        id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-options-vertical icons"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                if ($this->viewPermission == 'all' || ($this->viewPermission == 'added' && user()->id == $row->added_by) || ($this->viewPermission == 'owned') || ($this->viewPermission == 'both')) {
                    $action .= '<a href="' . route('policy.show', [$row->id]) . '" class="dropdown-item"><i class="mr-2 fa fa-eye"></i>' . __('app.view') . '</a>';
                }

                if (empty($row->filename)) {
                    $action .= '<a href="' . route('policy.download', [$row->id, user()->id]) . '" class="dropdown-item"><i class="mr-2 fa fa-download"></i>' . __('app.download') . '</a>';
                } else {
                    $action .= '<a href="' . route('policy-file.download', md5($row->id)) . '?type=only-file' . '" class="dropdown-item"><i class="fa fa-download mr-1"></i>' . __('app.download') . '</a>';
                }


                $department = !is_null($row->department_id_json) ? in_array(user()->employeeDetails->department_id, json_decode($row->department_id_json)) : true;
                $designation = !is_null($row->designation_id_json) ? in_array(user()->employeeDetails->designation_id, json_decode($row->designation_id_json)) : true;
                $employmentType = !is_null($row->employment_type_json) ? in_array(user()->employeeDetails->employment_type, json_decode($row->employment_type_json)) : true;

                if ($row->employeeAcknowledge->isEmpty() && (($this->editPermission == 'all'
                    || ($this->editPermission == 'added' && $row->added_by == user()->id)
                    || ($this->editPermission == 'owned' && $department && $designation && $employmentType))
                    || ($this->editPermission == 'both' && (($department && $designation && $employmentType) || $row->added_by == user()->id)))) {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('policy.edit', $row->id) . '" >
                            <i class="mr-2 fa fa-edit"></i>
                            ' . trans('app.edit') . '
                        </a>';
                }

                if (!$row->trashed() && $row->status == 'draft' && (user()->hasRole('admin') || $this->addPermission == 'all')) {
                    $action .= '<a class="dropdown-item publish-policy" href="javascript:;" data-toggle="tooltip"  data-policy-id="' . $row->id . '">
                            <i class="mr-2 fa fa-check-circle"></i>
                            ' . trans('policy::app.publish') . '
                        </a>';
                }

                if ($row->status == 'published' && ($this->canArchivePermission == 'all' || in_array('admin', user_roles()))) {
                    $action .= '<a class="dropdown-item archive-policy" href="javascript:;" data-policy-id="' . $row->id . '">
                            <i class="fa fa-archive mr-2"></i>
                            ' . trans('app.archive') . '
                        </a>';
                }

                if ($row->status == 'draft' && ($this->deletePermission == 'all' || ($this->deletePermission == 'added' && $row->added_by == user()->id) || $this->deletePermission == 'owned' || $this->deletePermission == 'both')) {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-toggle="tooltip"  data-policy-id="' . $row->id . '">
                        <i class="mr-2 fa fa-trash"></i>
                        ' . trans('app.delete') . '
                    </a>';
                }

                $action .= '</div>
                </div>
            </div>';

                return $action;
            })
            ->addColumn('title', function ($row) {

                if ($this->viewPermission == 'all' || ($this->viewPermission == 'added' && user()->id == $row->added_by) || ($this->viewPermission == 'owned') || ($this->viewPermission == 'both')) {
                    return '<a class="text-darkest-grey" href="' . route('policy.show', [$row->id]) . '">' . $row->title . '</a>';
                }

                return $row->title;
            })
            ->addColumn('publish_date', function ($row) {
                return $row->publish_date ? $row->publish_date->format(company()->date_format) : '--';
            })
            ->addColumn('department', function ($row) {
                // Get departments as an array
                $departments = (!empty($row->department_id_json) && $row->department_id_json != 'null')
                    ? Policy::department(json_decode($row->department_id_json))
                    : [];

                if (empty($departments)) {
                    return '--';
                }

                // Render each department as a badge
                $badges = collect($departments)->map(function ($val) {
                    return '<span class="badge badge-secondary mr-1">' . e($val) . '</span>';
                })->implode(' ');

                return $badges;
            })
            ->addColumn('gender', function ($row) {
                if (!$row->gender) {
                    return '--';
                }

                $icon = match($row->gender) {
                    'male' => '<i class="bi bi-gender-male mr-1"></i>',
                    'female' => '<i class="bi bi-gender-female mr-1"></i>',
                    'others' => '<i class="bi bi-gender-trans mr-1"></i>',
                    default => ''
                };

                return '<span class="badge badge-info">' . $icon . __('app.'.$row->gender) . '</span>';
            })
            ->addColumn('designation', function ($row) {
                // Get designations as an array
                $designations = (!empty($row->designation_id_json) && $row->designation_id_json != 'null')
                    ? Policy::designation(json_decode($row->designation_id_json))
                    : [];

                if (empty($designations)) {
                    return '--';
                }

                // Render each designation as a badge
                $badges = collect($designations)->map(function ($val) {
                    return '<span class="badge badge-secondary mr-1">' . e($val) . '</span>';
                })->implode(' ');

                return $badges;
            })
            ->addColumn('employment_type', function ($row) {
                $value = !empty($row->employment_type_json) ? collect(json_decode($row->employment_type_json))
                    ->map(function ($employmentType) {
                        return '<ul>' . __('modules.employees.' . $employmentType) . '</ul>';
                    })
                    ->implode('') : '--';
                return $value !== '' ? $value : '--';
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

                $totalEmployees = $this->getEmployeeTotal($row);

                return $row->employee_acknowledged . '/' . $totalEmployees;
            })
            ->addColumn('status', function ($row) {
                if ($row->status == 'published') {
                    $status = '<span class="badge badge-success">' . __('policy::app.published') . '</span> ';
                }
                else {
                    $status = '<span class="badge badge-danger">' . __('policy::app.draft')  . '</span> ';
                }

                return $status;
            })
            ->addIndexColumn()
            ->rawColumns(['title', 'action', 'employment_type', 'gender', 'department', 'designation', 'signature_required', 'status', 'acknowledged'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
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

        $model = $model->selectRaw('
            id, title, publish_date, department_id_json, designation_id_json, employment_type_json, signature_required, added_by, status, filename, gender,
            (
                select count(policy_employee_acknowledged.id)
                from policy_employee_acknowledged
                join users on users.id = policy_employee_acknowledged.user_id
                where policy_employee_acknowledged.policy_id = policies.id
                and users.status = "active"
            ) as employee_acknowledged,
            (
                select count(packn.id)
                from policy_employee_acknowledged as packn
                join users on users.id = packn.user_id
                where packn.policy_id = policies.id
                and packn.user_id = ' . user()->id . '
                and packn.company_id = ' . company()->id . '
                and users.status = "active"
            ) as is_acknowledged'
        );

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

                $query->where(function ($q) {
                    $q->orWhere('gender', user()->gender)
                        ->orWhereNull('gender');
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

                        $query->where(function ($q) {
                            $q->orWhere('gender', user()->gender)
                                ->orWhereNull('gender');
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

        if ($request->gender != null && $request->gender != '' && $request->gender != 'all') {
            $model->where('gender', $request->gender);
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

        return $model;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->setBuilder('policy-table', 2)
            ->parameters(
                [
                    'initComplete' => 'function () {
                    window.LaravelDataTables["policy-table"].buttons().container()
                        .appendTo( "#table-actions")
                }',
                    'fnDrawCallback' => 'function( oSettings ) {
                    $(".select-picker").selectpicker();

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
     * Get the dataTable columns definition.
     */
    public function getColumns()
    {
        $visible = $this->viewPermission == 'all' ? true : false;

        $data = [
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => true, 'title' => '#'],
            __('policy::app.policyTitle') => ['data' => 'title', 'name' => 'title', 'title' => __('policy::app.policyTitle')],
            __('policy::app.publishDate') => ['data' => 'publish_date', 'name' => 'publish_date', 'title' => __('policy::app.publishDate')],
            __('modules.employees.gender') => ['data' => 'gender', 'name' => 'gender', 'title' => __('modules.employees.gender')],
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

    // Get total employee count
    private function getEmployeeTotal($policy)
    {
        $department = $policy->department_id_json ? json_decode($policy->department_id_json) : [];
        $designation = $policy->designation_id_json ? json_decode($policy->designation_id_json) : [];
        $employmentType = $policy->employment_type_json ? json_decode($policy->employment_type_json) : [];

        $totalEmployees = EmployeeDetails::with('user')->whereHas('user', function($q) use($policy) {
            $q->where('status', 'active');

            if (!is_null($policy->gender)) {
                $q->where('gender', $policy->gender);
            }

        });

        $totalEmployees = $totalEmployees->where(function ($q) use ($department, $designation, $employmentType) {
            if (!empty($department)) {
                $q->whereIn('department_id', $department);
            }

            if (!empty($designation)) {
                $q->whereIn('designation_id', $designation);
            }

            if (!empty($employmentType)) {
                $q->whereIn('employment_type', $employmentType);
            }
        });

        return $totalEmployees->count();

    }

}
