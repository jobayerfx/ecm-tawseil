<?php

namespace Modules\Policy\Listeners;

use App\Models\ModuleSetting;
use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\PermissionType;
use App\Scopes\CompanyScope;

class CompanyCreatedListener
{

    public function handle($event)
    {
        $company = $event->company;
        $roles = ['employee', 'admin'];
        ModuleSetting::createRoleSettingEntry('policy', $roles, $company);

        // Add view_policy permission to employee role after module permissions are set
        $policyModule = \App\Models\Module::where('module_name', 'policy')->first();

        if ($policyModule) {
            $permission = Permission::where('name', 'view_policy')
                ->where('module_id', $policyModule->id)
                ->first();

            if ($permission) {
                $role = Role::withoutGlobalScope(CompanyScope::class)
                    ->where('name', 'employee')
                    ->where('company_id', $company->id)
                    ->first();

                if ($role) {
                    $updated = PermissionRole::where('permission_id', $permission->id)
                        ->where('role_id', $role->id)
                        ->update([
                            'permission_type_id' => PermissionType::OWNED,
                        ]);

                    if ($updated === 0) {
                        PermissionRole::create([
                            'permission_id' => $permission->id,
                            'role_id' => $role->id,
                            'permission_type_id' => PermissionType::OWNED,
                        ]);
                    }
                }
            }
        }
    }

}
