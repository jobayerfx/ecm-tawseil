<?php

use App\Models\Company;
use App\Models\EmployeeDetails;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add missing fields to onboarding_completed_task table if they don't exist
        if (!Schema::hasColumn('onboarding_completed_task', 'type')) {
            Schema::table('onboarding_completed_task', function (Blueprint $table) {
                $table->enum('type', ['onboard', 'offboard'])->nullable()->after('onboarding_task_id');
            });
        }

        if (!Schema::hasColumn('onboarding_completed_task', 'employee_id')) {
            Schema::table('onboarding_completed_task', function (Blueprint $table) {
                $table->integer('employee_id')->unsigned()->after('type');
                $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            });
        }

        if (!Schema::hasColumn('onboarding_completed_task', 'status')) {
            Schema::table('onboarding_completed_task', function (Blueprint $table) {
                $table->enum('status', ['pending', 'completed'])->default('pending')->after('user_id');
            });
        }

        // Add missing fields to employee_details table if they don't exist
        if (!Schema::hasColumn('employee_details', 'onboard_completed')) {
            Schema::table('employee_details', function (Blueprint $table) {
                $table->boolean('onboard_completed')->default(0)->after('reporting_to');
            });
        }

        if (!Schema::hasColumn('employee_details', 'offboard_completed')) {
            Schema::table('employee_details', function (Blueprint $table) {
                $table->boolean('offboard_completed')->default(0)->after('onboard_completed');
            });
        }

        if (!Schema::hasColumn('employee_details', 'onboarding_status')) {
            Schema::table('employee_details', function (Blueprint $table) {
                $table->enum('onboarding_status', ['old', 'new', 'offboarding'])->default('old')->after('offboard_completed');
            });
        }

        // Update existing records to set proper status
        $companies = Company::all();
        
        foreach ($companies as $company) {
            $employeeDetails = EmployeeDetails::where('company_id', $company->id)->get();

            foreach($employeeDetails as $employee) {
                // Check onboarding tasks
                $onboardTasks = DB::table('onboarding_completed_task')
                    ->where('employee_id', $employee->user_id)
                    ->where('type', 'onboard')
                    ->count();

                $offboardTasks = DB::table('onboarding_completed_task')
                    ->where('employee_id', $employee->user_id)
                    ->where('type', 'offboard')
                    ->count();

                // Set default values if not set
                if (!isset($employee->onboard_completed)) {
                    $employee->onboard_completed = 0;
                }
                
                if (!isset($employee->offboard_completed)) {
                    $employee->offboard_completed = 0;
                }
                
                if (!isset($employee->onboarding_status)) {
                    $employee->onboarding_status = 'old';
                }

                // Determine proper status based on existing data
                if ($onboardTasks > 0) {
                    $completedOnboardTasks = DB::table('onboarding_completed_task')
                        ->where('employee_id', $employee->user_id)
                        ->where('type', 'onboard')
                        ->where('status', 'completed')
                        ->count();

                    if ($completedOnboardTasks == $onboardTasks) {
                        $employee->onboard_completed = 1;
                        $employee->onboarding_status = 'old';
                    } else {
                        $employee->onboard_completed = 0;
                        $employee->onboarding_status = 'new';
                    }
                }

                if ($offboardTasks > 0) {
                    $completedOffboardTasks = DB::table('onboarding_completed_task')
                        ->where('employee_id', $employee->user_id)
                        ->where('type', 'offboard')
                        ->where('status', 'completed')
                        ->count();

                    if ($completedOffboardTasks == $offboardTasks) {
                        $employee->offboard_completed = 1;
                        $employee->onboarding_status = 'old';
                    } else {
                        $employee->offboard_completed = 0;
                        if ($employee->onboard_completed == 1) {
                            $employee->onboarding_status = 'offboarding';
                        }
                    }
                }

                $employee->save();
            }
        }

        // Update existing onboarding_completed_task records to set proper type and employee_id
        DB::table('onboarding_completed_task')->whereNull('type')->update(['type' => 'onboard']);
        
        // Set employee_id for records that don't have it
        DB::statement('
            UPDATE onboarding_completed_task 
            SET employee_id = (
                SELECT user_id 
                FROM employee_details 
                WHERE employee_details.id = onboarding_completed_task.id
            ) 
            WHERE employee_id IS NULL
        ');

        // Set status for records that don't have it
        DB::table('onboarding_completed_task')->whereNull('status')->update(['status' => 'pending']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove added columns if they exist
        if (Schema::hasColumn('onboarding_completed_task', 'type')) {
            Schema::table('onboarding_completed_task', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }

        if (Schema::hasColumn('onboarding_completed_task', 'employee_id')) {
            Schema::table('onboarding_completed_task', function (Blueprint $table) {
                $table->dropForeign(['employee_id']);
                $table->dropColumn('employee_id');
            });
        }

        if (Schema::hasColumn('onboarding_completed_task', 'status')) {
            Schema::table('onboarding_completed_task', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }

        if (Schema::hasColumn('employee_details', 'onboard_completed')) {
            Schema::table('employee_details', function (Blueprint $table) {
                $table->dropColumn('onboard_completed');
            });
        }

        if (Schema::hasColumn('employee_details', 'offboard_completed')) {
            Schema::table('employee_details', function (Blueprint $table) {
                $table->dropColumn('offboard_completed');
            });
        }

        if (Schema::hasColumn('employee_details', 'onboarding_status')) {
            Schema::table('employee_details', function (Blueprint $table) {
                $table->dropColumn('onboarding_status');
            });
        }
    }
};
