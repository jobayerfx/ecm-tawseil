<?php

use App\Models\Company;
use App\Models\DashboardWidget;
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
use Modules\Onboarding\Entities\OnboardingTask;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    public function up(): void
    {
        Schema::create('onboarding_tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->integer('company_id')->unsigned();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->enum('task_for', ['company', 'employee'])->default('employee');
            $table->boolean('employee_can_see')->default(1);
            $table->enum('type', ['onboard', 'offboard']);
            $table->integer('column_priority');
            $table->integer('added_by')->unsigned()->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onDelete('SET NULL')->onUpdate('cascade');
            $table->timestamps();
        });



        Schema::create('onboarding_completed_task', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('onboarding_task_id')->unsigned();
            $table->foreign('onboarding_task_id')->references('id')->on('onboarding_tasks')->onDelete('cascade')->onUpdate('cascade');

            $table->enum('type', ['onboard', 'offboard'])->default(null);

            $table->integer('employee_id')->unsigned();
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

            $table->date('completed_on')->nullable();

            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

            $table->string('file')->nullable();
            $table->enum('status', ['pending', 'completed'])->default('pending');

            $table->timestamps();
        });

        Schema::table('employee_details', function (Blueprint $table) {
            $table->boolean('onboard_completed')->default(0);
            $table->boolean('offboard_completed')->default(0);
            $table->enum('onboarding_status', ['old', 'new']);
        });

        $onboardingModule = \App\Models\Module::firstOrCreate(['module_name' => OnboardingTask::MODULE_NAME]);
        $id = $onboardingModule->id;

        $exists = Permission::where('name', 'manage_employee_onboarding')->exists();

        if (! $exists) {
            $permissionTypes = [
                ['name' => 'manage_employee_onboarding', 'display_name' => 'Manage Employee Onboarding', 'is_custom' => 1, 'module_id' => $id, 'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'manage_employee_offboarding', 'display_name' => 'Manage Employee Offboarding', 'is_custom' => 1, 'module_id' => $id, 'allowed_permissions' => Permission::ALL_NONE],
            ];
        }

        $companies = Company::all();

        foreach ($companies as $company) {
            $roles = ['employee', 'admin'];
            ModuleSetting::createRoleSettingEntry(OnboardingTask::MODULE_NAME, $roles, $company);
        }

        foreach ($permissionTypes as $key => $permissionType) {

            $permission = Permission::firstOrCreate([
                'name' => $permissionType['name'],
                'display_name' => $permissionType['display_name'],
                'is_custom' => $permissionType['is_custom'],
                'module_id' => $onboardingModule->id,
                'allowed_permissions' => $permissionType['allowed_permissions'],
            ]);


            foreach ($companies as $company) {

                $role = Role::where('name', 'admin')
                    ->where('company_id', $company->id)
                    ->first();

                if ($role) {
                    $permissionData = PermissionRole::where('permission_id', $permission->id)
                        ->where('role_id', $role->id)->where('permission_type_id', 4)->first();

                    if (is_null($permissionData)) {
                        $permissionRole = new PermissionRole();
                        $permissionRole->permission_id = $permission->id;
                        $permissionRole->role_id = $role->id;
                        $permissionRole->permission_type_id = 4;
                        $permissionRole->save();
                    }
                }


                $admins = User::allAdmins($company->id);

                foreach ($admins as $admin) {
                    UserPermission::firstOrCreate(
                        [
                            'user_id' => $admin->id,
                            'permission_id' => $permission->id,
                            'permission_type_id' => 4,
                        ]
                    );
                }
            }
        }

        $companies = Company::select('id')->get();

        foreach ($companies as $company) {
            // Insert dashboard widget...
            DashboardWidget::firstOrCreate([
                'widget_name' => 'onboarding',
                'status' => 1,
                'company_id' => $company->id,
                'dashboard_type' => 'private-dashboard'
            ]);

            DashboardWidget::firstOrCreate([
                'widget_name' => 'onboarding',
                'status' => 1,
                'company_id' => $company->id,
                'dashboard_type' => 'admin-hr-dashboard'
            ]);

            // Insert default onboard tasks...
            OnboardingTask::insert([
                [
                    'title' => 'Assign Laptop/ Assets / Joining kit',
                    'task_for' => 'company',
                    'employee_can_see' => true,
                    'type' => 'onboard',
                    'column_priority' => 1,
                    'company_id' => $company->id,
                ],
                [
                    'title' => 'Collect document (marksheet/ pan card / Aadhar card/salary slip/ Bank statement)',
                    'task_for' => 'employee',
                    'employee_can_see' => true,
                    'type' => 'onboard',
                    'column_priority' => 2,
                    'company_id' => $company->id,
                ],
                [
                    'title' => 'Sign Document (bond/ acceptance letter)',
                    'task_for' => 'employee',
                    'employee_can_see' => true,
                    'type' => 'onboard',
                    'column_priority' => 3,
                    'company_id' => $company->id,
                ],
                [
                    'title' => 'Provide Documents (Joining letter /welcome letter)',
                    'task_for' => 'company',
                    'employee_can_see' => true,
                    'type' => 'onboard',
                    'column_priority' => 4,
                    'company_id' => $company->id,
                ],
                [
                    'title' => 'Collect Assets',
                    'task_for' => 'company',
                    'employee_can_see' => true,
                    'type' => 'offboard',
                    'column_priority' => 1,
                    'company_id' => $company->id,
                ],
                [
                    'title' => 'Sign No Dues Certificate',
                    'task_for' => 'employee',
                    'employee_can_see' => true,
                    'type' => 'offboard',
                    'column_priority' => 2,
                    'company_id' => $company->id,
                ],
                [
                    'title' => 'Provide Experience Letter',
                    'task_for' => 'company',
                    'employee_can_see' => true,
                    'type' => 'offboard',
                    'column_priority' => 3,
                    'company_id' => $company->id,
                ]
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onboarding_tasks');
        Schema::dropIfExists('onboarding_completed_task');
        Schema::dropIfExists('onboarding_setting');
        DashboardWidget::where('widget_name', 'onboarding')->orWhere('widget_name', 'offboarding')->delete();
        Schema::table('employee_details', function (Blueprint $table) {
            $table->dropColumn('onboard_completed');
            $table->dropColumn('offboard_completed');
        });
    }
};
