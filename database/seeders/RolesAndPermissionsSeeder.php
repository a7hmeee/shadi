<?php

namespace Database\Seeders;

use App\Domains\Identity\Enums\PermissionName;
use App\Domains\Identity\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Schema::disableForeignKeyConstraints();

        DB::table('roles')->delete();
        DB::table('model_has_roles')->delete();
        DB::table('model_has_permissions')->delete();
        DB::table('permissions')->delete();

        Schema::enableForeignKeyConstraints();

        $permissions = collect(PermissionName::cases())->map(fn (PermissionName $name) => [
            'name' => $name->value,
            'guard_name' => 'web',
        ])->toArray();

        Permission::insert($permissions);

        $roles = [
            UserRole::SUPER_ADMIN->value => PermissionName::cases(),
            UserRole::ADMIN->value => [
                PermissionName::DASHBOARD_VIEW,
                PermissionName::DECISION_SUPPORT_VIEW,
                PermissionName::BENEFICIARIES_VIEW,
                PermissionName::BENEFICIARIES_CREATE,
                PermissionName::BENEFICIARIES_UPDATE,
                PermissionName::BENEFICIARIES_DELETE,
                PermissionName::AID_REQUESTS_VIEW,
                PermissionName::AID_REQUESTS_REVIEW,
                PermissionName::AID_REQUESTS_APPROVE,
                PermissionName::AID_REQUESTS_REJECT,
                PermissionName::AID_REQUESTS_MARK_DELIVERED,
                PermissionName::AID_REQUEST_ATTACHMENTS_VIEW,
                PermissionName::AID_REQUEST_ATTACHMENTS_UPLOAD,
                PermissionName::AID_REQUEST_ATTACHMENTS_DELETE,
                PermissionName::AID_REQUEST_ATTACHMENTS_VERIFY,
                PermissionName::REPORTS_VIEW,
                PermissionName::EMPLOYEES_MANAGE,
            ],
            UserRole::EMPLOYEE->value => [
                PermissionName::DASHBOARD_VIEW,
                PermissionName::DECISION_SUPPORT_VIEW,
                PermissionName::BENEFICIARIES_VIEW,
                PermissionName::BENEFICIARIES_CREATE,
                PermissionName::BENEFICIARIES_UPDATE,
                PermissionName::AID_REQUESTS_VIEW,
                PermissionName::AID_REQUESTS_REVIEW,
                PermissionName::AID_REQUEST_ATTACHMENTS_VIEW,
                PermissionName::AID_REQUEST_ATTACHMENTS_UPLOAD,
                PermissionName::AID_REQUEST_ATTACHMENTS_VERIFY,
                PermissionName::REPORTS_VIEW,
            ],
            UserRole::BENEFICIARY->value => [],
        ];

        foreach ($roles as $roleName => $permissionCases) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            if (empty($permissionCases)) {
                continue;
            }

            $permissionNames = collect($permissionCases)->map(fn (PermissionName $p) => $p->value)->toArray();
            $permissions = Permission::whereIn('name', $permissionNames)->where('guard_name', 'web')->get();
            $role->syncPermissions($permissions);
        }

        $adminUser = User::where('email', 'admin@aid.test')->first();

        if (! $adminUser) {
            $adminUser = User::create([
                'name' => 'Super Admin',
                'email' => 'admin@aid.test',
                'password' => Hash::make('password'),
            ]);
        }

        $adminUser->syncRoles([UserRole::SUPER_ADMIN->value]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
