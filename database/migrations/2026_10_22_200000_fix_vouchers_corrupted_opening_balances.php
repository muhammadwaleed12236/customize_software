<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Account;
use App\Models\AccountLedgerEntry;
use App\Models\PaymentVoucher;
use App\Models\Purchase;
use App\Services\VoucherService;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fixes accounts whose opening_balance was artificially inflated by old VoucherService bug,
     * and syncs missing warehouse_stocks for local purchases edited with Shop stock (warehouse_id 0).
     */
    public function up(): void
    {
        // 1. Fix Accounts opening_balance
        $accounts = Account::all();

        foreach ($accounts as $acc) {
            $pvs = PaymentVoucher::where('status', '!=', 'voided')->get();
            $pvPartySum = 0.0;

            foreach ($pvs as $pv) {
                $pIds = VoucherService::safeDecodeArray($pv->party_id);
                $pAmts = VoucherService::safeDecodeArray($pv->party_amount);
                $pTypes = VoucherService::safeDecodeArray($pv->type);
                if (empty($pIds) && !empty($pv->party_id)) {
                    $pIds = [$pv->party_id];
                    $pAmts = [(float)$pv->total_amount];
                    $pTypes = [$pv->type];
                }

                foreach ($pIds as $idx => $pId) {
                    if ((int)$pId === (int)$acc->id) {
                        $pType = strtolower(trim((string)($pTypes[$idx] ?? ($pTypes[0] ?? ''))));
                        if (!in_array($pType, ['vendor', 'customer', 'walkin', 'sale_receipt'])) {
                            $amt = isset($pAmts[$idx]) && is_numeric($pAmts[$idx])
                                ? (float)$pAmts[$idx]
                                : (count($pIds) === 1 ? (float)$pv->total_amount : 0.0);
                            $pvPartySum += $amt;
                        }
                    }
                }
            }

            if ($pvPartySum > 0 && abs((float)$acc->opening_balance - $pvPartySum) < 0.01) {
                $acc->opening_balance = 0.00;
                $acc->save();

                $ob = AccountLedgerEntry::where('account_id', $acc->id)
                    ->where('voucher_type', 'opening_balance')
                    ->first();
                if ($ob) {
                    $ob->debit = 0.00;
                    $ob->credit = 0.00;
                    $ob->running_balance = 0.00;
                    $ob->save();
                }

                AccountLedgerEntry::recalculateRunningBalance((int)$acc->id);
            }
        }

        // 2. Sync missing stock for local purchases (e.g. Purchase 70 Product 317)
        $purchases = Purchase::where('purchase_type', 'local')
            ->whereNull('deleted_at')
            ->with('items')
            ->get();

        $now = now();
        foreach ($purchases as $pur) {
            foreach ($pur->items as $item) {
                $whId = (int)($item->warehouse_id ?: $pur->warehouse_id);
                $pId = (int)$item->product_id;
                $bId = (int)$pur->branch_id;
                $qty = (float)$item->qty;

                $wsQuery = DB::table('warehouse_stocks')
                    ->where('product_id', $pId)
                    ->where('branch_id', $bId);
                if ($whId > 0) {
                    $wsQuery->where('warehouse_id', $whId);
                } else {
                    $wsQuery->whereNull('warehouse_id');
                }
                $ws = $wsQuery->first();

                if (!$ws) {
                    DB::table('warehouse_stocks')->insert([
                        'branch_id'    => $bId,
                        'warehouse_id' => $whId > 0 ? $whId : null,
                        'product_id'   => $pId,
                        'quantity'     => $qty,
                        'price'        => null,
                        'remarks'      => 'Direct purchase stock sync',
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ]);

                    $stocksRow = DB::table('stocks')
                        ->where('product_id', $pId)
                        ->where('branch_id', $bId)
                        ->first();
                    if (!$stocksRow) {
                        DB::table('stocks')->insert([
                            'product_id'   => $pId,
                            'branch_id'    => $bId,
                            'qty'          => $qty,
                            'created_at'   => $now,
                            'updated_at'   => $now,
                        ]);
                    } else {
                        DB::table('stocks')
                            ->where('id', $stocksRow->id)
                            ->increment('qty', $qty);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Irreversible data correction
    }
};
