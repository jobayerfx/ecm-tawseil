<?php

use App\Models\Role;
use App\Models\Company;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\PermissionType;
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
        $policyModule = \App\Models\Module::where('module_name', 'policy')->first();

        if (!$policyModule) {
            return;
        }

        $permission = Permission::where('name', 'view_policy')
            ->where('module_id', $policyModule->id)
            ->first();

        if (!$permission) {
            return;
        }

        $companies = Company::select('id')->get();

        foreach ($companies as $company) {
            $role = Role::where('name', 'employee')
                ->where('company_id', $company->id)
                ->first();

            if ($role) {
                $permissionData = PermissionRole::where('permission_id', $permission->id)
                    ->where('role_id', $role->id)
                    ->first();

                if (is_null($permissionData)) {
                    $permissionRole = new PermissionRole();
                    $permissionRole->permission_id = $permission->id;
                    $permissionRole->role_id = $role->id;
                    $permissionRole->permission_type_id = PermissionType::OWNED;
                    $permissionRole->save();
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

