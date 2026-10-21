<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\Sale;
use App\Services\VoucherService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            DB::beginTransaction();

            $activeSaleIds = Sale::pluck('id')->toArray();
            $activeInvoiceNos = Sale::pluck('invoice_no')->toArray();

            // 1. Direct 'Sale Delete' entries
            $orphaned = CustomerLedger::where(function($q) {
                    $q->where('transaction_type', 'like', '%Delete%')
                      ->orWhere('description', 'like', '%Sale Deleted%');
                })
                ->get();

            // 2. Original sale entries whose sales were deleted
            $allSaleEntries = CustomerLedger::where(function($q) {
                    $q->where('transaction_type', 'Sale')
                      ->orWhere('transaction_type', 'Sale Update')
                      ->orWhere('description', 'like', '%Sale Invoice%');
                })
                ->get();

            $orphanedSaleEntries = collect();
            foreach ($allSaleEntries as $entry) {
                $ref = $entry->reference_id;
                $isNumeric = is_numeric($ref);
                $existsById = $isNumeric && in_array((int)$ref, $activeSaleIds);
                $existsByInv = in_array((string)$ref, $activeInvoiceNos);
                if (!$existsById && !$existsByInv) {
                    $orphanedSaleEntries->push($entry);
                }
            }

            // 3. Entries with empty transaction_type and empty reference_id that have debit > 0
            $emptyRefSales = CustomerLedger::where(function($q) {
                    $q->whereNull('transaction_type')->orWhere('transaction_type', '');
                })
                ->where(function($q) {
                    $q->whereNull('reference_id')->orWhere('reference_id', '');
                })
                ->where('total_debit', '>', 0)
                ->get();

            $toDeleteIds = $orphaned->pluck('id')
                ->concat($orphanedSaleEntries->pluck('id'))
                ->concat($emptyRefSales->pluck('id'))
                ->unique()
                ->toArray();

            if (!empty($toDeleteIds)) {
                CustomerLedger::whereIn('id', $toDeleteIds)->delete();
            }

            // 4. Recalculate customer ledgers
            $allCustomerIds = Customer::pluck('id')->toArray();
            foreach ($allCustomerIds as $cId) {
                VoucherService::recalculateCustomerLedger((int)$cId);
            }

            // 5. Sync active sales previous_balance and total_balance
            $sales = Sale::all();
            foreach ($sales as $sale) {
                if ($sale->customer_id) {
                    $saleLedger = CustomerLedger::where('customer_id', $sale->customer_id)
                        ->where('reference_id', (string)$sale->id)
                        ->first();
                    if ($saleLedger) {
                        $sale->previous_balance = (float)$saleLedger->previous_balance;
                        $sale->total_balance = (float)$saleLedger->closing_balance;
                        $sale->save();
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Migration cleanup_deleted_sales_customer_ledgers failed: " . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data cleanup migration: no rollback necessary
    }
};
