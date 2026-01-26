<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Company;
use App\Models\Module;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPermission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get the Server Manager module
        $module = Module::where('module_name', 'servermanager')->first();

        if (!$module) {
            return;
        }

        // Define Provider permissions
        $providerPermissions = [
            [
                'name' => 'view_provider',
                'display_name' => 'View Provider',
                'allowed_permissions' => json_encode(['all' => 4, 'added' => 1, 'none' => 5]),
                'is_custom' => 1,
            ],
            [
                'name' => 'add_provider',
                'display_name' => 'Add Provider',
                'allowed_permissions' => json_encode(['all' => 4, 'none' => 5]),
                'is_custom' => 1,
            ],
            [
                'name' => 'edit_provider',
                'display_name' => 'Edit Provider',
                'allowed_permissions' => json_encode(['all' => 4, 'added' => 1, 'none' => 5]),
                'is_custom' => 1,
            ],
            [
                'name' => 'delete_provider',
                'display_name' => 'Delete Provider',
                'allowed_permissions' => json_encode(['all' => 4, 'added' => 1, 'none' => 5]),
                'is_custom' => 1,
            ],
            // [
            //     'name' => 'export_provider',
            //     'display_name' => 'Export Provider',
            //     'allowed_permissions' => json_encode(['all' => 4, 'none' => 5]),
            //     'is_custom' => 1,
            // ],
        ];

        // Create or update permissions
        foreach ($providerPermissions as $permissionData) {
            Permission::updateOrCreate(
                [
                    'name' => $permissionData['name'],
                    'module_id' => $module->id,
                ],
                [
                    'display_name' => $permissionData['display_name'],
                    'allowed_permissions' => $permissionData['allowed_permissions'],
                    'is_custom' => $permissionData['is_custom'],
                ]
            );
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Get the Server Manager module
        $module = Module::where('module_name', 'servermanager')->first();

        if (!$module) {
            return;
        }

        // Get provider permissions
        $providerPermissions = Permission::where('module_id', $module->id)
            ->whereIn('name', ['view_provider', 'add_provider', 'edit_provider', 'delete_provider', 'export_provider'])
            ->get();

        // Delete permissions
        foreach ($providerPermissions as $permission) {
            $permission->delete();
        }
    }
};
