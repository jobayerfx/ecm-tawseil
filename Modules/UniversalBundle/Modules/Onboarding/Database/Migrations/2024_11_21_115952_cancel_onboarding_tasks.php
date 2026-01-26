<?php

use App\Models\Company;
use App\Models\DashboardWidget;
use App\Models\EmployeeDetails;
use App\Models\ModuleSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\PermissionType;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use App\Models\UserPermission;
use Modules\Onboarding\Entities\OnboardingCompletedTask;
use Modules\Onboarding\Entities\OnboardingTask;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    public function up(): void
    {

        $companies = Company::all();

        foreach ($companies as $company) {
            $employeeDetails = EmployeeDetails::where('company_id', $company->id)->get();

            foreach($employeeDetails as $employee)
            {
                $onboardTasks = OnboardingCompletedTask::where('type', 'onboard')->where('employee_id', $employee->user_id)->count();

                $offBoardTasks = OnboardingCompletedTask::where('type', 'offboard')->where('employee_id', $employee->user_id)->count();

                if($onboardTasks == 0 && $offBoardTasks == 0)
                {
                    $employee->onboard_completed = 0;
                    $employee->offboard_completed = 0;
                    $employee->onboarding_status = 'old';
                    $employee->save();
                }

            }
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

};
