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
    public static function generateEntryNo(int $accountId, string $voucherType = 'manual'): string
    {
        // Get the account with its head
        $account = Account::with('head')->find($accountId);
        $headName = strtolower($account?->head?->name ?? '');

        // Determine prefix
        if ($voucherType === 'opening_balance') {
            $prefix = 'OB';
        } elseif (str_contains($headName, 'bank')) {
            $prefix = 'BR';
        } elseif (str_contains($headName, 'cash')) {
            $prefix = 'CR';
        } else {
            $prefix = 'JV';
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
        $prefix = str_contains($headName, 'bank') ? 'BR' : (str_contains($headName, 'cash') ? 'CR' : 'JV');

        $entries = self::where('account_id', $accountId)
            ->orderByRaw("CASE WHEN voucher_type = 'opening_balance' THEN 0 ELSE 1 END")
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        if ($entries->isEmpty()) {
            return;
        }

        $running = 0.0;
        $startIndex = 0;

        $firstEntry = $entries->first();
        if ($firstEntry && $firstEntry->voucher_type === 'opening_balance') {
            // Keep opening balance entry date at or before earliest non-OB entry
            $earliestNonOb = $entries->where('voucher_type', '!=', 'opening_balance')->first();
            if ($earliestNonOb && $firstEntry->transaction_date > $earliestNonOb->transaction_date) {
                $firstEntry->transaction_date = $earliestNonOb->transaction_date;
            }
            if ($firstEntry->entry_no !== 'OB-1') {
                $firstEntry->entry_no = 'OB-1';
            }
            $running = (float)$firstEntry->running_balance;
            $startIndex = 1;
        } else {
            $running = (float)($account->opening_balance ?? 0);
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
            $expectedEntryNo = $prefix . '-' . $seq;
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
