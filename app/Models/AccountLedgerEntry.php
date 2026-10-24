<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountLedgerEntry extends Model
{
    use HasFactory;

    protected $table = 'account_ledger_entries';

    protected $fillable = [
        'account_id',
        'branch_id',
        'voucher_type',
        'voucher_no',
        'voucher_id',
        'entry_no',
        'transaction_date',
        'description',
        'debit',
        'credit',
        'running_balance',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'debit'           => 'float',
        'credit'          => 'float',
        'running_balance' => 'float',
    ];

    // Relations
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate next entry_no for an account.
     * Series logic:
     *   - Account head name contains "Bank"  → BR-1, BR-2 ...
     *   - Account head name contains "Cash"  → CR-1, CR-2 ...
     *   - Opening balance entry              → OB-1
     *   - Everything else                   → JV-1, JV-2 ...
     */
    public static function generateEntryNo(int $accountId, string $voucherType = 'manual', float $credit = 0): string
    {
        // Get the account with its head
        $account = Account::with('head')->find($accountId);
        $headName = strtolower($account?->head?->name ?? '');

        $titleName = strtolower($account?->title ?? '');
        $isBank = str_contains($headName, 'bank') || str_contains($titleName, 'bank') || str_contains($titleName, 'jazz cash') || str_contains($titleName, 'easypaisa');
        $isCash = str_contains($headName, 'cash') || str_contains($titleName, 'cash');

        // Determine prefix based on transaction nature and account
        if ($voucherType === 'opening_balance') {
            $prefix = 'OB';
        } elseif ($voucherType === 'expense') {
            $prefix = 'EV';
        } elseif ($isBank) {
            $prefix = ($credit > 0 || $voucherType === 'payment') ? 'BP' : 'BR';
        } elseif ($isCash) {
            $prefix = ($credit > 0 || $voucherType === 'payment') ? 'CP' : 'CR';
        } else {
            $prefix = ($credit > 0 || $voucherType === 'payment') ? 'PV' : 'RV';
        }


        // Get max sequential number for this account with this prefix
        $entries = self::where('account_id', $accountId)
            ->where('entry_no', 'like', $prefix . '-%')
            ->pluck('entry_no');

        $maxNum = 0;
        foreach ($entries as $eNo) {
            $parts = explode('-', $eNo);
            $num = (int) end($parts);
            if ($num > $maxNum) {
                $maxNum = $num;
            }
        }

        return $prefix . '-' . ($maxNum + 1);
    }

    /**
     * Get the final closing ledger entry for an account in strict chronological order.
     * Matches the bottom-most row displayed in the Account Ledger.
     */
    public static function getClosingEntry(int $accountId): ?self
    {
        // Pick the latest non-opening entry in chronological order;
        // fallback to opening balance entry if no other entries exist.
        $lastNonOb = self::where('account_id', $accountId)
            ->where('voucher_type', '!=', 'opening_balance')
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastNonOb) {
            return $lastNonOb;
        }

        return self::where('account_id', $accountId)
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * Get the final closing balance for an account from its ledger in date order.
     * Guaranteed mathematical accuracy: Opening Balance + Total Debit - Total Credit (or reverse for Credit accounts).
     */
    public static function getClosingBalance(int $accountId): float
    {
        $account = Account::find($accountId);
        if (!$account) return 0.0;

        $isCredit = trim(strtolower($account->type ?? 'debit')) === 'credit';
        $ob = (float)($account->opening_balance ?? 0);

        // Sum of all actual transactions (excluding OB row to avoid double counting)
        $totals = self::where('account_id', $accountId)
            ->where('voucher_type', '!=', 'opening_balance')
            ->selectRaw('COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(credit), 0) as total_credit')
            ->first();

        $totDebit = (float)($totals->total_debit ?? 0);
        $totCredit = (float)($totals->total_credit ?? 0);

        return $isCredit
            ? ($ob + $totCredit - $totDebit)
            : ($ob + $totDebit - $totCredit);
    }

    /**
     * Recalculates the cumulative running balance and re-sequences entry numbers
     * for all ledger entries of an account in strict chronological (date-wise) order.
     */
    public static function recalculateRunningBalance(int $accountId): void
    {
        $account = Account::find($accountId);
        if (!$account) {
            return;
        }

        $isCredit = trim(strtolower($account->type ?? 'debit')) === 'credit';
        $headName = strtolower($account->head?->name ?? '');
        $titleName = strtolower($account->title ?? '');
        $isBank = str_contains($headName, 'bank') || str_contains($titleName, 'bank') || str_contains($titleName, 'jazz cash') || str_contains($titleName, 'easypaisa');
        $isCash = str_contains($headName, 'cash') || str_contains($titleName, 'cash');
        $prefix = $isBank ? 'BR' : ($isCash ? 'CR' : 'JV');

        $entries = self::where('account_id', $accountId)
            ->orderByRaw("CASE WHEN voucher_type = 'opening_balance' THEN 0 ELSE 1 END ASC")
            ->orderBy('transaction_date', 'asc')
            ->orderByRaw("CASE WHEN debit > 0 THEN 0 ELSE 1 END ASC")
            ->orderByRaw("CASE WHEN voucher_no IS NOT NULL AND voucher_no != '' THEN 0 ELSE 1 END ASC")
            ->orderBy('voucher_no', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        if ($entries->isEmpty()) {
            return;
        }

        $running = 0.0;
        $startIndex = 0;

        $firstEntry = $entries->first();
        if ($firstEntry && $firstEntry->voucher_type === 'opening_balance') {
            // Opening balance entry must always precede or match the earliest transaction
            $earliestNonOb = $entries->where('voucher_type', '!=', 'opening_balance')->first();
            if ($account->opening_date) {
                $manualDate = is_string($account->opening_date)
                    ? explode(' ', $account->opening_date)[0]
                    : $account->opening_date->format('Y-m-d');
                if ($earliestNonOb && $manualDate > $earliestNonOb->transaction_date) {
                    $firstEntry->transaction_date = $earliestNonOb->transaction_date;
                } else {
                    $firstEntry->transaction_date = $manualDate;
                }
            } else {
                if ($earliestNonOb && $firstEntry->transaction_date > $earliestNonOb->transaction_date) {
                    $firstEntry->transaction_date = $earliestNonOb->transaction_date;
                }
            }

            if ($firstEntry->entry_no !== 'OB-1') {
                $firstEntry->entry_no = 'OB-1';
            }

            // Ensure opening balance entry matches account opening balance
            $expectedOpening = abs((float)($account->opening_balance ?? 0));
            if ($isCredit) {
                $firstEntry->debit = 0;
                $firstEntry->credit = abs($expectedOpening);
            } else {
                $firstEntry->debit = abs($expectedOpening);
                $firstEntry->credit = 0;
            }
            $firstEntry->running_balance = $expectedOpening;
            if ($firstEntry->isDirty()) {
                $firstEntry->save();
            }

            $running = $expectedOpening;
            $startIndex = 1;
        } else {
            $running = abs((float)($account->opening_balance ?? 0));
            $startIndex = 0;
        }

        $seq = 1;
        foreach ($entries as $index => $entry) {
            if ($index < $startIndex) {
                if ($entry->isDirty()) {
                    $entry->save();
                }
                continue;
            }

            // Re-sequence entry_no in date order
            $vType = strtolower($entry->voucher_type ?? '');
            $vNo = (string)($entry->voucher_no ?? '');
            $isExpense = ($vType === 'expense' || str_starts_with($vNo, 'EVID') || str_starts_with($vNo, 'EV'));
            $isPayment = ($vType === 'payment' || str_starts_with($vNo, 'PVID') || str_starts_with($vNo, 'PV') || (float)$entry->credit > 0);

            if ($vType === 'opening_balance') {
                $p = 'OB';
            } elseif ($isExpense) {
                $p = 'EV';
            } elseif ($isBank) {
                $p = $isPayment ? 'BP' : 'BR';
            } elseif ($isCash) {
                $p = $isPayment ? 'CP' : 'CR';
            } else {
                $p = $isPayment ? 'PV' : 'RV';
            }

            $expectedEntryNo = $p . '-' . $seq;
            if ($entry->entry_no !== $expectedEntryNo) {
                $entry->entry_no = $expectedEntryNo;
            }
            $seq++;

            $debit = (float)$entry->debit;
            $credit = (float)$entry->credit;

            if ($isCredit) {
                $running = $running + $credit - $debit;
            } else {
                $running = $running + $debit - $credit;
            }

            if (abs($entry->running_balance - $running) > 0.0001) {
                $entry->running_balance = $running;
            }

            if ($entry->isDirty()) {
                $entry->save();
            }
        }
    }
}
