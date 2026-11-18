<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Add report.receivable.view and report.payable.view permissions.
     *
     * - Assigned to 'super admin' always.
     * - Assigned to roles that already have related report access:
     *     report.receivable.view  -> roles with report.customer.ledger.view
     *     report.payable.view     -> roles with report.vendor.ledger.view
     * No existing logic is changed.
     */
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $newPerms = [
            'report.receivable.view',
            'report.payable.view',
        ];

        foreach ($newPerms as $perm) {
            Permission::firstOrCreate([
                'name'       => $perm,
                'guard_name' => 'web',
            ]);
        }

        // Super admin gets everything
        $superAdmin = Role::where('name', 'super admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($newPerms);
        }

        // Roles that have report.customer.ledger.view -> give report.receivable.view
        $rolesWithCustomerLedger = Role::whereHas('permissions', function ($q) {
            $q->where('name', 'report.customer.ledger.view');
        })->get();

        foreach ($rolesWithCustomerLedger as $role) {
            if (!$role->hasPermissionTo('report.receivable.view')) {
                $role->givePermissionTo('report.receivable.view');
            }
        }

        // Roles that have report.vendor.ledger.view -> give report.payable.view
        $rolesWithVendorLedger = Role::whereHas('permissions', function ($q) {
            $q->where('name', 'report.vendor.ledger.view');
        })->get();

        foreach ($rolesWithVendorLedger as $role) {
            if (!$role->hasPermissionTo('report.payable.view')) {
                $role->givePermissionTo('report.payable.view');
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migration – remove the two permissions.
     */
    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::whereIn('name', [
            'report.receivable.view',
            'report.payable.view',
        ])->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
