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

class AddServermanagerPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // First, ensure the module exists in the modules table
        $module = Module::updateOrCreate(
            ['module_name' => 'servermanager'],
            ['description' => 'Server Manager for hosting and domain management']
        );

        // Define Server Manager permissions
        $serverManagerPermissions = [
            [
                'name' => 'view_hosting',
                'display_name' => 'View Hosting',
                'allowed_permissions' => json_encode(['all' => 4, 'added' => 1, 'none' => 5]),
                'is_custom' => 1,
            ],
            [
                'name' => 'add_hosting',
                'display_name' => 'Add Hosting',
                'allowed_permissions' => json_encode(['all' => 4, 'none' => 5]),
                'is_custom' => 1,
            ],
            [
                'name' => 'edit_hosting',
                'display_name' => 'Edit Hosting',
                'allowed_permissions' => json_encode(['all' => 4, 'added' => 1, 'none' => 5]),
                'is_custom' => 1,
            ],
            [
                'name' => 'delete_hosting',
                'display_name' => 'Delete Hosting',
                'allowed_permissions' => json_encode(['all' => 4, 'added' => 1, 'none' => 5]),
                'is_custom' => 1,
            ],
            [
                'name' => 'view_domain',
                'display_name' => 'View Domain',
                'allowed_permissions' => json_encode(['all' => 4, 'added' => 1, 'none' => 5]),
                'is_custom' => 1,
            ],
            [
                'name' => 'add_domain',
                'display_name' => 'Add Domain',
                'allowed_permissions' => json_encode(['all' => 4, 'none' => 5]),
                'is_custom' => 1,
            ],
            [
                'name' => 'edit_domain',
                'display_name' => 'Edit Domain',
                'allowed_permissions' => json_encode(['all' => 4, 'added' => 1, 'none' => 5]),
                'is_custom' => 1,
            ],
            [
                'name' => 'delete_domain',
                'display_name' => 'Delete Domain',
                'allowed_permissions' => json_encode(['all' => 4, 'added' => 1, 'none' => 5]),
                'is_custom' => 1,
            ],
        ];

        // Create or update permissions
        foreach ($serverManagerPermissions as $permissionData) {
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
     *
     * @return void
     */
    public function down()
    {
        // Get the Server Manager module
        $module = Module::where('module_name', 'servermanager')->first();

        if ($module) {
            // Delete all Server Manager permissions
            Permission::where('module_id', $module->id)->delete();
        }
    }
}
