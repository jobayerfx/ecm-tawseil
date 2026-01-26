<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Company;
use App\Models\Permission;
use App\Scopes\CompanyScope;
use App\Models\ModuleSetting;
use App\Models\PermissionRole;
use App\Models\UserPermission;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up()
    {
        $policyModule = \App\Models\Module::firstOrCreate(['module_name' => 'policy']);

        $permissionTypes = [
            ['name' => 'add_policy', 'display_name' => 'Add Policy', 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_NONE],
            ['name' => 'view_policy', 'display_name' => 'View Policy', 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5],
            ['name' => 'edit_policy', 'display_name' => 'Edit Policy', 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5],
            ['name' => 'delete_policy', 'display_name' => 'Delete Policy', 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5],
            ['name' => 'view_acknowledged', 'display_name' => 'View Acknowledged', 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_4_OWNED_2_NONE_5],
            ['name' => 'view_non_acknowledged', 'display_name' => 'View Non Acknowledged', 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_NONE]
        ];

        $companies = Company::select('id');

        foreach ($companies as $company) {
            $roles = ['employee', 'admin'];
            ModuleSetting::createRoleSettingEntry('policy', $roles, $company);
        }

        foreach ($permissionTypes as $key => $permissionType) {

            $permission = Permission::firstOrCreate([
                'name' => $permissionType['name'],
                'display_name' => $permissionType['display_name'],
                'is_custom' => $permissionType['is_custom'],
                'module_id' => $policyModule->id,
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


        Schema::create('policies', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->date('date')->nullable();
            $table->enum('signature_required', ['yes', 'no'])->default('no');
            $table->string('file')->nullable();
            $table->string('department_id_json')->nullable();
            $table->string('designation_id_json')->nullable();
            $table->string('employment_type_json')->nullable();
            $table->unsignedInteger('added_by')->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->unsignedInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->timestamps();
        });

        Schema::create('policy_employee_acknowledged', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->unsignedInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->unsignedInteger('policy_id')->nullable();
            $table->foreign('policy_id')->references('id')->on('policies')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->string('signature_file')->nullable();
            $table->dateTime('acknowledged_on')->nullable();
            $table->string('ip')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
