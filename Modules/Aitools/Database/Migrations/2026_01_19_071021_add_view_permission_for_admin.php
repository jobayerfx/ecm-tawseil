<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Module;
use App\Models\Company;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\UserPermission;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update this module name if you need a different module
        $module = Module::where('module_name', 'aitools')->first();

        if ($module) {
            // Permissions must be created in this exact order to match UI columns:
            // Column 1: Add, Column 2: View, Column 3: Update/Edit, Column 4: Delete
            $permissions = [
                [
                    'module_id' => $module->id,
                    'name' => 'add_aitools',
                    'display_name' => 'Add',
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 0
                ],
                [
                    'module_id' => $module->id,
                    'name' => 'view_aitools',
                    'display_name' => 'View',
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 0
                ],
                [
                    'module_id' => $module->id,
                    'name' => 'edit_aitools',
                    'display_name' => 'Edit',
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 0
                ],
                [
                    'module_id' => $module->id,
                    'name' => 'delete_aitools',
                    'display_name' => 'Delete',
                    'allowed_permissions' => Permission::ALL_NONE,
                    'is_custom' => 0
                ],
            ];

            $companies = Company::select('id')->get();

            foreach ($permissions as $permissionData) {
                $permission = Permission::updateOrCreate(
                    [
                        'name' => $permissionData['name'],
                        'module_id' => $permissionData['module_id'],
                    ],
                    [
                        'display_name' => $permissionData['display_name'],
                        'is_custom' => $permissionData['is_custom'],
                        'allowed_permissions' => $permissionData['allowed_permissions'],
                    ]
                );

                // Assign all permissions to admin role and all admin users
                foreach ($companies as $company) {
                    // Add permission to admin role only with "All" access
                    $adminRole = Role::where('name', 'admin')
                        ->where('company_id', $company->id)
                        ->first();

                    if ($adminRole) {
                        $permissionRole = PermissionRole::where('permission_id', $permission->id)
                            ->where('role_id', $adminRole->id)
                            ->first();

                        if (!$permissionRole) {
                            PermissionRole::create([
                                'permission_id' => $permission->id,
                                'role_id' => $adminRole->id,
                                'permission_type_id' => 4, // All
                            ]);
                        }
                    }
                }

                // Add permission to all admin users across all companies
                $adminUsers = User::allAdmins();

                foreach ($adminUsers as $adminUser) {
                    $userPermission = UserPermission::where('user_id', $adminUser->id)
                        ->where('permission_id', $permission->id)
                        ->first();

                    if (!$userPermission) {
                        UserPermission::create([
                            'user_id' => $adminUser->id,
                            'permission_id' => $permission->id,
                            'permission_type_id' => 4, // All
                        ]);
                    }

                    // Clear cache for this user
                    cache()->forget('sidebar_user_perms_' . $adminUser->id);
                    cache()->forget('permission-' . $permission->name . '-' . $adminUser->id);
                }
            }
        }

        // Clear application cache
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $module = Module::where('module_name', 'aitools')->first();

        if (!is_null($module)) {
            $permissions = ['add_aitools', 'view_aitools', 'edit_aitools', 'delete_aitools'];

            foreach ($permissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)
                    ->where('module_id', $module->id)->first();

                if ($permission) {
                    PermissionRole::where('permission_id', $permission->id)->delete();
                    UserPermission::where('permission_id', $permission->id)->delete();
                    $permission->delete();
                }
            }
        }
    }
};
