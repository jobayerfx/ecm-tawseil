<?php

namespace Modules\Policy\DataTables;

use App\Models\User;
use App\Models\Acknowledged;
use App\DataTables\BaseDataTable;
use App\Models\EmployeeDetails;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use Modules\Policy\Entities\Policy;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Modules\Policy\Entities\PolicyEmployeeAcknowledged;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class NonAcknowledgedDataTable extends BaseDataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */

    private $policyId;

    public function __construct($id)
    {
        parent::__construct();
        $this->policyId = $id;
    }

    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('action', function($row){
                return '<a class="text-darkest-grey" href="'.route('policy.show', [$row->id]).'">'.__('app.download').'</a>';
            })
            ->addColumn('employee_name', function($row){
                return view('components.employee', ['user' => $row->user])->render();
            })
            ->setRowId('id')
            ->rawColumns(array_merge(['employee_name']))
            ->addIndexColumn();
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(EmployeeDetails $model)
    {
        $policy = Policy::where('id', $this->policyId)->withTrashed()->first();
        $userIds = PolicyEmployeeAcknowledged::where('policy_id', $this->policyId)->pluck('user_id')->toArray();

        $department = $policy->department_id_json ? json_decode($policy->department_id_json) : [];
        $designation = $policy->designation_id_json ? json_decode($policy->designation_id_json) : [];
        $employmentType = $policy->employment_type_json ? json_decode($policy->employment_type_json) : [];

        $model = $model->with('user')->whereHas('user', function($q) use($userIds, $policy){
            $q->whereNotIn('id', $userIds)->where('status', 'active');

            if (!is_null($policy->gender)) {
                $q->where('gender', $policy->gender);
            }

        });

        $model = $model->where(function ($q) use ($department, $designation, $employmentType) {
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

        return $model;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->setBuilder('nonAcknowledged-table', 1)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["nonAcknowledged-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $(".select-picker").selectpicker();
                }',
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns()
    {
        $data = [
            __('app.id') => ['data' => 'id', 'name' => 'id', 'visible' => false, 'exportable' => false, 'title' => __('app.id')],
            __('modules.employees.employeeName') => ['data' => 'employee_name', 'name' => 'user_id', 'exportable' => false, 'title' => __('modules.employees.employeeName')],
            ];

        return $data;
    }

}
