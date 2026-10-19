<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Account;
use App\Models\AccountLedgerEntry;
use App\Models\ReceiptsVoucher;
use App\Models\PaymentVoucher;
use App\Models\ExpenseVoucher;
use Illuminate\Support\Facades\DB;

class CleanDuplicateAccountLedgerEntries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ledger:clean-duplicates {--dry-run : Only show what will be deleted without making changes} {--account= : Clean a specific account ID only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up duplicate and orphaned account ledger entries and recalculate running balances';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $targetAccount = $this->option('account');

        $this->info($isDryRun ? "=== RUNNING IN DRY RUN MODE (NO CHANGES SAVED) ===" : "=== CLEANING UP DUPLICATE & ORPHAN LEDGER ENTRIES ===");

        $accountsQuery = Account::query();
        if ($targetAccount) {
            $accountsQuery->where('id', $targetAccount);
        }
        $accounts = $accountsQuery->get();

        $totalOrphansDeleted = 0;
        $totalDuplicatesDeleted = 0;

        foreach ($accounts as $account) {
            $accId = $account->id;
            $this->line("\nChecking Account #{$accId}: {$account->title} ({$account->account_code})");

            // 1. Identify orphan receipts (voucher_id is set but does not exist in receipts_vouchers)
            $orphanReceiptIds = AccountLedgerEntry::where('account_id', $accId)
                ->where('voucher_type', 'receipt')
                ->whereNotNull('voucher_id')
                ->whereNotIn('voucher_id', function ($query) {
                    $query->select('id')->from('receipts_vouchers');
                })
                ->pluck('id')
                ->toArray();

            // 2. Identify orphan payments
            $orphanPaymentIds = AccountLedgerEntry::where('account_id', $accId)
                ->where('voucher_type', 'payment')
                ->whereNotNull('voucher_id')
                ->whereNotIn('voucher_id', function ($query) {
                    $query->select('id')->from('payment_vouchers');
                })
                ->pluck('id')
                ->toArray();

            // 3. Identify orphan expenses
            $orphanExpenseIds = AccountLedgerEntry::where('account_id', $accId)
                ->where('voucher_type', 'expense')
                ->whereNotNull('voucher_id')
                ->whereNotIn('voucher_id', function ($query) {
                    $query->select('id')->from('expense_vouchers');
                })
                ->pluck('id')
                ->toArray();

            $allOrphanIds = array_unique(array_merge($orphanReceiptIds, $orphanPaymentIds, $orphanExpenseIds));

            if (!empty($allOrphanIds)) {
                $this->warn("  Found " . count($allOrphanIds) . " orphan entries (vouchers deleted from DB).");
                $totalOrphansDeleted += count($allOrphanIds);
                if (!$isDryRun) {
                    AccountLedgerEntry::whereIn('id', $allOrphanIds)->delete();
                }
            }

            // 4. Identify exact duplicates by (voucher_type, voucher_id) where voucher_id is not null
            $duplicateVoucherGroups = AccountLedgerEntry::where('account_id', $accId)
                ->whereNotNull('voucher_id')
                ->select('voucher_type', 'voucher_id', DB::raw('count(*) as count'), DB::raw('MIN(id) as keep_id'))
                ->groupBy('voucher_type', 'voucher_id')
                ->having('count', '>', 1)
                ->get();

            $duplicateIds = [];
            foreach ($duplicateVoucherGroups as $group) {
                $extraIds = AccountLedgerEntry::where('account_id', $accId)
                    ->where('voucher_type', $group->voucher_type)
                    ->where('voucher_id', $group->voucher_id)
                    ->where('id', '!=', $group->keep_id)
                    ->pluck('id')
                    ->toArray();

                $duplicateIds = array_merge($duplicateIds, $extraIds);
            }

            // 5. Identify duplicate sales invoices (voucher_no LIKE 'INV-%' and voucher_type = 'receipt')
            $invGroups = AccountLedgerEntry::where('account_id', $accId)
                ->where('voucher_type', 'receipt')
                ->where('voucher_no', 'like', 'INV-%')
                ->whereNotIn('id', $allOrphanIds)
                ->select('voucher_no', DB::raw('count(*) as count'))
                ->groupBy('voucher_no')
                ->having('count', '>', 1)
                ->get();

            foreach ($invGroups as $invGroup) {
                $invEntries = AccountLedgerEntry::where('account_id', $accId)
                    ->where('voucher_type', 'receipt')
                    ->where('voucher_no', $invGroup->voucher_no)
                    ->whereNotIn('id', $allOrphanIds)
                    ->orderBy('id', 'asc')
                    ->get();

                // Find if one of them matches a valid receipts_voucher ID
                $validEntry = null;
                foreach ($invEntries as $entry) {
                    if ($entry->voucher_id && ReceiptsVoucher::where('id', $entry->voucher_id)->exists()) {
                        $validEntry = $entry;
                        break;
                    }
                }

                // If none or multiple, keep the first one and mark the rest as duplicates
                $keepEntryId = $validEntry ? $validEntry->id : $invEntries->first()->id;

                foreach ($invEntries as $entry) {
                    if ($entry->id !== $keepEntryId && !in_array($entry->id, $duplicateIds)) {
                        $duplicateIds[] = $entry->id;
                    }
                }
            }

            $duplicateIds = array_diff(array_unique($duplicateIds), $allOrphanIds);

            if (!empty($duplicateIds)) {
                $this->warn("  Found " . count($duplicateIds) . " duplicate voucher/invoice entries.");
                $totalDuplicatesDeleted += count($duplicateIds);
                if (!$isDryRun) {
                    AccountLedgerEntry::whereIn('id', $duplicateIds)->delete();
                }
            }

            // 6. Re-sequence entry_no and recalculate running balance
            if (!$isDryRun) {
                // Re-sequence entry numbers cleanly (BR-1, BR-2... or CR-1, CR-2...)
                $headName = strtolower($account->head?->name ?? '');
                $prefix = str_contains($headName, 'bank') ? 'BR' : (str_contains($headName, 'cash') ? 'CR' : 'JV');

                $entries = AccountLedgerEntry::where('account_id', $accId)
                    ->orderBy('id', 'asc')
                    ->get();

                $seq = 1;
                foreach ($entries as $entry) {
                    if ($entry->voucher_type === 'opening_balance') {
                        if ($entry->entry_no !== 'OB-1') {
                            $entry->entry_no = 'OB-1';
                            $entry->save();
                        }
                    } else {
                        $expectedEntryNo = $prefix . '-' . $seq;
                        if ($entry->entry_no !== $expectedEntryNo) {
                            $entry->entry_no = $expectedEntryNo;
                            $entry->save();
                        }
                        $seq++;
                    }
                }

                // Recalculate running balance
                AccountLedgerEntry::recalculateRunningBalance($accId);

                // Update account opening_balance or current balance
                $lastEntry = AccountLedgerEntry::where('account_id', $accId)->orderBy('id', 'desc')->first();
                if ($lastEntry) {
                    $this->info("  Cleaned! Final Balance: " . number_format($lastEntry->running_balance, 2) . " (Total valid entries: {$entries->count()})");
                }
            }
        }

        $this->info("\n=== SUMMARY ===");
        $this->info("Total orphan entries deleted: {$totalOrphansDeleted}");
        $this->info("Total duplicate entries deleted: {$totalDuplicatesDeleted}");
        $this->info($isDryRun ? "Dry run complete. Run without --dry-run to apply changes." : "Cleanup complete! All balances recalculated.");

        return 0;
    }
}
