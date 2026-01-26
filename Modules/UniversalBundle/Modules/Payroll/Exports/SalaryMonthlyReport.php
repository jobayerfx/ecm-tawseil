<?php

namespace Modules\Payroll\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Payroll\Entities\SalarySlip;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SalaryMonthlyReport implements FromCollection, WithHeadings, WithColumnFormatting, WithEvents, ShouldAutoSize
{
    private $startDate;
    private $endDate;
    private $departmentId;
    private $designationId;

    public function __construct($startDate, $endDate, $departmentId, $designationId)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->departmentId = $departmentId;
        $this->designationId = $designationId;
    }

    public function collection()
    {
        $department = $this->departmentId;
        $designation = $this->designationId;

        $start = Carbon::createFromFormat('m-Y', $this->startDate);
        $end = Carbon::createFromFormat('m-Y', $this->endDate);

        // Getting All records of Payroll according to filter
        $query = SalarySlip::select([
                'salary_slips.id',
                'salary_slips.salary_json',
                'salary_slips.basic_salary',
                'salary_slips.gross_salary',
                'salary_slips.status',
                'salary_slips.extra_json',
                'salary_slips.company_id',
                'salary_slips.month',
                'salary_slips.year',
                'salary_slips.net_salary',
                'salary_slips.salary_from as pay_date',
                'salary_slips.status',
                'salary_slips.user_id as employee_id',
                'salary_slips.user_id',
                'users.id as emp_employee_id',
                'employee_details.employee_id as empid',
                'teams.team_name as department_name',
                'designations.name as designation_name',
                'salary_slips.expense_claims',
                'salary_groups.group_name as salary_group_name',
                'salary_groups.id as salary_group_id',
            ])
            ->with(['user', 'salary_group', 'user.employeeDetails'])
            ->join('users', 'users.id', '=', 'salary_slips.user_id')
            ->join('employee_details', 'users.id', '=', 'employee_details.user_id')
            ->leftJoin('salary_groups', 'salary_groups.id', '=', 'salary_slips.salary_group_id')
            ->leftJoin('designations', 'designations.id', '=', 'employee_details.designation_id')
            ->leftJoin('teams', 'teams.id', '=', 'employee_details.department_id');

        if ($department !== null && $department !== 'all') {
            $query->where('employee_details.department_id', $department);
        }

        if ($designation !== null && $designation !== 'all') {
            $query->where('employee_details.designation_id', $designation);
        }

        $query->whereRaw('(`salary_slips`.`year` * 100 + `salary_slips`.`month`) >= ' . ($start->year * 100 + $start->month));
        $query->whereRaw('(`salary_slips`.`year` * 100 + `salary_slips`.`month`) <= ' . ($end->year * 100 + $end->month));
        $query->where('salary_slips.status', 'paid');

        $query->orderBy('users.name', 'asc');
        $query->orderByRaw('(`salary_slips`.`year` * 100 + `salary_slips`.`month`) ASC');

        $results = $query->get();

        // Fixed headings before dynamic Headings
        $columns = ['Employee ID', 'Employee Name', 'Department', 'Designation', 'Location', 'Salary Group', 'Month', 'Basic Salary'];

        // Getting Dynamic heading according to payrolls
        $heads = $this->getDynamicHeadings($results);

        foreach($heads[0] as $hd)
        {
            $columns[] = $hd;
        }

        // Fixed headings after dynamic Headings
        $columns[] = 'Expense Claim';
        $columns[] = 'Extra Earnings';
        $columns[] = 'Extra Deductions';
        $columns[] = 'Total Earnings';
        $columns[] = 'Total Deductions';
        $columns[] = 'Special Allowance';
        $columns[] = 'Net Salary';

        $headsArray = $heads[0];

        $rows = [
            [''],
            $columns,
        ];

        $finalTotals = array_fill(0, count($columns), 0);
        $finalTotals[0] = '';
        $finalTotals[1] = '';
        $finalTotals[6] = 'Totals:';

        // Collect all employee rows and sum for final totals
        foreach ($results as $result) {
            $salaryJson = json_decode($result->salary_json, true);
            $basicSalary = $result->basic_salary;
            $totalEarnings = $basicSalary + $result->expense_claims;
            $totalDeductions = 0;

            $row = [
                $result->empid,
                $result->user->name,
                $result->department_name ?? '-',
                $result->designation_name ?? '-',
                $result->location_name ?? '-',
                $result->salary_group_name,
                Carbon::now()->startOfMonth()->month($result->month)->year($result->year)->format('F Y'),
                $basicSalary * 1
            ];

            foreach ($headsArray as $head) {
                if (isset($salaryJson['earnings'][$head])) {
                    $totalEarnings += $salaryJson['earnings'][$head];
                    $row[] = round($salaryJson['earnings'][$head], 2);
                }
                elseif (isset($salaryJson['deductions'][$head])) {
                    $totalDeductions += $salaryJson['deductions'][$head];
                    $row[] = round($salaryJson['deductions'][$head], 2);
                }
                else {
                    $row[] = 0;
                }
            }

            // Process extra earnings and deductions
            $extraEarning = isset($result->extra_json['earnings']) ? array_sum($result->extra_json['earnings']) : 0;
            $extraDeduction = isset($result->extra_json['deductions']) ? array_sum($result->extra_json['deductions']) : 0;

            $fixedAllowance = ($result->fixed_allowance == 0 || $result->fixed_allowance < 0) ? $result->gross_salary - (round($totalEarnings + $extraEarning, 2)) : $result->fixed_allowance;
            $fixedAllowance = ($fixedAllowance == 0 || $fixedAllowance < 0) ? 0 : $fixedAllowance;

            $row[] = round($result->expense_claims * 1, 2);
            $row[] = round($extraEarning, 2);
            $row[] = round($extraDeduction, 2);
            $row[] = round($totalEarnings + $extraEarning, 2);
            $row[] = round($totalDeductions + $extraDeduction, 2);
            $row[] = round($fixedAllowance * 1, 2);
            $row[] = round($result->net_salary, 2);

            // Add to rows
            $rows[] = $row;

            // Add to final totals (skip text columns)
            foreach ($row as $k => $v) {
                if (is_numeric($v)) {
                    $finalTotals[$k] = (isset($finalTotals[$k]) && is_numeric($finalTotals[$k])) ? round($finalTotals[$k] + (float)$v, 2) : $v;
                }
            }
        }

        $finalTotals[0] = '';
        $rows[] = [''];
        $rows[] = $finalTotals;

        return collect($rows);
    }

    public function getDynamicHeadings($salarySlips)
    {
        $dynamicHeading = [];
        $earnings = [];
        $deductions = [];

        foreach($salarySlips as $salary){
            $headings = json_decode($salary->salary_json);

            if(isset($headings->earnings)){
                $earnings = array_keys((array)$headings->earnings);
                $dynamicHeading = array_merge($dynamicHeading, $earnings);
            }

            if(isset($headings->deductions)){
                $deductions = array_keys((array)$headings->deductions);
                $dynamicHeading = array_merge($dynamicHeading, $deductions);
            }

            $dynamicHeading = array_unique($dynamicHeading);
        }

        return [$dynamicHeading, $earnings, $deductions];
    }

    public function headings(): array
    {
        $startMonth = Carbon::createFromFormat('m-Y', $this->startDate);
        $endMonth = Carbon::createFromFormat('m-Y', $this->endDate);

        return [
            [company()->company_name . ' - '.__('payroll::modules.payroll.salaryReport')],
            [],
            ['Start:', $startMonth->format('F Y'), '', 'End:', $endMonth->format('F Y'), '', 'Generated On:', Carbon::now()->timezone(company()->timezone)->format('jS F, Y, g:i a')],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_TEXT
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();
                $lastColumn = $sheet->getHighestColumn();
                $sheet->mergeCells('A1:' . $lastColumn . '1');
                $sheet->getStyle('A1:' . $lastColumn . '1')
                    ->getFont()
                    ->setSize(16)
                    ->setBold(true);

                // Set row height for merged cell to ensure data is visible and not cut off
                $sheet->getRowDimension(1)->setRowHeight(35);

                $sheet->getStyle('A3')->getFont()->setBold(true);
                $sheet->getStyle('D3')->getFont()->setBold(true);
                $sheet->getStyle('G3')->getFont()->setBold(true);
                $sheet->getStyle('A5:' . $sheet->getHighestColumn() . '5')->getFont()->setBold(true);

                // Find the row number of the totals row
                $totalRow = $sheet->getHighestRow();

                // The totals row is the last row (after a blank row), so we want to bold the entire totals row
                // The totals row is at $totalRow
                $sheet->getStyle('A' . $totalRow . ':' . $lastColumn . $totalRow)
                    ->getFont()
                    ->setBold(true);

                // Optionally, you can also set a background color for the totals row if desired
                // $sheet->getStyle('A' . $totalRow . ':' . $lastColumn . $totalRow)
                //     ->getFill()
                //     ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                //     ->getStartColor()
                //     ->setARGB('FFF9E79F');

                $sheet->getStyle('A5:' . $sheet->getHighestColumn() . '5')
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('00d8ff');
            }
        ];
    }

    public function properties(): array
    {
        return [
            'creator' => user()->name,
            'lastModifiedBy' => user()->name,
            'title' => company()->company_name,
            'description' => 'Payroll',
            'company' => user()->name,
        ];
    }

}
