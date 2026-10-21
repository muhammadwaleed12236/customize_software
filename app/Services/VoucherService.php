<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\Vendor;
use App\Models\VendorLedger;
use App\Models\Account;
use App\Models\AccountLedgerEntry;
use App\Models\ReceiptsVoucher;
use App\Models\PaymentVoucher;
use App\Models\ExpenseVoucher;
use App\Models\JournalVoucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VoucherService
{
    public static string $lastError = '';

    /* =========================================================================
     * 1. RECEIPTS VOUCHER (CRV / BRV)
     * ========================================================================= */

    /**
     * Reverse a Receipt Voucher completely.
     * Cleanly removes ledger entries and recalculates party & account balances.
     */
    public static function reverseReceiptVoucher(ReceiptsVoucher $voucher, $userId, bool $isVoid = true): bool
    {
        DB::beginTransaction();
        try {
            $totalAmount = (float) $voucher->total_amount;
            $rvid        = $voucher->rvid;
            $branchId    = (int)($voucher->branch_id ?? (auth()->check() ? auth()->user()->branch_id : 1));

            // 1. REVERSE ACCOUNT SIDE: Extract accounts and delete AccountLedgerEntry for this receipt voucher
            $accounts = self::safeDecodeArray($voucher->row_account_id);
            if (empty($accounts) && !empty($voucher->row_account_id) && is_numeric($voucher->row_account_id)) {
                $accounts = [(int)$voucher->row_account_id];
            }

            $existingAccIds = AccountLedgerEntry::where(function($q) use ($rvid, $voucher) {
                $q->where('voucher_no', $rvid);
                if ($voucher->id) {
                    $q->orWhere('voucher_id', $voucher->id);
                }
                $cleanRefs = self::safeDecodeArray($voucher->reference_no);
                foreach ($cleanRefs as $ref) {
                    $refStr = trim((string)$ref);
                    if (!empty($refStr) && !in_array($refStr, ['[""]', '""', 'null', '[null]'])) {
                        $q->orWhere('voucher_no', $refStr);
                    }
                }
            })->pluck('account_id')->toArray();

            $accounts = array_unique(array_filter(array_merge($accounts, $existingAccIds)));

            AccountLedgerEntry::where(function($q) use ($rvid, $voucher) {
                $q->where('voucher_no', $rvid);
                if ($voucher->id) {
                    $q->orWhere('voucher_id', $voucher->id);
                }
                $cleanRefs = self::safeDecodeArray($voucher->reference_no);
                foreach ($cleanRefs as $ref) {
                    $refStr = trim((string)$ref);
                    if (!empty($refStr) && !in_array($refStr, ['[""]', '""', 'null', '[null]'])) {
                        $q->orWhere('voucher_no', $refStr);
                    }
                }
            })->delete();

            // 2. REVERSE PARTY SIDE (Clean delete of customer/vendor ledgers and recalculation)
            $partyIds     = self::safeDecodeArray($voucher->party_id);
            $partyTypes   = self::safeDecodeArray($voucher->type);
            $partyAmounts = self::safeDecodeArray($voucher->party_amount);

            if (empty($partyIds) && !empty($voucher->party_id)) {
                $partyIds     = [$voucher->party_id];
                $partyTypes   = [$voucher->type];
                $partyAmounts = [(float)$voucher->total_amount];
            }

            $affectedAccountIds = $accounts;

            foreach ($partyIds as $idx => $pId) {
                if (!$pId) continue;
                $pType   = $partyTypes[$idx] ?? ($partyTypes[0] ?? 'customer');
                $pAmount = isset($partyAmounts[$idx]) && is_numeric($partyAmounts[$idx])
                    ? (float)$partyAmounts[$idx]
                    : (count($partyIds) === 1 ? $totalAmount : 0);

                if ($pType === 'customer' || $pType === 'walkin' || $pType === 'SALE_RECEIPT') {
                    CustomerLedger::where('reference_id', $rvid)->where('customer_id', $pId)->delete();
                    self::recalculateCustomerLedger((int)$pId);
                } elseif ($pType === 'vendor') {
                    VendorLedger::where('reference_id', $rvid)->where('vendor_id', $pId)->delete();
                    self::recalculateVendorLedger((int)$pId, $branchId);
                } elseif (is_numeric($pType)) {
                    $affectedAccountIds[] = (int)$pId;
                }
            }

            // Recalculate running balance for affected accounts
            foreach (array_unique(array_filter($affectedAccountIds)) as $accId) {
                AccountLedgerEntry::recalculateRunningBalance((int)$accId);
            }

            // 3. Mark Voucher Status
            if ($isVoid) {
                $voucher->processed = 0;
                $voucher->status    = 'voided';
                $voucher->save();
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            self::$lastError = $e->getMessage();
            Log::error("Receipt Voucher Reversal Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Post ledger entries for a Receipt Voucher.
     * Supports both single party and multi-party rows.
     */
    public static function applyReceiptVoucher(ReceiptsVoucher $voucher, array $data, $userId): bool
    {
        $totalAmount = (float)($data['total_amount'] ?? $voucher->total_amount);
        $rvid        = $voucher->rvid;
        $branchId    = (int)($data['branch_id'] ?? $voucher->branch_id ?? 1);
        $receiptDate = $data['receipt_date'] ?? $voucher->receipt_date ?? now()->toDateString();
        $remarks     = $data['remarks'] ?? $voucher->remarks ?? '';

        // Decode Party arrays
        $partyIds = is_array($data['party_id'] ?? null)
            ? $data['party_id']
            : (is_array($data['vendor_id'] ?? null) ? $data['vendor_id'] : self::safeDecodeArray($voucher->party_id));

        $partyTypes = is_array($data['party_type'] ?? null)
            ? $data['party_type']
            : (is_array($data['vendor_type'] ?? null)
                ? $data['vendor_type']
                : (is_array($data['type'] ?? null) ? $data['type'] : self::safeDecodeArray($voucher->type)));

        $partyAmounts = is_array($data['party_amount'] ?? null)
            ? $data['party_amount']
            : self::safeDecodeArray($voucher->party_amount);

        if (empty($partyIds) && !empty($voucher->party_id)) {
            $partyIds     = [$voucher->party_id];
            $partyTypes   = [$voucher->type];
            $partyAmounts = [(float)$voucher->total_amount];
        }

        // 1. PARTY SIDE POSTING (CREDIT)
        foreach ($partyIds as $idx => $partyId) {
            if (!$partyId) continue;
            $partyType   = $partyTypes[$idx] ?? ($partyTypes[0] ?? 'customer');
            $partyAmount = isset($partyAmounts[$idx]) && is_numeric($partyAmounts[$idx])
                ? (float)$partyAmounts[$idx]
                : (count($partyIds) === 1 ? $totalAmount : 0);

            if ($partyAmount <= 0) continue;

            if ($partyType === 'customer' || $partyType === 'walkin' || $partyType === 'SALE_RECEIPT') {
                $lastLedger = CustomerLedger::where('customer_id', $partyId)
                    ->orderBy('id', 'desc')
                    ->first();

                $customer        = Customer::find($partyId);
                $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($customer->opening_balance ?? 0);

                CustomerLedger::create([
                    'customer_id'      => $partyId,
                    'admin_or_user_id' => $userId,
                    'previous_balance' => $previousBalance,
                    'opening_balance'  => $customer->opening_balance ?? 0,
                    'total_debit'      => 0,
                    'total_credit'     => $partyAmount,
                    'closing_balance'  => $previousBalance - $partyAmount,
                    'transaction_type' => 'receipt',
                    'reference_id'     => $rvid,
                    'description'      => "Receipt Voucher #{$rvid}" . ($remarks ? " - {$remarks}" : ""),
                ]);

                self::recalculateCustomerLedger((int)$partyId);
            } elseif ($partyType === 'vendor') {
                $lastLedger = VendorLedger::where('vendor_id', $partyId)
                    ->where('branch_id', $branchId)
                    ->orderBy('id', 'desc')
                    ->first();

                $vendor          = Vendor::find($partyId);
                $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($vendor->opening_balance ?? 0);

                VendorLedger::create([
                    'vendor_id'        => $partyId,
                    'branch_id'        => $branchId,
                    'admin_or_user_id' => $userId,
                    'transaction_date' => $receiptDate,
                    'description'      => "Receipt Voucher #{$rvid}" . ($remarks ? " - {$remarks}" : ""),
                    'opening_balance'  => $vendor->opening_balance ?? 0,
                    'previous_balance' => $previousBalance,
                    'debit_amount'     => 0,
                    'credit_amount'    => $partyAmount,
                    'closing_balance'  => $previousBalance + $partyAmount,
                    'transaction_type' => 'receipt',
                    'reference_id'     => $rvid,
                ]);

                self::recalculateVendorLedger((int)$partyId, $branchId);
            } elseif (is_numeric($partyType) || in_array(strtolower(trim((string)$partyType)), ['account', 'ledger']) || !in_array(strtolower(trim((string)$partyType)), ['vendor', 'customer', 'walkin', 'sale_receipt'])) {
                $account = Account::find($partyId);
                if ($account) {
                    self::postLedgerEntry(
                        $account->id,
                        'receipt',
                        $rvid,
                        $voucher->id,
                        $receiptDate,
                        "Receipt Voucher Party Side: " . ($remarks ?: 'N/A'),
                        0,
                        $partyAmount,
                        $userId
                    );
                    AccountLedgerEntry::recalculateRunningBalance((int)$account->id);
                }
            }
        }

        // 2. ACCOUNT SIDE POSTING (DEBIT - Cash / Bank Accounts)
        $rowAccountIds = is_array($data['row_account_id'] ?? null) 
            ? $data['row_account_id'] 
            : self::safeDecodeArray($voucher->row_account_id);
        $amounts = is_array($data['amount'] ?? null) 
            ? $data['amount'] 
            : self::safeDecodeArray($voucher->amount);

        $accountTotals = [];
        foreach ($rowAccountIds as $index => $accId) {
            $rowAmount = (float)($amounts[$index] ?? 0);
            if ($rowAmount > 0 && $accId) {
                $accountTotals[(int)$accId] = ($accountTotals[(int)$accId] ?? 0) + $rowAmount;
            }
        }

        foreach ($accountTotals as $accId => $totalAccAmount) {
            self::postLedgerEntry(
                $accId,
                'receipt',
                $rvid,
                $voucher->id,
                $receiptDate,
                "Receipt from Party: " . ($remarks ?: "Voucher #{$rvid}"),
                $totalAccAmount,
                0,
                $userId
            );
            AccountLedgerEntry::recalculateRunningBalance((int)$accId);
        }

        return true;
    }

    /**
     * Update an existing Receipt Voucher cleanly.
     * Reverses old ledger effects and re-applies new entries without duplicate reversal noise.
     */
    public static function updateReceiptVoucher(ReceiptsVoucher $voucher, array $data, $userId): bool
    {
        DB::beginTransaction();
        try {
            // 1. Cleanly revert old ledger effects
            self::reverseReceiptVoucher($voucher, $userId, false);

            // 2. Parse party arrays
            $partyTypes   = $data['party_type'] ?? (isset($data['vendor_type']) ? (is_array($data['vendor_type']) ? $data['vendor_type'] : [$data['vendor_type']]) : []);
            $partyIds     = $data['party_id'] ?? (isset($data['vendor_id']) ? (is_array($data['vendor_id']) ? $data['vendor_id'] : [$data['vendor_id']]) : []);
            $partyAmounts = $data['party_amount'] ?? [];
            $partyTels    = $data['party_tel'] ?? (isset($data['tel']) ? (is_array($data['tel']) ? $data['tel'] : [$data['tel']]) : []);

            if (empty($partyAmounts) && count($partyIds) === 1) {
                $partyAmounts = [(float)($data['total_amount'] ?? $voucher->total_amount)];
            }

            $narrationIds = is_array($data['narration_id'] ?? null) 
                ? $data['narration_id'] 
                : self::safeDecodeArray($voucher->narration_id);

            // 3. Update Voucher Record
            $voucher->update([
                'branch_id'        => (int)($data['branch_id'] ?? $voucher->branch_id ?? 1),
                'receipt_date'     => $data['receipt_date'] ?? $voucher->receipt_date,
                'type'             => json_encode($partyTypes),
                'party_id'         => json_encode($partyIds),
                'party_amount'     => json_encode($partyAmounts),
                'tel'              => json_encode($partyTels),
                'remarks'          => $data['remarks'] ?? $voucher->remarks,
                'narration_id'     => json_encode($narrationIds),
                'reference_no'     => json_encode($data['reference_no'] ?? self::safeDecodeArray($voucher->reference_no)),
                'row_account_head' => json_encode($data['row_account_head'] ?? self::safeDecodeArray($voucher->row_account_head)),
                'row_account_id'   => json_encode($data['row_account_id'] ?? self::safeDecodeArray($voucher->row_account_id)),
                'discount_value'   => json_encode($data['discount_value'] ?? self::safeDecodeArray($voucher->discount_value)),
                'rate'             => json_encode($data['rate'] ?? self::safeDecodeArray($voucher->rate)),
                'amount'           => json_encode($data['amount'] ?? self::safeDecodeArray($voucher->amount)),
                'total_amount'     => (float)($data['total_amount'] ?? $voucher->total_amount),
                'status'           => 'active',
                'processed'        => true,
            ]);

            // 4. Apply new ledger effects
            self::applyReceiptVoucher($voucher, $data, $userId);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Receipt Voucher Update Error: " . $e->getMessage());
            return false;
        }
    }


    /* =========================================================================
     * 2. PAYMENT VOUCHER (CPV / BPV)
     * ========================================================================= */

    /**
     * Reverse a Payment Voucher completely.
     * Cleanly removes ledger entries and recalculates vendor & customer ledgers.
     */
    public static function reversePaymentVoucher(PaymentVoucher $voucher, $userId, bool $isVoid = true): bool
    {
        DB::beginTransaction();
        try {
            $totalAmount = (float) $voucher->total_amount;
            $pvid        = $voucher->pvid;
            $branchId    = (int)($voucher->branch_id ?? (auth()->check() ? auth()->user()->branch_id : 1));

            // 1. REVERSE ROW ACCOUNTS: Extract accounts and delete AccountLedgerEntry for this payment voucher
            $accounts = self::safeDecodeArray($voucher->row_account_id);
            if (empty($accounts) && !empty($voucher->row_account_id) && is_numeric($voucher->row_account_id)) {
                $accounts = [(int)$voucher->row_account_id];
            }

            $existingAccIds = AccountLedgerEntry::where(function($q) use ($pvid, $voucher) {
                $q->where('voucher_no', $pvid);
                if ($voucher->id) {
                    $q->orWhere('voucher_id', $voucher->id);
                }
                $cleanRefs = self::safeDecodeArray($voucher->reference_no);
                foreach ($cleanRefs as $ref) {
                    $refStr = trim((string)$ref);
                    if (!empty($refStr) && !in_array($refStr, ['[""]', '""', 'null', '[null]'])) {
                        $q->orWhere('voucher_no', $refStr);
                    }
                }
            })->pluck('account_id')->toArray();

            $accounts = array_unique(array_filter(array_merge($accounts, $existingAccIds)));

            AccountLedgerEntry::where(function($q) use ($pvid, $voucher) {
                $q->where('voucher_no', $pvid);
                if ($voucher->id) {
                    $q->orWhere('voucher_id', $voucher->id);
                }
                $cleanRefs = self::safeDecodeArray($voucher->reference_no);
                foreach ($cleanRefs as $ref) {
                    $refStr = trim((string)$ref);
                    if (!empty($refStr) && !in_array($refStr, ['[""]', '""', 'null', '[null]'])) {
                        $q->orWhere('voucher_no', $refStr);
                    }
                }
            })->delete();

            // 2. REVERSE PARTY SIDE (Vendor/Customer/Account ledgers)
            $partyIds     = self::safeDecodeArray($voucher->party_id);
            $partyTypes   = self::safeDecodeArray($voucher->type);
            $partyAmounts = self::safeDecodeArray($voucher->party_amount);

            if (empty($partyIds) && !empty($voucher->party_id)) {
                $partyIds     = [$voucher->party_id];
                $partyTypes   = [$voucher->type];
                $partyAmounts = [(float)$voucher->total_amount];
            }

            $affectedAccountIds = $accounts;

            foreach ($partyIds as $idx => $pId) {
                if (!$pId) continue;
                $pType   = $partyTypes[$idx] ?? ($partyTypes[0] ?? 'vendor');
                $pAmount = isset($partyAmounts[$idx]) && is_numeric($partyAmounts[$idx])
                    ? (float)$partyAmounts[$idx]
                    : (count($partyIds) === 1 ? $totalAmount : 0);

                if ($pType === 'vendor') {
                    VendorLedger::where('reference_id', $pvid)->where('vendor_id', $pId)->delete();
                    self::recalculateVendorLedger((int)$pId, $branchId);
                } elseif ($pType === 'customer' || $pType === 'walkin') {
                    CustomerLedger::where('reference_id', $pvid)->where('customer_id', $pId)->delete();
                    self::recalculateCustomerLedger((int)$pId);
                } elseif (is_numeric($pType)) {
                    $affectedAccountIds[] = (int)$pId;
                }
            }

            // Recalculate running balance for affected accounts
            foreach (array_unique(array_filter($affectedAccountIds)) as $accId) {
                AccountLedgerEntry::recalculateRunningBalance((int)$accId);
            }

            // 3. Mark Voucher Status
            if ($isVoid) {
                $voucher->processed = 0;
                $voucher->status    = 'voided';
                $voucher->save();
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            self::$lastError = $e->getMessage();
            Log::error("Payment Voucher Reversal Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Post ledger entries for a Payment Voucher.
     * Supports both single party and multi-party rows.
     */
    public static function applyPaymentVoucher(PaymentVoucher $voucher, array $data, $userId): bool
    {
        $totalAmount = (float)($data['total_amount'] ?? $voucher->total_amount);
        $pvid        = $voucher->pvid;
        $branchId    = (int)($data['branch_id'] ?? $voucher->branch_id ?? (auth()->user()->branch_id ?? 1));
        $receiptDate = $data['receipt_date'] ?? $voucher->receipt_date ?? now()->toDateString();
        $remarks     = $data['remarks'] ?? $voucher->remarks ?? '';

        // 1. ROW ACCOUNTS POSTING (CREDIT - Paid from Bank/Cash)
        $rowAccountIds = is_array($data['row_account_id'] ?? null) 
            ? $data['row_account_id'] 
            : self::safeDecodeArray($voucher->row_account_id);
        $amounts = is_array($data['amount'] ?? null) 
            ? $data['amount'] 
            : self::safeDecodeArray($voucher->amount);

        $accountTotals = [];
        foreach ($rowAccountIds as $index => $accId) {
            $rowAmount = isset($amounts[$index]) ? (float)$amounts[$index] : 0;
            if ($rowAmount > 0 && $accId) {
                $accountTotals[(int)$accId] = ($accountTotals[(int)$accId] ?? 0) + $rowAmount;
            }
        }

        foreach ($accountTotals as $accId => $totalAccAmount) {
            self::postLedgerEntry(
                $accId,
                'payment',
                $pvid,
                $voucher->id,
                $receiptDate,
                $remarks ?: "Payment Voucher #{$pvid}",
                0,
                $totalAccAmount,
                $userId
            );
            AccountLedgerEntry::recalculateRunningBalance((int)$accId);
        }

        // 2. PARTY SIDE POSTING (DEBIT - Vendor payable reduced / Customer receivable increased)
        $partyIds = is_array($data['party_id'] ?? null)
            ? $data['party_id']
            : (is_array($data['vendor_id'] ?? null) ? $data['vendor_id'] : self::safeDecodeArray($voucher->party_id));

        $partyTypes = is_array($data['party_type'] ?? null)
            ? $data['party_type']
            : (is_array($data['vendor_type'] ?? null)
                ? $data['vendor_type']
                : (is_array($data['type'] ?? null) ? $data['type'] : self::safeDecodeArray($voucher->type)));

        $partyAmounts = is_array($data['party_amount'] ?? null)
            ? $data['party_amount']
            : self::safeDecodeArray($voucher->party_amount);

        if (empty($partyIds) && !empty($voucher->party_id)) {
            $partyIds     = [$voucher->party_id];
            $partyTypes   = [$voucher->type];
            $partyAmounts = [(float)$voucher->total_amount];
        }

        foreach ($partyIds as $idx => $partyId) {
            if (!$partyId) continue;
            $partyType   = $partyTypes[$idx] ?? ($partyTypes[0] ?? 'vendor');
            $partyAmount = isset($partyAmounts[$idx]) && is_numeric($partyAmounts[$idx])
                ? (float)$partyAmounts[$idx]
                : (count($partyIds) === 1 ? $totalAmount : 0);

            if ($partyAmount <= 0) continue;

            if ($partyType === 'vendor') {
                $lastLedger = VendorLedger::where('vendor_id', $partyId)
                    ->where('branch_id', $branchId)
                    ->orderBy('id', 'desc')
                    ->first();

                $vendor          = Vendor::find($partyId);
                $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($vendor->opening_balance ?? 0);

                VendorLedger::create([
                    'vendor_id'        => $partyId,
                    'branch_id'        => $branchId,
                    'admin_or_user_id' => $userId,
                    'transaction_date' => $receiptDate,
                    'description'      => "Payment Voucher #{$pvid}" . ($remarks ? " - {$remarks}" : ""),
                    'opening_balance'  => $previousBalance,
                    'previous_balance' => $previousBalance,
                    'debit_amount'     => $partyAmount,
                    'credit_amount'    => 0,
                    'closing_balance'  => $previousBalance - $partyAmount,
                    'transaction_type' => 'payment',
                    'reference_id'     => $pvid,
                ]);

                self::recalculateVendorLedger((int)$partyId, $branchId);
            } elseif ($partyType === 'customer' || $partyType === 'walkin') {
                $lastLedger = CustomerLedger::where('customer_id', $partyId)
                    ->orderBy('id', 'desc')
                    ->first();

                $customer        = Customer::find($partyId);
                $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($customer->opening_balance ?? 0);

                CustomerLedger::create([
                    'customer_id'      => $partyId,
                    'admin_or_user_id' => $userId,
                    'previous_balance' => $previousBalance,
                    'opening_balance'  => $previousBalance,
                    'total_debit'      => $partyAmount,
                    'total_credit'     => 0,
                    'closing_balance'  => $previousBalance + $partyAmount,
                    'transaction_type' => 'payment',
                    'reference_id'     => $pvid,
                    'description'      => "Payment Voucher #{$pvid}" . ($remarks ? " - {$remarks}" : ""),
                ]);

                self::recalculateCustomerLedger((int)$partyId);
            } elseif (is_numeric($partyType) || in_array(strtolower(trim((string)$partyType)), ['account', 'ledger']) || !in_array(strtolower(trim((string)$partyType)), ['vendor', 'customer', 'walkin', 'sale_receipt'])) {
                $account = Account::find($partyId);
                if ($account) {
                    self::postLedgerEntry(
                        $account->id,
                        'payment',
                        $pvid,
                        $voucher->id,
                        $receiptDate,
                        "Payment Voucher Party Side: " . ($remarks ?: 'N/A'),
                        $partyAmount,
                        0,
                        $userId
                    );
                    AccountLedgerEntry::recalculateRunningBalance((int)$account->id);
                }
            }
        }

        return true;
    }

    /**
     * Update an existing Payment Voucher cleanly.
     */
    public static function updatePaymentVoucher(PaymentVoucher $voucher, array $data, $userId): bool
    {
        DB::beginTransaction();
        try {
            // 1. Revert old ledger effects
            self::reversePaymentVoucher($voucher, $userId, false);

            // 2. Parse party arrays
            $partyTypes   = $data['party_type'] ?? (isset($data['vendor_type']) ? (is_array($data['vendor_type']) ? $data['vendor_type'] : [$data['vendor_type']]) : []);
            $partyIds     = $data['party_id'] ?? (isset($data['vendor_id']) ? (is_array($data['vendor_id']) ? $data['vendor_id'] : [$data['vendor_id']]) : []);
            $partyAmounts = $data['party_amount'] ?? [];
            $partyTels    = $data['party_tel'] ?? (isset($data['tel']) ? (is_array($data['tel']) ? $data['tel'] : [$data['tel']]) : []);

            if (empty($partyAmounts) && count($partyIds) === 1) {
                $partyAmounts = [(float)($data['total_amount'] ?? $voucher->total_amount)];
            }

            $narrationIds = is_array($data['narration_id'] ?? null) 
                ? $data['narration_id'] 
                : self::safeDecodeArray($voucher->narration_id);

            // 3. Update Voucher Record
            $voucher->update([
                'branch_id'        => (int)($data['branch_id'] ?? $voucher->branch_id ?? 1),
                'receipt_date'     => $data['receipt_date'] ?? $voucher->receipt_date,
                'type'             => json_encode($partyTypes),
                'party_id'         => json_encode($partyIds),
                'party_amount'     => json_encode($partyAmounts),
                'tel'              => json_encode($partyTels),
                'remarks'          => $data['remarks'] ?? $voucher->remarks,
                'narration_id'     => json_encode($narrationIds),
                'reference_no'     => json_encode($data['reference_no'] ?? self::safeDecodeArray($voucher->reference_no)),
                'row_account_head' => json_encode($data['row_account_head'] ?? self::safeDecodeArray($voucher->row_account_head)),
                'row_account_id'   => json_encode($data['row_account_id'] ?? self::safeDecodeArray($voucher->row_account_id)),
                'discount_value'   => json_encode($data['discount_value'] ?? self::safeDecodeArray($voucher->discount_value)),
                'amount'           => json_encode($data['amount'] ?? self::safeDecodeArray($voucher->amount)),
                'total_amount'     => (float)($data['total_amount'] ?? $voucher->total_amount),
                'status'           => 'active',
                'processed'        => true,
            ]);

            // 4. Apply new ledger effects
            self::applyPaymentVoucher($voucher, $data, $userId);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Payment Voucher Update Error: " . $e->getMessage());
            return false;
        }
    }


    /* =========================================================================
     * 3. EXPENSE VOUCHER (EV)
     * ========================================================================= */

    /**
     * Reverse an Expense Voucher completely.
     */
    public static function reverseExpenseVoucher(ExpenseVoucher $voucher, $userId, bool $isVoid = true): bool
    {
        DB::beginTransaction();
        try {
            $totalAmount = (float) $voucher->total_amount;
            $evid        = $voucher->evid;
            $date        = now()->toDateString();
            $branchId    = (int)($voucher->branch_id ?? (auth()->check() ? auth()->user()->branch_id : 1));

            // 1. REVERSE EXPENSE ACCOUNTS (Row side: Debited originally, now Credited back)
            $accounts = self::safeDecodeArray($voucher->row_account_id);
            if (empty($accounts) && !empty($voucher->row_account_id) && is_numeric($voucher->row_account_id)) {
                $accounts = [(int)$voucher->row_account_id];
            }

            $existingAccIds = AccountLedgerEntry::where(function($q) use ($evid, $voucher) {
                $q->where('voucher_no', $evid);
                if ($voucher->id) {
                    $q->orWhere('voucher_id', $voucher->id);
                }
                $cleanRefs = self::safeDecodeArray($voucher->reference_no);
                foreach ($cleanRefs as $ref) {
                    $refStr = trim((string)$ref);
                    if (!empty($refStr) && !in_array($refStr, ['[""]', '""', 'null', '[null]'])) {
                        $q->orWhere('voucher_no', $refStr);
                    }
                }
            })->pluck('account_id')->toArray();

            $accounts = array_unique(array_filter(array_merge($accounts, $existingAccIds)));

            // 1. REVERSE EXPENSE ACCOUNTS: Delete AccountLedgerEntry for this expense voucher
            AccountLedgerEntry::where(function($q) use ($evid, $voucher) {
                $q->where('voucher_no', $evid);
                if ($voucher->id) {
                    $q->orWhere('voucher_id', $voucher->id);
                }
                $cleanRefs = self::safeDecodeArray($voucher->reference_no);
                foreach ($cleanRefs as $ref) {
                    $refStr = trim((string)$ref);
                    if (!empty($refStr) && !in_array($refStr, ['[""]', '""', 'null', '[null]'])) {
                        $q->orWhere('voucher_no', $refStr);
                    }
                }
            })->delete();

            // 2. REVERSE PAYMENT SOURCE / PARTY SIDE
            $partyIds     = self::safeDecodeArray($voucher->party_id);
            $partyTypes   = self::safeDecodeArray($voucher->type);
            $partyAmounts = self::safeDecodeArray($voucher->party_amount);

            if (empty($partyIds) && !empty($voucher->party_id)) {
                $partyIds     = [$voucher->party_id];
                $partyTypes   = [$voucher->type];
                $partyAmounts = [(float)$voucher->total_amount];
            }

            $affectedAccountIds = $accounts;

            foreach ($partyIds as $idx => $pId) {
                if (!$pId) continue;
                $pType   = $partyTypes[$idx] ?? ($partyTypes[0] ?? 'vendor');
                $pAmount = isset($partyAmounts[$idx]) && is_numeric($partyAmounts[$idx])
                    ? (float)$partyAmounts[$idx]
                    : (count($partyIds) === 1 ? $totalAmount : 0);

                if ($pType === 'vendor') {
                    VendorLedger::where('reference_id', $evid)->where('vendor_id', $pId)->delete();
                    self::recalculateVendorLedger((int)$pId, $branchId);
                } elseif ($pType === 'customer' || $pType === 'walkin') {
                    CustomerLedger::where('reference_id', $evid)->where('customer_id', $pId)->delete();
                    self::recalculateCustomerLedger((int)$pId);
                } else {
                    $accountId = is_numeric($pId) ? $pId : $pType;
                    $affectedAccountIds[] = (int)$accountId;
                }
            }

            // Recalculate running balance for affected accounts
            foreach (array_unique(array_filter($affectedAccountIds)) as $accId) {
                AccountLedgerEntry::recalculateRunningBalance((int)$accId);
            }

            // 3. Mark Voucher Status
            if ($isVoid) {
                $voucher->status = 'voided';
                $voucher->save();
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            self::$lastError = $e->getMessage();
            Log::error("Expense Voucher Reversal Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Post ledger entries for an Expense Voucher.
     * Supports both single party and multi-party rows.
     */
    public static function applyExpenseVoucher(ExpenseVoucher $voucher, array $data, $userId): bool
    {
        $totalAmount = (float)($data['total_amount'] ?? $voucher->total_amount);
        $evid        = $voucher->evid;
        $branchId    = (int)($data['branch_id'] ?? $voucher->branch_id ?? (auth()->user()->branch_id ?? 1));
        $entryDate   = $data['entry_date'] ?? $voucher->entry_date ?? now()->toDateString();
        $remarks     = $data['remarks'] ?? $voucher->remarks ?? '';

        // 1. EXPENSE ACCOUNTS POSTING (DEBIT - Expense rows increased)
        $rowAccountIds = is_array($data['row_account_id'] ?? null) 
            ? $data['row_account_id'] 
            : self::safeDecodeArray($voucher->row_account_id);
        $amounts = is_array($data['amount'] ?? null) 
            ? $data['amount'] 
            : self::safeDecodeArray($voucher->amount);

        $accountTotals = [];
        foreach ($rowAccountIds as $index => $accId) {
            $rowAmount = isset($amounts[$index]) ? (float)$amounts[$index] : 0;
            if ($rowAmount > 0 && $accId) {
                $accountTotals[(int)$accId] = ($accountTotals[(int)$accId] ?? 0) + $rowAmount;
            }
        }

        foreach ($accountTotals as $accId => $totalAccAmount) {
            self::postLedgerEntry(
                $accId,
                'expense',
                $evid,
                $voucher->id,
                $entryDate,
                $remarks ?: "Expense Voucher #{$evid}",
                $totalAccAmount,
                0,
                $userId
            );
            AccountLedgerEntry::recalculateRunningBalance((int)$accId);
        }

        // 2. PAYMENT SOURCE / PARTY SIDE POSTING (CREDIT - Funds decreased)
        $partyIds = is_array($data['party_id'] ?? null)
            ? $data['party_id']
            : (is_array($data['vendor_id'] ?? null) ? $data['vendor_id'] : self::safeDecodeArray($voucher->party_id));

        $partyTypes = is_array($data['party_type'] ?? null)
            ? $data['party_type']
            : (is_array($data['vendor_type'] ?? null)
                ? $data['vendor_type']
                : (is_array($data['type'] ?? null) ? $data['type'] : self::safeDecodeArray($voucher->type)));

        $partyAmounts = is_array($data['party_amount'] ?? null)
            ? $data['party_amount']
            : self::safeDecodeArray($voucher->party_amount);

        if (empty($partyIds) && !empty($voucher->party_id)) {
            $partyIds     = [$voucher->party_id];
            $partyTypes   = [$voucher->type];
            $partyAmounts = [(float)$voucher->total_amount];
        }

        foreach ($partyIds as $idx => $partyId) {
            if (!$partyId) continue;
            $partyType   = $partyTypes[$idx] ?? ($partyTypes[0] ?? 'vendor');
            $partyAmount = isset($partyAmounts[$idx]) && is_numeric($partyAmounts[$idx])
                ? (float)$partyAmounts[$idx]
                : (count($partyIds) === 1 ? $totalAmount : 0);

            if ($partyAmount <= 0) continue;

            if ($partyType === 'vendor') {
                $lastLedger = VendorLedger::where('vendor_id', $partyId)
                    ->where('branch_id', $branchId)
                    ->orderBy('id', 'desc')
                    ->first();

                $vendor          = Vendor::find($partyId);
                $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($vendor->opening_balance ?? 0);

                VendorLedger::create([
                    'vendor_id'        => $partyId,
                    'branch_id'        => $branchId,
                    'admin_or_user_id' => $userId,
                    'transaction_date' => $entryDate,
                    'description'      => "Expense Voucher #{$evid}" . ($remarks ? " - {$remarks}" : ""),
                    'opening_balance'  => $previousBalance,
                    'previous_balance' => $previousBalance,
                    'debit_amount'     => 0,
                    'credit_amount'    => $partyAmount,
                    'closing_balance'  => $previousBalance + $partyAmount,
                    'transaction_type' => 'expense',
                    'reference_id'     => $evid,
                ]);

                self::recalculateVendorLedger((int)$partyId, $branchId);
            } elseif ($partyType === 'customer' || $partyType === 'walkin') {
                $lastLedger = CustomerLedger::where('customer_id', $partyId)
                    ->orderBy('id', 'desc')
                    ->first();

                $customer        = Customer::find($partyId);
                $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($customer->opening_balance ?? 0);

                CustomerLedger::create([
                    'customer_id'      => $partyId,
                    'admin_or_user_id' => $userId,
                    'previous_balance' => $previousBalance,
                    'opening_balance'  => $previousBalance,
                    'total_debit'      => 0,
                    'total_credit'     => $partyAmount,
                    'closing_balance'  => $previousBalance + $partyAmount,
                    'transaction_type' => 'expense',
                    'reference_id'     => $evid,
                    'description'      => "Expense Voucher #{$evid}" . ($remarks ? " - {$remarks}" : ""),
                ]);

                self::recalculateCustomerLedger((int)$partyId);
            } else {
                // GL Account (e.g. Cash in Hand / Bank Account)
                $accountId = is_numeric($partyId) ? $partyId : $partyType;
                $account   = Account::find($accountId);
                if ($account) {
                    self::postLedgerEntry(
                        $account->id,
                        'expense',
                        $evid,
                        $voucher->id,
                        $entryDate,
                        "Expense Payment Source: " . ($remarks ?: 'N/A'),
                        0,
                        $partyAmount,
                        $userId
                    );
                    AccountLedgerEntry::recalculateRunningBalance((int)$account->id);
                }
            }
        }

        return true;
    }

    /**
     * Update an existing Expense Voucher cleanly.
     */
    public static function updateExpenseVoucher(ExpenseVoucher $voucher, array $data, $userId): bool
    {
        DB::beginTransaction();
        try {
            // 1. Revert old ledger effects
            self::reverseExpenseVoucher($voucher, $userId, false);

            // 2. Parse party arrays
            $partyTypes   = $data['party_type'] ?? (isset($data['vendor_type']) ? (is_array($data['vendor_type']) ? $data['vendor_type'] : [$data['vendor_type']]) : []);
            $partyIds     = $data['party_id'] ?? (isset($data['vendor_id']) ? (is_array($data['vendor_id']) ? $data['vendor_id'] : [$data['vendor_id']]) : []);
            $partyAmounts = $data['party_amount'] ?? [];
            $partyTels    = $data['party_tel'] ?? (isset($data['tel']) ? (is_array($data['tel']) ? $data['tel'] : [$data['tel']]) : []);

            if (empty($partyAmounts) && count($partyIds) === 1) {
                $partyAmounts = [(float)($data['total_amount'] ?? $voucher->total_amount)];
            }

            $narrationIds = is_array($data['narration_id'] ?? null) 
                ? $data['narration_id'] 
                : self::safeDecodeArray($voucher->narration_id);

            // 3. Update Voucher Record
            $voucher->update([
                'branch_id'        => (int)($data['branch_id'] ?? $voucher->branch_id ?? 1),
                'entry_date'       => $data['entry_date'] ?? $voucher->entry_date,
                'type'             => json_encode($partyTypes),
                'party_id'         => json_encode($partyIds),
                'party_amount'     => json_encode($partyAmounts),
                'tel'              => json_encode($partyTels),
                'remarks'          => $data['remarks'] ?? $voucher->remarks,
                'narration_id'     => json_encode($narrationIds),
                'row_account_head' => json_encode($data['row_account_head'] ?? self::safeDecodeArray($voucher->row_account_head)),
                'row_account_id'   => json_encode($data['row_account_id'] ?? self::safeDecodeArray($voucher->row_account_id)),
                'amount'           => json_encode($data['amount'] ?? self::safeDecodeArray($voucher->amount)),
                'total_amount'     => (float)($data['total_amount'] ?? $voucher->total_amount),
                'status'           => 'active',
            ]);

            // 4. Apply new ledger effects
            self::applyExpenseVoucher($voucher, $data, $userId);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Expense Voucher Update Error: " . $e->getMessage());
            return false;
        }
    }


    /* =========================================================================
     * 4. JOURNAL VOUCHER (JV)
     * ========================================================================= */

    /**
     * Reverse a Journal Voucher completely.
     * Original: Debit side debited (payable reduced / asset increased), Credit side credited (receivable reduced).
     * Reversal: Debit side credited (payable restored), Credit side debited (receivable restored).
     */
    public static function reverseJournalVoucher(JournalVoucher $voucher, $userId, bool $isVoid = true): bool
    {
        DB::beginTransaction();
        try {
            $amount   = (float) $voucher->amount;
            $jvid     = $voucher->jvid;
            $date     = now()->toDateString();
            $branchId = $voucher->branch_id ?? (auth()->check() ? auth()->user()->branch_id : 0);

            // 1. REVERSE DEBIT SIDE (Original DEBITED -> Reversal CREDITS)
            if ($voucher->debit_party_type === 'vendor') {
                $lastLedger = VendorLedger::where('vendor_id', $voucher->debit_party_id)
                    ->where('branch_id', $branchId)
                    ->orderBy('id', 'desc')
                    ->first();

                $vendor          = Vendor::find($voucher->debit_party_id);
                $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($vendor->opening_balance ?? 0);

                VendorLedger::create([
                    'vendor_id'        => $voucher->debit_party_id,
                    'branch_id'        => $branchId,
                    'admin_or_user_id' => $userId,
                    'transaction_date' => $date,
                    'description'      => "Reversal of Journal Voucher #{$jvid} — Debit Side",
                    'opening_balance'  => $vendor->opening_balance ?? 0,
                    'previous_balance' => $previousBalance,
                    'debit_amount'     => 0,
                    'credit_amount'    => $amount, // Credited back
                    'closing_balance'  => $previousBalance + $amount,
                    'transaction_type' => 'reversal',
                    'reference_id'     => $jvid,
                ]);
            } elseif ($voucher->debit_party_type === 'customer') {
                $lastLedger = CustomerLedger::where('customer_id', $voucher->debit_party_id)
                    ->orderBy('id', 'desc')
                    ->first();

                $customer        = Customer::find($voucher->debit_party_id);
                $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($customer->opening_balance ?? 0);

                CustomerLedger::create([
                    'customer_id'      => $voucher->debit_party_id,
                    'admin_or_user_id' => $userId,
                    'previous_balance' => $previousBalance,
                    'opening_balance'  => $customer->opening_balance ?? 0,
                    'total_debit'      => 0,
                    'total_credit'     => $amount, // Credited back
                    'closing_balance'  => $previousBalance - $amount,
                    'transaction_type' => 'reversal',
                    'reference_id'     => $jvid,
                    'description'      => "Reversal of Journal Voucher #{$jvid} — Debit Side",
                ]);
            } elseif ($voucher->debit_party_type === 'account' || is_numeric($voucher->debit_party_type)) {
                $accId   = $voucher->debit_party_id;
                $account = Account::find($accId);
                if ($account) {
                    self::postLedgerEntry(
                        $account->id,
                        'manual',
                        $jvid,
                        $voucher->id,
                        $date,
                        "Reversal of Journal Voucher #{$jvid} — Debit Side",
                        0,
                        $amount,
                        $userId
                    );
                }
            }

            // 2. REVERSE CREDIT SIDE (Original CREDITED -> Reversal DEBITS)
            if ($voucher->credit_party_type === 'customer') {
                $lastLedger = CustomerLedger::where('customer_id', $voucher->credit_party_id)
                    ->orderBy('id', 'desc')
                    ->first();

                $customer        = Customer::find($voucher->credit_party_id);
                $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($customer->opening_balance ?? 0);

                CustomerLedger::create([
                    'customer_id'      => $voucher->credit_party_id,
                    'admin_or_user_id' => $userId,
                    'previous_balance' => $previousBalance,
                    'opening_balance'  => $customer->opening_balance ?? 0,
                    'total_debit'      => $amount, // Debited back (receivable restored)
                    'total_credit'     => 0,
                    'closing_balance'  => $previousBalance + $amount,
                    'transaction_type' => 'reversal',
                    'reference_id'     => $jvid,
                    'description'      => "Reversal of Journal Voucher #{$jvid} — Credit Side",
                ]);
            } elseif ($voucher->credit_party_type === 'vendor') {
                $lastLedger = VendorLedger::where('vendor_id', $voucher->credit_party_id)
                    ->where('branch_id', $branchId)
                    ->orderBy('id', 'desc')
                    ->first();

                $vendor          = Vendor::find($voucher->credit_party_id);
                $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($vendor->opening_balance ?? 0);

                VendorLedger::create([
                    'vendor_id'        => $voucher->credit_party_id,
                    'branch_id'        => $branchId,
                    'admin_or_user_id' => $userId,
                    'transaction_date' => $date,
                    'description'      => "Reversal of Journal Voucher #{$jvid} — Credit Side",
                    'opening_balance'  => $vendor->opening_balance ?? 0,
                    'previous_balance' => $previousBalance,
                    'debit_amount'     => $amount, // Debited back
                    'credit_amount'    => 0,
                    'closing_balance'  => $previousBalance - $amount,
                    'transaction_type' => 'reversal',
                    'reference_id'     => $jvid,
                ]);
            } elseif ($voucher->credit_party_type === 'account' || is_numeric($voucher->credit_party_type)) {
                $accId   = $voucher->credit_party_id;
                $account = Account::find($accId);
                if ($account) {
                    self::postLedgerEntry(
                        $account->id,
                        'manual',
                        $jvid,
                        $voucher->id,
                        $date,
                        "Reversal of Journal Voucher #{$jvid} — Credit Side",
                        $amount,
                        0,
                        $userId
                    );
                }
            }

            // 3. Mark Status
            if ($isVoid) {
                $voucher->status = 'voided';
                $voucher->save();
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            self::$lastError = $e->getMessage();
            Log::error("JV Reversal Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Post ledger entries for a Journal Voucher.
     */
    public static function applyJournalVoucher(JournalVoucher $voucher, array $data, $userId): bool
    {
        $amount          = (float)($data['amount'] ?? $voucher->amount);
        $jvid            = $voucher->jvid;
        $voucherDate     = $data['voucher_date'] ?? $voucher->voucher_date ?? now()->toDateString();
        $branchId        = (int)($data['branch_id'] ?? $voucher->branch_id ?? 0);
        $remarks         = $data['remarks'] ?? $voucher->remarks ?? '';
        $debitPartyType  = $data['debit_party_type'] ?? $voucher->debit_party_type;
        $debitPartyId    = $data['debit_party_id'] ?? $voucher->debit_party_id;
        $creditPartyType = $data['credit_party_type'] ?? $voucher->credit_party_type;
        $creditPartyId   = $data['credit_party_id'] ?? $voucher->credit_party_id;

        // 1. DEBIT SIDE POSTING
        if ($debitPartyType === 'vendor') {
            $lastLedger = VendorLedger::where('vendor_id', $debitPartyId)
                ->where('branch_id', $branchId)
                ->orderBy('id', 'desc')
                ->first();

            $vendor          = Vendor::find($debitPartyId);
            $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($vendor->opening_balance ?? 0);

            VendorLedger::create([
                'vendor_id'        => $debitPartyId,
                'branch_id'        => $branchId,
                'admin_or_user_id' => $userId,
                'transaction_date' => $voucherDate,
                'description'      => "Journal Voucher #{$jvid} — Debit" . ($remarks ? " - {$remarks}" : ""),
                'opening_balance'  => $vendor->opening_balance ?? 0,
                'previous_balance' => $previousBalance,
                'debit_amount'     => $amount,
                'credit_amount'    => 0,
                'closing_balance'  => $previousBalance - $amount,
                'transaction_type' => 'journal',
                'reference_id'     => $jvid,
            ]);
        } elseif ($debitPartyType === 'customer') {
            $lastLedger = CustomerLedger::where('customer_id', $debitPartyId)
                ->orderBy('id', 'desc')
                ->first();

            $customer        = Customer::find($debitPartyId);
            $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($customer->opening_balance ?? 0);

            CustomerLedger::create([
                'customer_id'      => $debitPartyId,
                'admin_or_user_id' => $userId,
                'previous_balance' => $previousBalance,
                'opening_balance'  => $customer->opening_balance ?? 0,
                'total_debit'      => $amount,
                'total_credit'     => 0,
                'closing_balance'  => $previousBalance + $amount,
                'transaction_type' => 'journal',
                'reference_id'     => $jvid,
                'description'      => "Journal Voucher #{$jvid} — Debit" . ($remarks ? " - {$remarks}" : ""),
            ]);
        } elseif ($debitPartyType === 'account' || is_numeric($debitPartyType)) {
            $accId   = $debitPartyId;
            $account = Account::find($accId);
            if ($account) {
                self::postLedgerEntry(
                    $account->id,
                    'manual',
                    $jvid,
                    $voucher->id,
                    $voucherDate,
                    "Journal Voucher #{$jvid} — Debit: " . ($remarks ?: 'N/A'),
                    $amount,
                    0,
                    $userId
                );
            }
        }

        // 2. CREDIT SIDE POSTING
        if ($creditPartyType === 'customer') {
            $lastLedger = CustomerLedger::where('customer_id', $creditPartyId)
                ->orderBy('id', 'desc')
                ->first();

            $customer        = Customer::find($creditPartyId);
            $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($customer->opening_balance ?? 0);

            CustomerLedger::create([
                'customer_id'      => $creditPartyId,
                'admin_or_user_id' => $userId,
                'previous_balance' => $previousBalance,
                'opening_balance'  => $customer->opening_balance ?? 0,
                'total_debit'      => 0,
                'total_credit'     => $amount,
                'closing_balance'  => $previousBalance - $amount,
                'transaction_type' => 'journal',
                'reference_id'     => $jvid,
                'description'      => "Journal Voucher #{$jvid} — Credit" . ($remarks ? " - {$remarks}" : ""),
            ]);
        } elseif ($creditPartyType === 'vendor') {
            $lastLedger = VendorLedger::where('vendor_id', $creditPartyId)
                ->where('branch_id', $branchId)
                ->orderBy('id', 'desc')
                ->first();

            $vendor          = Vendor::find($creditPartyId);
            $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)($vendor->opening_balance ?? 0);

            VendorLedger::create([
                'vendor_id'        => $creditPartyId,
                'branch_id'        => $branchId,
                'admin_or_user_id' => $userId,
                'transaction_date' => $voucherDate,
                'description'      => "Journal Voucher #{$jvid} — Credit" . ($remarks ? " - {$remarks}" : ""),
                'opening_balance'  => $vendor->opening_balance ?? 0,
                'previous_balance' => $previousBalance,
                'debit_amount'     => 0,
                'credit_amount'    => $amount,
                'closing_balance'  => $previousBalance + $amount,
                'transaction_type' => 'journal',
                'reference_id'     => $jvid,
            ]);
        } elseif ($creditPartyType === 'account' || is_numeric($creditPartyType)) {
            $accId   = $creditPartyId;
            $account = Account::find($accId);
            if ($account) {
                self::postLedgerEntry(
                    $account->id,
                    'manual',
                    $jvid,
                    $voucher->id,
                    $voucherDate,
                    "Journal Voucher #{$jvid} — Credit: " . ($remarks ?: 'N/A'),
                    0,
                    $amount,
                    $userId
                );
            }
        }

        return true;
    }


    /* =========================================================================
     * 5. GENERAL LEDGER HELPER (AccountLedgerEntry)
     * ========================================================================= */

    /**
     * Post an entry into account_ledger_entries and maintain cumulative running balance.
     */
    public static function postLedgerEntry(
        int    $accountId,
        string $voucherType,
        string $voucherNo,
        ?int   $voucherId,
        string $date,
        ?string $description,
        float  $debit,
        float  $credit,
        $userId = null
    ): void {
        $account = Account::find($accountId);
        if (!$account) return;

        // Get last running balance for this account
        $lastEntry = AccountLedgerEntry::where('account_id', $accountId)
            ->orderByDesc('id')
            ->first();

        if ($lastEntry) {
            $previousBalance = (float)$lastEntry->running_balance;
        } else {
            // Base balance
            $openingBalance = (float)($account->opening_balance ?? 0);

            if ($openingBalance != 0) {
                $obEntryNo = AccountLedgerEntry::generateEntryNo($accountId, 'opening_balance');
                AccountLedgerEntry::create([
                    'account_id'        => $accountId,
                    'branch_id'         => $account->branch_id ?? 0,
                    'voucher_type'      => 'opening_balance',
                    'voucher_no'        => null,
                    'voucher_id'        => null,
                    'entry_no'          => $obEntryNo,
                    'transaction_date'  => $account->opening_date ? (is_string($account->opening_date) ? substr($account->opening_date, 0, 10) : $account->opening_date->format('Y-m-d')) : ($account->created_at ? $account->created_at->format('Y-m-d') : now()->toDateString()),
                    'description'       => 'Opening Balance',
                    'debit'             => $openingBalance >= 0 ? $openingBalance : 0,
                    'credit'            => $openingBalance < 0 ? abs($openingBalance) : 0,
                    'running_balance'   => $openingBalance,
                    'created_by'        => $userId ?? auth()->id(),
                ]);
            }

            $previousBalance = $openingBalance;
        }

        // Account normal balance type calculation
        if (trim(strtolower($account->type ?? 'debit')) === 'credit') {
            $newBalance = $previousBalance + $credit - $debit;
        } else {
            $newBalance = $previousBalance + $debit - $credit;
        }

        $validVoucherTypes = ['opening_balance', 'receipt', 'payment', 'expense', 'manual'];
        $cleanVoucherType = in_array($voucherType, $validVoucherTypes) ? $voucherType : 'manual';

        // ✅ IDEMPOTENCY CHECK: If entry for this voucher already exists on this account, update it!
        if ($voucherId) {
            $existing = AccountLedgerEntry::where('account_id', $accountId)
                ->where('voucher_type', $cleanVoucherType)
                ->where('voucher_id', $voucherId)
                ->first();

            if ($existing) {
                $existing->update([
                    'voucher_no'       => $voucherNo,
                    'transaction_date' => $date,
                    'description'      => $description ?? ucfirst($cleanVoucherType) . ' Voucher #' . $voucherNo,
                    'debit'            => $debit,
                    'credit'           => $credit,
                ]);
                AccountLedgerEntry::recalculateRunningBalance($accountId);
                return;
            }
        }

        $entryNo = AccountLedgerEntry::generateEntryNo($accountId, $cleanVoucherType);

        AccountLedgerEntry::create([
            'account_id'        => $accountId,
            'branch_id'         => $account->branch_id ?? 0,
            'voucher_type'      => $cleanVoucherType,
            'voucher_no'        => $voucherNo,
            'voucher_id'        => $voucherId,
            'entry_no'          => $entryNo,
            'transaction_date'  => $date,
            'description'       => $description ?? ucfirst($cleanVoucherType) . ' Voucher #' . $voucherNo,
            'debit'             => $debit,
            'credit'            => $credit,
            'running_balance'   => $newBalance,
            'created_by'        => $userId ?? auth()->id(),
        ]);

        AccountLedgerEntry::recalculateRunningBalance($accountId);
    }

    /**
     * Safely decode database column into an array.
     * Handles:
     * - Already an array -> returns array_values
     * - JSON encoded array string -> returns decoded array
     * - JSON encoded scalar (e.g. "4", "500") -> returns [4], [500]
     * - Non-JSON string (e.g. "Cash/Bank", "INV-001") -> returns ["Cash/Bank"], ["INV-001"]
     * - Numeric/scalar value -> returns [value]
     * - Null / empty -> returns []
     */
    public static function safeDecodeArray($value): array
    {
        if (is_array($value)) {
            return array_values($value);
        }
        if ($value === null || $value === '') {
            return [];
        }
        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '' || $trimmed === 'null') {
                return [];
            }
            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                if (is_array($decoded)) {
                    return array_values($decoded);
                }
                if ($decoded !== null && $decoded !== '') {
                    return [$decoded];
                }
                return [];
            }
            // If not valid JSON, treat as raw single value string (e.g. "Cash/Bank" or "INV-0005")
            return [$trimmed];
        }
        // Numbers, etc.
        return [$value];
    }

    /**
     * Get the specific amount allocated to a party from a voucher.
     * Supports both multi-party JSON arrays and legacy single-party scalars.
     */
    public static function getPartyAmount($voucher, $partyId, ?string $expectedType = null): float
    {
        $vObj = is_array($voucher) ? (object)$voucher : $voucher;

        $partyTypes   = self::safeDecodeArray($vObj->type ?? null);
        $partyIds     = self::safeDecodeArray($vObj->party_id ?? null);
        $partyAmounts = self::safeDecodeArray($vObj->party_amount ?? null);

        // If single scalar legacy voucher
        if (empty($partyIds) && !empty($vObj->party_id)) {
            if ((string)$vObj->party_id === (string)$partyId) {
                if ($expectedType) {
                    $pType = (string)($vObj->type ?? '');
                    if ($pType && $pType !== $expectedType && !($expectedType === 'customer' && in_array($pType, ['customer', 'walkin', 'SALE_RECEIPT']))) {
                        return 0.0;
                    }
                }
                return (float)($vObj->total_amount ?? $vObj->debit_amount ?? $vObj->credit_amount ?? 0);
            }
            return 0.0;
        }

        $total = 0.0;
        foreach ($partyIds as $idx => $pId) {
            if ((string)$pId === (string)$partyId) {
                if ($expectedType) {
                    $pType = $partyTypes[$idx] ?? ($partyTypes[0] ?? null);
                    if ($pType && $pType !== $expectedType && !($expectedType === 'customer' && in_array($pType, ['customer', 'walkin', 'SALE_RECEIPT']))) {
                        continue;
                    }
                }

                $amt = isset($partyAmounts[$idx]) && is_numeric($partyAmounts[$idx])
                    ? (float)$partyAmounts[$idx]
                    : (count($partyIds) === 1 ? (float)($vObj->total_amount ?? $vObj->debit_amount ?? $vObj->credit_amount ?? 0) : 0.0);
                $total += $amt;
            }
        }

        return (float)$total;
    }

    /**
     * Scope a database query to match a specific party in single or multi-party vouchers.
     */
    public static function scopePartyQuery($query, $partyId, string $column = 'party_id')
    {
        return $query->where(function ($q) use ($partyId, $column) {
            $q->where($column, (string)$partyId)
              ->orWhere($column, $partyId)
              ->orWhere($column, 'LIKE', '%"' . $partyId . '"%')
              ->orWhere($column, 'LIKE', '%[' . $partyId . ',%')
              ->orWhere($column, 'LIKE', '%, ' . $partyId . ',%')
              ->orWhere($column, 'LIKE', '%, ' . $partyId . ']%')
              ->orWhere($column, 'LIKE', '%[' . $partyId . ']%');
        });
    }

    /**
     * Recalculate customer running balance from start to finish in strict chronological order
     */
    public static function recalculateCustomerLedger(int $customerId): void
    {
        $customer = Customer::find($customerId);
        if (!$customer) return;

        $entries = CustomerLedger::where('customer_id', $customerId)
            ->orderByRaw("COALESCE(created_at, '1970-01-01') ASC")
            ->orderBy('id', 'asc')
            ->get();
        $running = (float)($customer->opening_balance ?? 0);

        foreach ($entries as $entry) {
            $entry->previous_balance = $running;
            $running = $running + (float)$entry->total_debit - (float)$entry->total_credit;
            $entry->closing_balance = $running;
            $entry->save();
        }
    }

    /**
     * Recalculate vendor running balance from start to finish in strict chronological order
     */
    public static function recalculateVendorLedger(int $vendorId, int $branchId = 0): void
    {
        $vendor = Vendor::find($vendorId);
        if (!$vendor) return;

        $query = VendorLedger::where('vendor_id', $vendorId)
            ->orderByRaw("COALESCE(transaction_date, DATE(created_at), '1970-01-01') ASC")
            ->orderBy('id', 'asc');
        if ($branchId > 0) {
            $query->where('branch_id', $branchId);
        }
        $entries = $query->get();
        $running = (float)($vendor->opening_balance ?? 0);

        foreach ($entries as $entry) {
            $entry->previous_balance = $running;
            $running = $running - (float)$entry->debit_amount + (float)$entry->credit_amount;
            $entry->closing_balance = $running;
            $entry->save();
        }
    }
}


