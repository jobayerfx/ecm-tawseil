<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Company;
use App\Models\Module;
use App\Models\ModuleSetting;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\UserPermission;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Policy\Entities\PolicySetting;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up()
    {
        // Remove old permissions...
        $oldPermissionTypes = [
            ['name' => 'add_policy', 'display_name' => 'Add Policy', 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_NONE],
            ['name' => 'view_policy', 'display_name' => 'View Policy', 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5],
            ['name' => 'edit_policy', 'display_name' => 'Edit Policy', 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5],
            ['name' => 'delete_policy', 'display_name' => 'Delete Policy', 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5],
            ['name' => 'view_acknowledged', 'display_name' => 'View Acknowledged', 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_4_OWNED_2_NONE_5],
            ['name' => 'view_non_acknowledged', 'display_name' => 'View Non Acknowledged', 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_NONE]
        ];

        $policyModule = Module::where('module_name', 'policy')->first();

        ModuleSetting::where('module_name', 'policy')->delete();

        $companies = Company::select('id');

        foreach ($oldPermissionTypes as $permissionType) {

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
                    PermissionRole::where('permission_id', $permission->id)->where('role_id', $role->id)->where('permission_type_id', 4)->delete();
                }


                $admins = User::allAdmins($company->id);

                foreach ($admins as $admin) {
                    UserPermission::where('user_id', $admin->id)->where('permission_id', $permission->id)->where('permission_type_id', 4)->delete();
                }

            }

            Permission::where('name', $permissionType['name'])->delete();

        }

        // Added publish_date and deleted_at column...
        if (!Schema::hasColumn('publish_date', 'status', 'deleted_at')) {
            Schema::table('policies', function (Blueprint $table) {
                $table->date('publish_date')->nullable()->after('date');
                $table->enum('status', ['draft', 'published'])->default('draft')->after('updated_by');
                $table->softDeletes()->index();
            });
        }

        // Insert module permissions again with package_id
        $permissionTypes = [
            ['name' => 'add_policy', 'display_name' => 'Add Policy', 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_NONE],
            ['name' => 'view_policy', 'display_name' => 'View Policy', 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5],
            ['name' => 'edit_policy', 'display_name' => 'Edit Policy', 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5],
            ['name' => 'delete_policy', 'display_name' => 'Delete Policy', 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5],
            ['name' => 'can_archive_policy', 'display_name' => 'Can Archive Policy', 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_NONE],
            ['name' => 'view_non_acknowledged', 'display_name' => 'View Non Acknowledged', 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_NONE],
            ['name' => 'view_acknowledged', 'display_name' => 'View Acknowledged', 'is_custom' => 1, 'allowed_permissions' => Permission::ALL_4_OWNED_2_NONE_5]
        ];

        Company::chunk(50, function ($companies) {
            foreach ($companies as $company) {
                PolicySetting::addModuleSetting($company);
            }
        });

        foreach ($permissionTypes as $key => $permissionType) {

            $permission = Permission::firstOrCreate([
                'name' => $permissionType['name'],
                'display_name' => $permissionType['display_name'],
                'is_custom' => $permissionType['is_custom'],
                'module_id' => $policyModule->id,
                'allowed_permissions' => $permissionType['allowed_permissions'],
            ]);

            Company::chunk(50, function ($companies) use($permission) {
                foreach ($companies as $company) {

                    $role = Role::where('name', 'admin')->where('company_id', $company->id)->first();

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
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasColumn('publish_date', 'status', 'deleted_at')) {
            Schema::table('policies', function (Blueprint $table) {
                $table->dropColumn(['publish_date', 'status', 'deleted_at']);
            });
        }
    }

};
