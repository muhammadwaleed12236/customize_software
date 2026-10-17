<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Branch;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;

/**
 * ERP: Role-Based Warehouse Data Level Security
 *
 * Access Hierarchy:
 *  1. super admin       → ALL warehouses in the system
 *  2. branch admin      → ALL warehouses linked to their branch (via branch_warehouse table)
 *  3. Sales Officer,
 *     Purchase Officer,
 *     Warehouse Manager,
 *     Other Staff       → ONLY warehouses explicitly assigned in user_warehouses table
 *                         (can span multiple branches if Super Admin assigned them)
 */
trait WarehouseScope
{
    /**
     * Returns array of warehouse IDs the current user is allowed to access.
     *
     * @return array<int>
     */
    protected function allowedWarehouses(): array
    {
        if (!Auth::check()) {
            return [];
        }

        $user = Auth::user();

        // ────────────────────────────────────────────────────────────
        // Rule 1: Super Admin → access to ALL warehouses
        // ────────────────────────────────────────────────────────────
        if ($user->hasRole('super admin')) {
            return Warehouse::pluck('id')->toArray();
        }

        // ────────────────────────────────────────────────────────────
        // Rule 2: Branch Admin / Manager → ALL warehouses in their branch
        // ────────────────────────────────────────────────────────────
        if ($user->hasRole('branch admin') || $user->hasRole('admin') || $user->hasRole('branch manager') || $user->hasRole('manager')) {
            $branch = Branch::with('warehouses')->find($user->branch_id);
            if (!$branch) return [];
            return $branch->warehouses()->pluck('warehouses.id')->toArray();
        }

        // ────────────────────────────────────────────────────────────
        // Rule 3: Other roles → explicitly assigned warehouses
        //         fallback to own branch warehouses if none explicitly assigned
        // ────────────────────────────────────────────────────────────
        $assigned = $user->assignedWarehouseIds();
        if (!empty($assigned)) {
            return $assigned;
        }

        if ($user->branch_id) {
            $branch = Branch::with('warehouses')->find($user->branch_id);
            return $branch ? $branch->warehouses()->pluck('warehouses.id')->toArray() : [];
        }

        return [];
    }

    /**
     * Check if current user can access a specific warehouse.
     */
    protected function canAccessWarehouse(int $warehouseId): bool
    {
        return in_array($warehouseId, $this->allowedWarehouses());
    }

    /**
     * Returns Eloquent query scope for Warehouse model
     * filtered to only allowed warehouses.
     */
    protected function allowedWarehouseQuery()
    {
        return Warehouse::whereIn('id', $this->allowedWarehouses());
    }
}
