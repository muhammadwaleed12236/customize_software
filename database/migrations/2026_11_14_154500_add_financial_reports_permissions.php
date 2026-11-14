<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $perms = [
            'report.trial.balance.view',
            'report.income.statement.view',
            'report.balance.sheet.view',
        ];

        foreach ($perms as $perm) {
            Permission::firstOrCreate([
                'name'       => $perm,
                'guard_name' => 'web',
            ]);
        }

        $superAdmin = Role::where('name', 'super admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($perms);
        }

        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo($perms);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $perms = [
            'report.trial.balance.view',
            'report.income.statement.view',
            'report.balance.sheet.view',
        ];

        Permission::whereIn('name', $perms)->delete();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
