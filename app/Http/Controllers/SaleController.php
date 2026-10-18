<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\Product;
use App\Models\ProductBooking;
use App\Models\ProductBookingItem;
use App\Models\ReceiptsVoucher;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePosting;
use App\Models\SalesReturn;
use App\Models\Stock;
use App\Models\AccountHead;
use App\Models\Vendor;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\StockMovement;
use App\Models\Notification;
use App\Services\StockAlertService;
use Illuminate\Support\Facades\Auth;
use App\Models\Branch;
use App\Models\SalesOfficer;
use App\Models\SaleSetting;
use App\Models\AccountLedgerEntry;
use App\Models\BranchTransaction;
use App\Models\WarehouseOrder;
use App\Models\WarehouseOrderItem;
use App\Models\CustomerRemaining;


class SaleController extends Controller
{



    public function ajaxPost(Request $request)
    {
        // return response()->json(['request'=>$request]);
        try {
            return DB::transaction(function () use ($request) {

                /* ================= VALIDATION & DATA INITIALIZATION ================= */
                // ✅ Support user-selected custom sale date (past or today)
                $selectedDate = $request->input('sale_date') ?? $request->input('Invoice_date') ?? null;
                $entryDateTime = Carbon::now();
                if (!empty($selectedDate)) {
                    try {
                        $parsedDate = Carbon::parse($selectedDate);
                        $entryDateTime = $parsedDate->setTime(now()->hour, now()->minute, now()->second);
                    } catch (\Exception $e) {
                        $entryDateTime = Carbon::now();
                    }
                }

                $booking = null;
                $items = [];

                if ($request->filled('booking_id')) {
                    /* ================= PATH A: POST AN EXISTING BOOKING ================= */
                    $booking = ProductBooking::with('items')
                        ->lockForUpdate()
                        ->findOrFail($request->booking_id);

                    if ($booking->is_posted) {
                        abort(422, 'Invoice already posted');
                    }

                    $branchId = (int) ($booking->branch_id ?? 1);
                    $partyType = $booking->party_type ?? 'walking';
                    $customerId = $booking->customer_id;
                    $subCustomer = ($partyType === 'walking') ? ($booking->customer_name ?? null) : null;
                    $manualInvoice = $request->input('manual_invoice', $request->input('Invoice_main', $booking->manual_invoice));
                    $salesmanId = $booking->salesman_id ?? $request->salesman_id ?? null;
                    $address = $booking->address;
                    $tel = $booking->tel;
                    $remarks = $request->input('remarks', $request->input('reference', $booking->remarks));
                    $subTotal1 = (float) ($booking->sub_total1 ?? 0);
                    $subTotal2 = (float) ($booking->sub_total2 ?? 0);
                    $discountPercent = (float) ($booking->discount_percent ?? 0);
                    $discountAmount = (float) ($booking->discount_amount ?? 0);
                    $additionalDiscount = (float) ($booking->additional_discount ?? 0);
                    $extraCharges = (float) ($booking->extra_charges ?? 0);
                    $previousBalance = (float) ($booking->previous_balance ?? 0);
                    $totalBalance = (float) ($booking->total_balance ?? 0);

                    $warehouseInputs = $request->warehouse_id;
                    if (!is_array($warehouseInputs)) {
                        $warehouseInputs = [];
                        if (!empty($request->warehouse_id)) {
                            foreach ($booking->items as $item) {
                                $warehouseInputs[$item->product_id] = $request->warehouse_id;
                            }
                        } else {
                            foreach ($booking->items as $item) {
                                $warehouseInputs[$item->product_id] = $item->warehouse_id ?? auth()->user()->warehouse_id ?? 1;
                            }
                        }
                    }

                    foreach ($booking->items as $item) {
                        $rawWid = $warehouseInputs[$item->product_id] ?? $item->warehouse_id;
                        $wid = null;
                        if ($rawWid) {
                            if (is_string($rawWid) && str_starts_with($rawWid, 'branch_')) {
                                $wid = null;
                            } elseif (is_string($rawWid) && ($rawWid === 'branch' || $rawWid === 'shop')) {
                                $wid = null;
                            } elseif (is_string($rawWid) && str_starts_with($rawWid, 'warehouse_')) {
                                $wid = (int) str_replace('warehouse_', '', $rawWid);
                            } elseif (is_numeric($rawWid) && (int) $rawWid > 0) {
                                $wid = (int) $rawWid;
                            }
                        } else {
                            $wid = $item->warehouse_id;
                        }

                        $item->warehouse_id = $wid;
                        $item->save();

                        $items[] = (object) [
                            'product_id'       => $item->product_id,
                            'watt'             => (float) ($item->watt ?? 0),
                            'sales_qty'        => (float) $item->sales_qty,
                            'retail_price'     => (float) $item->retail_price,
                            'discount_percent' => (float) ($item->discount_percent ?? 0),
                            'discount_amount'  => (float) ($item->discount_amount ?? 0),
                            'amount'           => (float) $item->amount,
                            'warehouse_id'     => $wid,
                        ];
                    }
                } else {
                    /* ================= PATH B: DIRECT SALE (NO BOOKING CREATED) ================= */
                    $branchId = 1;
                    if (Auth::check()) {
                        $user = Auth::user();
                        if ($user->hasRole('super admin')) {
                            $branchId = (int) ($request->input('branch_id') ?? $user->branch_id ?? 1);
                        } else {
                            $branchId = (int) ($user->branch_id ?? 1);
                        }
                    } else {
                        $branchId = (int) ($request->input('branch_id') ?? 1);
                    }

                    $partyType = $request->input('partyType') ?? 'walking';
                    if ($partyType === 'walking') {
                        $customerId = null;
                        $subCustomer = $request->input('walkin_name') ?? $request->input('customer_display') ?? $request->input('customer') ?? 'Walk-in Customer';
                    } else {
                        $customerId = $request->input('customer_id') ?? $request->input('customer') ?? null;
                        $subCustomer = null;
                    }

                    $manualInvoice = $request->input('manual_invoice', $request->input('Invoice_main'));
                    $salesmanId = $request->input('salesman_id') ?? null;
                    $address = $request->input('address');
                    $tel = $request->input('tel');
                    $remarks = $request->input('remarks', $request->input('reference'));
                    $subTotal1 = (float) ($request->input('subTotal1') ?? 0);
                    $subTotal2 = (float) ($request->input('subTotal2') ?? 0);
                    $discountPercent = (float) ($request->input('discountPercent') ?? 0);
                    $discountAmount = (float) ($request->input('discountAmount') ?? 0);
                    $additionalDiscount = (float) ($request->input('additional_discount') ?? 0);
                    $extraCharges = (float) ($request->input('extra_charges') ?? 0);
                    $previousBalance = (float) ($request->input('previousBalance') ?? 0);
                    $totalBalance = (float) ($request->input('totalBalance') ?? 0);

                    // Parse items from input arrays
                    $productIds = $request->input('product_id') ?? [];
                    $salesQtys = $request->input('sales_qty') ?? [];
                    $retailPrices = $request->input('retail_price') ?? [];
                    $discPercents = $request->input('discount_percentage') ?? [];
                    $discAmounts = $request->input('discount_amount') ?? [];
                    $salesAmounts = $request->input('sales_amount') ?? [];
                    $watts = $request->input('watt') ?? [];
                    $whInputs = $request->input('warehouse_id') ?? [];
                    $globalWh = $request->input('global_warehouse_id');

                    foreach ($productIds as $i => $pId) {
                        $qty = (float) ($salesQtys[$i] ?? 0);
                        if (!$pId || $qty <= 0) continue;

                        $rawWid = null;
                        if (is_array($whInputs)) {
                            $rawWid = $whInputs[$i] ?? $whInputs[$pId] ?? null;
                        } else {
                            $rawWid = $whInputs;
                        }
                        if (!$rawWid) {
                            $rawWid = $globalWh;
                        }

                        $wid = null;
                        if ($rawWid) {
                            if (is_string($rawWid) && str_starts_with($rawWid, 'warehouse_')) {
                                $wid = (int) str_replace('warehouse_', '', $rawWid);
                            } elseif (is_string($rawWid) && (str_starts_with($rawWid, 'branch_') || $rawWid === 'branch' || $rawWid === 'shop')) {
                                $wid = null;
                            } elseif (is_numeric($rawWid) && (int) $rawWid > 0) {
                                $wid = (int) $rawWid;
                            }
                        }

                        $items[] = (object) [
                            'product_id'       => (int) $pId,
                            'watt'             => (float) (is_array($watts) ? ($watts[$i] ?? 0) : 0),
                            'sales_qty'        => $qty,
                            'retail_price'     => (float) ($retailPrices[$i] ?? 0),
                            'discount_percent' => (float) ($discPercents[$i] ?? 0),
                            'discount_amount'  => (float) ($discAmounts[$i] ?? 0),
                            'amount'           => (float) ($salesAmounts[$i] ?? 0),
                            'warehouse_id'     => $wid,
                        ];
                    }

                    if (empty($items)) {
                        abort(422, 'Please add at least one product with quantity > 0.');
                    }
                }

                /* ================= CREATE SALE ================= */
                $invoiceNo = null;
                try {
                    if ($branchId) {
                        $branch = Branch::lockForUpdate()->find($branchId);
                        if ($branch) {
                            $branch->invoice_counter = ((int) ($branch->invoice_counter ?? 0)) + 1;
                            $branch->save();
                            $invoiceNo = 'INV-' . str_pad($branch->invoice_counter, 4, '0', STR_PAD_LEFT);
                            Log::info('Generated sale invoice for branch', ['branch_id' => $branchId, 'invoice_no' => $invoiceNo, 'counter' => $branch->invoice_counter]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to generate branch invoice counter', ['branch_id' => $branchId, 'error' => $e->getMessage()]);
                }
                
                // Fallback if counter not available
                if (!$invoiceNo) {
                    $maxSaleId = Sale::where('branch_id', $branchId)->max('id') ?? 0;
                    $invoiceNo = 'INV-' . str_pad($maxSaleId + 1, 4, '0', STR_PAD_LEFT);
                    Log::warning('Using fallback sale invoice (counter not available)', ['invoice_no' => $invoiceNo]);
                }

                $saleData = [
                    'invoice_no'          => $invoiceNo,
                    'manual_invoice'      => $manualInvoice,
                    'customer_id'         => $customerId,
                    'salesman_id'         => $salesmanId,
                    'sub_customer'        => $subCustomer,
                    'party_type'          => $partyType,
                    'address'             => $address,
                    'tel'                 => $tel,
                    'remarks'             => $remarks,
                    'sub_total1'          => $subTotal1,
                    'sub_total2'          => $subTotal2,
                    'discount_percent'    => $discountPercent,
                    'discount_amount'     => $discountAmount,
                    'additional_discount' => $additionalDiscount,
                    'extra_charges'       => $extraCharges,
                    'previous_balance'    => $previousBalance,
                    'total_balance'       => $totalBalance,
                    'total_net'           => $subTotal2,
                    'branch_id'           => $branchId,
                    'booking_id'          => $booking ? $booking->id : null,
                    'created_at'          => $entryDateTime,
                    'updated_at'          => $entryDateTime,
                ];

                $sale = Sale::create($saleData);

                /* ================= CUSTOMER LEDGER (ONLY FOR CREDIT CUSTOMERS) ================= */
                if ($partyType === 'credit' && $customerId) {
                    $lastLedger = CustomerLedger::where('customer_id', $customerId)
                        ->latest('id')
                        ->lockForUpdate()
                        ->first();

                    $customer = Customer::find($customerId);
                    $customerOpeningBalance = $customer->opening_balance ?? 0;
                    $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : (float)$customerOpeningBalance;
                    $saleAmount = $subTotal2 - $additionalDiscount + $extraCharges;

                    $totalReceipts = 0;
                    if (!empty($request->receipt_amount) && is_array($request->receipt_amount)) {
                        foreach ($request->receipt_amount as $amt) {
                            $amt = (float) $amt;
                            if ($amt > 0) $totalReceipts += $amt;
                        }
                    }

                    $expectedClosing = $previousBalance + $saleAmount - $totalReceipts;
                    $closingBalance = ($totalBalance > 0 && abs($totalBalance - $expectedClosing) < 1) ? (float)$totalBalance : $expectedClosing;

                    // Sync accurate previous_balance and total_balance on the sale model
                    $sale->previous_balance = $previousBalance;
                    $sale->total_balance    = $closingBalance;
                    $sale->save();

                    Log::info('Creating customer ledger entry for credit sale', [
                        'invoice' => $sale->invoice_no,
                        'customer_id' => $customerId,
                        'previous_balance' => $previousBalance,
                        'opening_balance' => $customerOpeningBalance,
                        'new_sale_amount' => $saleAmount,
                        'receipts_paid' => $totalReceipts,
                        'closing_balance' => $closingBalance,
                    ]);

                    CustomerLedger::create([
                        'customer_id'        => $customerId,
                        'admin_or_user_id'   => auth()->id(),
                        'transaction_type'   => 'Sale',
                        'reference_id'       => (string) $sale->id,
                        'description'        => 'Sale Invoice #' . $sale->invoice_no,
                        'opening_balance'    => $customerOpeningBalance,
                        'previous_balance'   => $previousBalance,
                        'total_debit'        => $saleAmount,
                        'total_credit'       => $totalReceipts,
                        'closing_balance'    => $closingBalance,
                        'created_at'         => $entryDateTime,
                        'updated_at'         => $entryDateTime,
                    ]);
                }

                /* ================= SALE ITEMS & STOCK DEDUCTION ================= */
                foreach ($items as $it) {
                    $wid = $it->warehouse_id;
                    $itemBranchId = $branchId;

                    // Deduct from warehouse_stocks (unified inventory table)
                    $warehousestock = WarehouseStock::lockForUpdate()
                        ->where('product_id', $it->product_id)
                        ->where('branch_id', $itemBranchId)
                        ->when(is_null($wid), function ($q) {
                            $q->whereNull('warehouse_id');
                        }, function ($q) use ($wid) {
                            $q->where('warehouse_id', $wid);
                        })
                        ->first();

                    if (!$warehousestock) {
                        Log::info('Stock record not found, creating initial 0 stock row', [
                            'product_id' => $it->product_id,
                            'branch_id' => $itemBranchId,
                            'warehouse_id' => $wid ?? 'NULL (branch stock)',
                        ]);
                        $warehousestock = WarehouseStock::create([
                            'product_id'   => $it->product_id,
                            'branch_id'    => $itemBranchId,
                            'warehouse_id' => $wid,
                            'quantity'     => 0,
                        ]);
                    }

                    $currentWhQty = $warehousestock->quantity ?? 0;
                    $newWhQty = $currentWhQty - $it->sales_qty;

                    $warehousestock->quantity = $newWhQty;
                    $warehousestock->save();

                    Log::info('Deducted from warehouse_stocks', [
                        'product_id' => $it->product_id,
                        'branch_id' => $itemBranchId,
                        'warehouse_id' => $wid ?? 'NULL (branch stock)',
                        'qty_before' => $currentWhQty,
                        'qty_after' => $newWhQty,
                    ]);

                    /* ================= DEDUCT FROM STOCK (BRANCH-LEVEL) ================= */
                    $stock = Stock::lockForUpdate()
                        ->where('product_id', $it->product_id)
                        ->where('branch_id', $itemBranchId)
                        ->first();

                    if (!$stock) {
                        $stock = Stock::create([
                            'product_id' => $it->product_id,
                            'branch_id'  => $itemBranchId,
                            'qty'        => 0,
                        ]);
                    }

                    $stockBefore = $stock->qty ?? 0;
                    $stock->qty = $stockBefore - $it->sales_qty;
                    $stock->save();
                    
                    Log::info('Deducted from stocks (branch-level)', [
                        'product_id' => $it->product_id,
                        'branch_id' => $itemBranchId,
                        'qty_before' => $stockBefore,
                        'qty_after' => $stock->qty,
                    ]);

                    // Sale Item
                    SaleItem::create([
                        'invoice_no'       => $sale->invoice_no,
                        'branch_id'        => $sale->branch_id,
                        'sale_id'          => $sale->id,
                        'warehouse_id'     => $wid,
                        'product_id'       => $it->product_id,
                        'watt'             => (float) ($it->watt ?? 0),
                        'sales_qty'        => $it->sales_qty,
                        'retail_price'     => $it->retail_price,
                        'discount_percent' => (float) ($it->discount_percent ?? 0),
                        'discount_amount'  => (float) ($it->discount_amount ?? 0),
                        'amount'           => $it->amount,
                    ]);

                    // Stock Movement
                    StockMovement::create([
                        'branch_id'     => $sale->branch_id ?? ($branchId ?? 1),
                        'product_id'    => $it->product_id,
                        'type'          => 'out',
                        'qty'           => $it->sales_qty,
                        'ref_type'      => 'SALE',
                        'ref_id'        => $sale->id,
                        'ref_uuid'      => $booking ? $booking->invoice_no : $sale->invoice_no,
                        'is_auto_pluck' => 1,
                        'note'          => 'Sale Invoice ' . $sale->invoice_no . ($wid ? ' (Warehouse: ' . $wid . ')' : ' (Branch Stock)'),
                    ]);

                    /* ================= CHECK STOCK ALERT ================= */
                    StockAlertService::checkAndCreateAlert($it->product_id, $wid ?? $itemBranchId);
                }

                /* ================= ACCOUNT UPDATE ================= */
                $salesHead = AccountHead::where('name', 'like', '%Sales%')->first();
                if ($salesHead) {
                    $saleAccount = Account::lockForUpdate()->where('head_id', $salesHead->id)->first();
                    if ($saleAccount) {
                        Log::info('Found Sales account', [
                            'account_id' => $saleAccount->id,
                            'account_title' => $saleAccount->title,
                            'sale_total' => $sale->total_net,
                        ]);
                    }
                }

                /* ================= PROCESS PAYMENT RECEIPTS ================= */
                Log::info('===== RECEIPT PROCESSING START =====', [
                    'booking_id' => $booking ? $booking->id : null,
                    'sale_id' => $sale->id,
                    'sale_invoice' => $sale->invoice_no,
                ]);

                if (!empty($request->receipt_account_id) && is_array($request->receipt_account_id)) {
                    $rowAccountIds = [];
                    $rowAmounts = [];
                    foreach ($request->receipt_account_id as $i => $accId) {
                        $acc = $accId;
                        $amt = (float) ($request->receipt_amount[$i] ?? 0);
                        if ($amt > 0 && (empty($acc) || !is_numeric($acc))) {
                            abort(422, 'Invalid receipt row: amount provided but account missing or invalid at row ' . ($i + 1));
                        }
                        if (!$acc || $amt <= 0) continue;
                        $rowAccountIds[] = (int) $acc;
                        $rowAmounts[] = $amt;
                    }

                    if (!empty($rowAccountIds)) {
                        $unique = [];
                        $uniqueAccountIds = [];
                        $uniqueAmounts = [];
                        foreach ($rowAccountIds as $i => $acctId) {
                            $amt = $rowAmounts[$i] ?? ($rowAmounts[0] ?? 0);
                            if ($amt <= 0) continue;
                            $sig = $acctId . '|' . number_format((float)$amt, 2, '.', '');
                            if (isset($unique[$sig])) continue;
                            $unique[$sig] = true;
                            $uniqueAccountIds[] = $acctId;
                            $uniqueAmounts[] = $amt;
                        }

                        foreach ($uniqueAccountIds as $i => $acctId) {
                            $amt = $uniqueAmounts[$i];
                            $rv = ReceiptsVoucher::create([
                                'branch_id'        => $sale->branch_id ?? ($branchId ?? 1),
                                'rvid'             => ReceiptsVoucher::generateRVID(auth()->id()),
                                'receipt_date'     => $entryDateTime->toDateString(),
                                'entry_date'       => $entryDateTime,
                                'type'             => 'SALE_RECEIPT',
                                'party_id'         => $customerId,
                                'booking_id'       => $booking ? $booking->id : null,
                                'sale_id'          => $sale->id,
                                'tel'              => $tel,
                                'remarks'          => $remarks,
                                'reference_no'     => $sale->invoice_no,
                                'row_account_head' => 'Cash/Bank',
                                'row_account_id'   => is_array($acctId) ? json_encode($acctId) : $acctId,
                                'amount'           => is_array($amt) ? json_encode($amt) : $amt,
                                'total_amount'     => $amt,
                                'processed'        => true,
                                'created_at'       => $entryDateTime,
                                'updated_at'       => $entryDateTime,
                            ]);

                            $rowAccount = Account::lockForUpdate()->find($acctId);
                            if ($rowAccount) {
                                \App\Services\VoucherService::postLedgerEntry(
                                    $acctId,
                                    'receipt',
                                    $sale->invoice_no ?? $rv->rvid,
                                    $rv->id,
                                    $entryDateTime->toDateString(),
                                    "Sale Receipt — Invoice #" . ($sale->invoice_no ?? '') . ($booking ? " (Booking #" . ($booking->id ?? '') . ")" : ""),
                                    $amt,
                                    0,
                                    auth()->id()
                                );
                            }
                        }
                    }
                }

                // If booking had unprocessed receipts in DB, mark them
                if ($booking) {
                    $receipts = ReceiptsVoucher::query()
                        ->where('type', 'SALE_RECEIPT')
                        ->where(function ($q) use ($booking) {
                            $q->where('booking_id', $booking->id)
                                ->orWhere(function ($q2) use ($booking) {
                                    $q2->whereNull('booking_id')
                                        ->where('reference_no', $booking->invoice_no);
                                });
                        })
                        ->where(function ($q) {
                            $q->where('processed', false)->orWhereNull('processed');
                        })
                        ->lockForUpdate()
                        ->get();

                    foreach ($receipts as $rv) {
                        $rv->processed = true;
                        $rv->save();
                    }

                    /* ================= MARK BOOKING POSTED ================= */
                    $booking->update([
                        'is_posted' => 1,
                        'posted_at' => $entryDateTime,
                        'created_at' => $entryDateTime,
                        'updated_at' => $entryDateTime,
                        'status'    => 'sale',
                    ]);
                }

                /* ================= CREATE NOTIFICATION IF NOTIFY_ME IS SET ================= */
                $notifyDays = $booking ? ($booking->notify_me ?? 0) : ($request->input('notify_me') ?? 0);
                if (!empty($notifyDays) && (int)$notifyDays > 0) {
                    $notificationDate = Carbon::today()->addDays((int)$notifyDays);
                    Notification::create([
                        'booking_id'        => $booking ? $booking->id : null,
                        'sale_id'           => $sale->id,
                        'customer_id'       => $customerId,
                        'type'              => 'booking_payment',
                        'title'             => 'Payment Reminder - ' . $sale->invoice_no,
                        'description'       => 'Payment reminder for sale ' . $sale->invoice_no . ' (Amount: ' . $sale->total_net . ')',
                        'notification_date' => $notificationDate,
                        'status'            => 'pending',
                        'created_by'        => auth()->id(),
                    ]);
                }

                /* ================= RESPONSE ================= */
                return response()->json([
                    'ok'          => true,
                    'sale_id'     => $sale->id,
                    'invoice_url' => route('sale.invoice', $sale->id),
                    'status'      => $booking ? $booking->status : 'sale',
                    'msg'         => $message ?? 'Sale completed & posted successfully. Stock deducted!',
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Sale post failed', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $status = 422;
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                $status = $e->getStatusCode();
            }
            return response()->json(['ok' => false, 'error' => $e->getMessage()], $status);
        }
    }


    /**
     * ═══════════════════════════════════════════════════════════════════════════
     * 🎯 DRAFT POST: Gate Pass / Draft mode posting (identical to ajaxPost)
     * ═══════════════════════════════════════════════════════════════════════════
     * 
     * KEY DIFFERENCE FROM ajaxPost: Stock (warehouse_stocks & stocks tables) NOT deducted
     * IDENTICAL LOGIC:
     * - Customer ledger (for credit customers)
     * - Sale items saved to database
     * - Stock movement tracking (record only, not deduction)
     * - Account updates (Sales account credit)
     * - Receipt processing
     * - Payment notifications
     * - sale_postings saved for gate pass processing
     * 
     * Stock deduction happens ONLY when Gate Pass is generated
     */
    public function ajaxPostDraft(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {

                /* ================= VALIDATION & FETCH BOOKING ================= */
                if (!$request->booking_id) {
                    abort(422, 'Booking ID required');
                }

                $booking = ProductBooking::with('items')
                    ->lockForUpdate()
                    ->findOrFail($request->booking_id);

                if ($booking->is_posted) {
                    abort(422, 'Invoice already posted');
                }

                // Warehouse selection is optional for draft mode
                $warehouseMap = $request->warehouse_id ?? [];
                
                Log::info('Draft Post Started', [
                    'booking_id' => $booking->id,
                    'invoice' => $booking->invoice_no,
                    'items_count' => $booking->items->count()
                ]);

                /* ================= UPDATE WAREHOUSE IDs (Optional) ================= */
                foreach ($booking->items as $item) {
                    $rawWid = $warehouseMap[$item->product_id] ?? null;
                    $wid = null;
                    if ($rawWid) {
                        if (is_string($rawWid) && str_starts_with($rawWid, 'warehouse_')) {
                            $wid = (int) str_replace('warehouse_', '', $rawWid);
                        } elseif (is_numeric($rawWid) && (int) $rawWid > 0) {
                            $wid = (int) $rawWid;
                        }
                    }
                    $item->warehouse_id = $wid;
                    $item->save();
                }
                Log::info('Updated booking items with warehouse selections (draft)', ['booking_id' => $booking->id]);

                /* ================= CREATE SALE RECORD ================= */
                $invoiceNo = null;
                try {
                    if ($booking->branch_id) {
                        $branch = Branch::lockForUpdate()->find($booking->branch_id);
                        if ($branch) {
                            $branch->invoice_counter = ((int) ($branch->invoice_counter ?? 0)) + 1;
                            $branch->save();
                            $invoiceNo = 'INV-' . str_pad($branch->invoice_counter, 4, '0', STR_PAD_LEFT);
                            Log::info('Generated sale invoice for draft', ['branch_id' => $booking->branch_id, 'invoice_no' => $invoiceNo]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to generate draft invoice counter', ['error' => $e->getMessage()]);
                }

                if (!$invoiceNo) {
                    $maxSaleId = Sale::where('branch_id', $booking->branch_id)->max('id') ?? 0;
                    $invoiceNo = 'INV-' . str_pad($maxSaleId + 1, 4, '0', STR_PAD_LEFT);
                }

                $sale = Sale::create([
                    'branch_id'            => $booking->branch_id,
                    'invoice_no'           => $invoiceNo,
                    'manual_invoice'       => $request->input('manual_invoice', $request->input('Invoice_main', $booking->manual_invoice)),
                    'customer_id'          => $booking->customer_id,
                    'salesman_id'          => $booking->salesman_id ?? $request->salesman_id ?? null,
                    'sub_customer'         => (($booking->party_type ?? '') === 'walking') ? ($booking->customer_name ?? null) : null,
                    'party_type'           => $booking->party_type,
                    'address'              => $booking->address,
                    'tel'                  => $booking->tel,
                    'remarks'              => $request->input('remarks', $request->input('reference', $booking->remarks)),
                    'sub_total1'           => $booking->sub_total1,
                    'sub_total2'           => $booking->sub_total2,
                    'discount_percent'     => $booking->discount_percent,
                    'discount_amount'      => $booking->discount_amount,
                    'additional_discount'  => $booking->additional_discount ?? 0,
                    'extra_charges'        => $booking->extra_charges ?? 0,
                    'previous_balance'     => $booking->previous_balance,
                    'total_balance'        => $booking->total_balance,
                    'total_net'            => $booking->sub_total2 ?? 0,
                    'status'               => 'draft_posted',
                ]);

                Log::info('Draft sale record created', ['sale_id' => $sale->id, 'invoice' => $sale->invoice_no]);

                /* ================= CUSTOMER LEDGER (ONLY FOR CREDIT CUSTOMERS) ================= */
                // Only create ledger entries for credit customers
                if(($booking->party_type ?? '') === 'credit' && $booking->customer_id){
                    
                    $lastLedger = CustomerLedger::where('customer_id', $booking->customer_id)
                        ->latest('id')
                        ->lockForUpdate()
                        ->first();

                    $customer = Customer::find($booking->customer_id);
                    $customerOpeningBalance = $customer->opening_balance ?? 0;

                    $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : $customerOpeningBalance;
                    $closingBalance = (float)($booking->total_balance ?? 0);
                    $saleAmount = ($booking->sub_total2 ?? 0) - ($booking->additional_discount ?? 0) + ($booking->extra_charges ?? 0);
                    
                    $totalReceipts = 0;
                    if (!empty($request->receipt_amount) && is_array($request->receipt_amount)) {
                        foreach ($request->receipt_amount as $amt) {
                            $amt = (float) $amt;
                            if ($amt > 0) $totalReceipts += $amt;
                        }
                    }

                    Log::info('Creating customer ledger entry for draft credit sale', [
                        'invoice' => $booking->invoice_no,
                        'customer_id' => $booking->customer_id,
                        'previous_balance' => $previousBalance,
                        'opening_balance' => $customerOpeningBalance,
                        'new_sale_amount' => $saleAmount,
                        'receipts_paid' => $totalReceipts,
                        'closing_balance_from_frontend' => $closingBalance,
                    ]);

                    CustomerLedger::create([
                        'customer_id'        => $booking->customer_id,
                        'admin_or_user_id'   => auth()->id(),
                        'opening_balance'    => $customerOpeningBalance,
                        'previous_balance'   => $previousBalance,
                        'total_debit'        => $saleAmount,
                        'total_credit'       => $totalReceipts,
                        'closing_balance'    => $closingBalance,
                    ]);
                }

                /* ═══════════════════════════════════════════════════════════════ */
                /* 🎯 SALE ITEMS & TRACKING (NO STOCK DEDUCTION IN DRAFT MODE) ✅   */
                /* ═══════════════════════════════════════════════════════════════ */
                foreach ($booking->items as $it) {
                    $warehouseId = $it->warehouse_id;
                    $branchId = $booking->branch_id;
                    
                    $sourceType = $warehouseId ? 'warehouse' : 'branch';
                    $sourceId = $warehouseId ?? $branchId;

                    // ✅ SAVE TO sale_postings with status='pending' (for gate pass processing)
                    SalePosting::create([
                        'sale_id'      => $sale->id,
                        'product_id'   => $it->product_id,
                        'qty'          => $it->sales_qty,
                        'source_type'  => $sourceType,
                        'source_id'    => $sourceId,
                        'status'       => 'pending',
                    ]);

                    Log::info('Saved to sale_postings (draft)', [
                        'product_id' => $it->product_id,
                        'qty' => $it->sales_qty,
                        'source_type' => $sourceType,
                        'source_id' => $sourceId
                    ]);

                    // ✅ SAVE TO sale_items TABLE (for product details record - SAME AS ajaxPost)
                    SaleItem::create([
                        'invoice_no'    => $sale->invoice_no,
                        'branch_id'     => $sale->branch_id,
                        'sale_id'       => $sale->id,
                        'warehouse_id'  => $warehouseId,
                        'product_id'    => $it->product_id,
                        'sales_qty'     => $it->sales_qty,
                        'retail_price'  => $it->retail_price,
                        'discount_percent' => (float) ($it->discount_percent ?? 0),
                        'discount_amount' => (float) ($it->discount_amount ?? 0),
                        'amount'        => $it->amount,
                    ]);

                    Log::info('Saved to sale_items (draft)', [
                        'product_id' => $it->product_id,
                        'sale_id' => $sale->id,
                        'invoice_no' => $sale->invoice_no,
                        'qty' => $it->sales_qty,
                        'amount' => $it->amount
                    ]);

                    // ✅ STOCK MOVEMENT TRACKING (record only, NO deduction in draft mode)
                    StockMovement::create([
                        'branch_id'     => $sale->branch_id ?? ($branchId ?? 1),
                        'product_id'    => $it->product_id,
                        'type'          => 'out',
                        'qty'           => $it->sales_qty,
                        'ref_type'      => 'SALE',
                        'ref_id'        => $sale->id,
                        'ref_uuid'      => $booking->invoice_no,
                        'is_auto_pluck' => 1,
                        'note'          => 'Sale Invoice ' . $booking->invoice_no . ' (Draft - Stock deduction pending) ' . ($warehouseId ? ' (Warehouse: ' . $warehouseId . ')' : ' (Branch Stock)'),
                    ]);

                    // ✅ CHECK STOCK ALERT (same as ajaxPost for consistency)
                    StockAlertService::checkAndCreateAlert($it->product_id, $warehouseId ?? $branchId);
                }

                /* ================= ACCOUNT UPDATE (Sales account credit) ================= */
                // Same logic as ajaxPost - find Sales account and credit it
                $salesHead = AccountHead::where('name', 'like', '%Sales%')->first();
                Log::info('Looking for Sales account head (Draft)', ['found' => $salesHead ? true : false, 'head_name' => $salesHead->name ?? null]);
                
                if ($salesHead) {
                    $saleAccount = Account::lockForUpdate()->where('head_id', $salesHead->id)->first();
                    if ($saleAccount) {
                        Log::info('Found Sales account (Draft)', [
                            'account_id' => $saleAccount->id,
                            'account_title' => $saleAccount->title,
                            'sale_total' => $sale->total_net,
                        ]);
                    } else {
                        Log::warning('No Sales account found under head (Draft)', ['head_id' => $salesHead->id]);
                    }
                } else {
                    Log::warning('Sales account head not found in database (Draft)');
                }

                /* ================= PROCESS PAYMENT RECEIPTS (same as ajaxPost) ================= */
                Log::info('===== RECEIPT PROCESSING START (DRAFT) =====', [
                    'booking_id' => $booking->id,
                    'booking_invoice' => $booking->invoice_no,
                    'sale_id' => $sale->id,
                    'sale_invoice' => $sale->invoice_no,
                ]);
                
                if (!empty($request->receipt_account_id) && is_array($request->receipt_account_id)) {
                    $rowAccountIds = [];
                    $rowAmounts = [];
                    foreach ($request->receipt_account_id as $i => $accId) {
                        $acc = $accId;
                        $amt = (float) ($request->receipt_amount[$i] ?? 0);
                        if ($amt > 0 && (empty($acc) || !is_numeric($acc))) {
                            abort(422, 'Invalid receipt row: amount provided but account missing or invalid at row ' . ($i + 1));
                        }
                        if (!$acc || $amt <= 0) continue;
                        $rowAccountIds[] = (int) $acc;
                        $rowAmounts[] = $amt;
                    }

                    if (!empty($rowAccountIds)) {
                        $existsProcessed = ReceiptsVoucher::where('reference_no', $booking->invoice_no)
                            ->where('type', 'SALE_RECEIPT')
                            ->where('processed', true)
                            ->exists();

                        if ($existsProcessed) {
                            Log::info('Processed SALE_RECEIPT already exists; skipping creation (Draft)', ['reference' => $booking->invoice_no]);
                        } else {
                            $existsUnprocessed = ReceiptsVoucher::where('reference_no', $booking->invoice_no)
                                ->where('type', 'SALE_RECEIPT')
                                ->where(function ($q) {
                                    $q->where('processed', false)->orWhereNull('processed');
                                })
                                ->exists();

                            if ($existsUnprocessed) {
                                Log::info('Unprocessed SALE_RECEIPT(s) exist for booking; skipping creation (Draft)', ['reference' => $booking->invoice_no]);
                            } else {
                                $unique = [];
                                $uniqueAccountIds = [];
                                $uniqueAmounts = [];
                                foreach ($rowAccountIds as $i => $acctId) {
                                    $amt = $rowAmounts[$i] ?? ($rowAmounts[0] ?? 0);
                                    if ($amt <= 0) continue;
                                    $sig = $acctId . '|' . number_format((float)$amt, 2, '.', '');
                                    if (isset($unique[$sig])) continue;
                                    $unique[$sig] = true;
                                    $uniqueAccountIds[] = $acctId;
                                    $uniqueAmounts[] = $amt;
                                }

                                foreach ($uniqueAccountIds as $i => $acctId) {
                                    $amt = $uniqueAmounts[$i];
                                    $rv = ReceiptsVoucher::create([
                                        'branch_id' => $sale->branch_id ?? ($booking->branch_id ?? (auth()->user()->branch_id ?? 1)),
                                        'rvid' => ReceiptsVoucher::generateRVID(auth()->id()),
                                        'receipt_date' => Carbon::today(),
                                        'entry_date' => Carbon::now(),
                                        'type' => 'SALE_RECEIPT',
                                        'party_id' => $booking->customer_id,
                                        'booking_id' => $booking->id,
                                        'sale_id' => $sale->id,
                                        'tel' => $booking->tel,
                                        'remarks' => $booking->remarks,
                                        'reference_no' => $sale->invoice_no,
                                        'row_account_head' => 'Cash/Bank',
                                        'row_account_id' => is_array($acctId) ? json_encode($acctId) : $acctId,
                                        'amount' => is_array($amt) ? json_encode($amt) : $amt,
                                        'total_amount' => $amt,
                                        'processed' => true,
                                    ]);

                                    Log::info('Created and applied per-account SALE_RECEIPT (Draft)', ['rv_id' => $rv->id, 'rvid' => $rv->rvid, 'account' => $acctId, 'amount' => $amt, 'reference' => $sale->invoice_no, 'sale_id' => $sale->id]);

                                    try {
                                        $rowAccount = Account::lockForUpdate()->find($acctId);
                                        if ($rowAccount) {
                                            // Post to Account Ledger
                                            \App\Services\VoucherService::postLedgerEntry(
                                                $acctId,
                                                'receipt',
                                                $sale->invoice_no ?? $rv->rvid,
                                                $rv->id,
                                                Carbon::today()->toDateString(),
                                                "Sale Receipt — Invoice #" . ($sale->invoice_no ?? '') . " (Booking #" . ($booking->id ?? '') . ")",
                                                $amt,
                                                0,
                                                auth()->id()
                                            );
                                            Log::info('Updated account balance (Draft)', ['account_id' => $acctId, 'amount' => $amt, 'type' => $rowAccount->type]);
                                        }
                                    } catch (\Exception $e) {
                                        Log::error('Failed to apply draft receipt', ['error' => $e->getMessage(), 'rv' => $rv->id ?? null]);
                                    }
                                }
                            }
                        }
                    }
                }

                $receipts = ReceiptsVoucher::query()
                    ->where('type', 'SALE_RECEIPT')
                    ->where(function ($q) use ($booking) {
                        $q->where('booking_id', $booking->id)
                            ->orWhere(function ($q2) use ($booking) {
                                $q2->whereNull('booking_id')
                                    ->where('reference_no', $booking->invoice_no);
                            });
                    })
                    ->where(function ($q) {
                        $q->where('processed', false)->orWhereNull('processed');
                    })
                    ->lockForUpdate()
                    ->get();

                $appliedSignatures = [];

                foreach ($receipts as $rv) {
                    Log::info('Found receipt for processing (Draft)', ['rv_id' => $rv->id, 'rvid' => $rv->rvid ?? null, 'processed' => $rv->processed ?? null, 'reference' => $rv->reference_no ?? null]);
                    if (!empty($rv->processed)) {
                        Log::info('Skipping processed receipt', ['rv_id' => $rv->id, 'rvid' => $rv->rvid ?? null]);
                        continue;
                    }
                    
                    $totalAmount = 0;
                    if (!empty($rv->total_amount)) {
                        $totalAmount = (float) $rv->total_amount;
                    } elseif (!empty($rv->amount)) {
                        $decoded = json_decode($rv->amount, true);
                        if (is_array($decoded)) {
                            $totalAmount = array_sum(array_map('floatval', $decoded));
                        } else {
                            $totalAmount = (float) $rv->amount;
                        }
                    }

                    if ($totalAmount <= 0) continue;

                    $rowAccountIds = [];
                    $rowAmounts = [];

                    if (!empty($rv->row_account_id)) {
                        $decodedIds = json_decode($rv->row_account_id, true);
                        if (is_array($decodedIds)) {
                            $rowAccountIds = $decodedIds;
                        } else {
                            $rowAccountIds = [$rv->row_account_id];
                        }
                    }

                    if (!empty($rv->amount)) {
                        $decodedAmounts = json_decode($rv->amount, true);
                        if (is_array($decodedAmounts)) {
                            $rowAmounts = array_map('floatval', $decodedAmounts);
                        } else {
                            $rowAmounts = [(float) $rv->amount];
                        }
                    }

                    if (empty($rowAmounts) || array_sum($rowAmounts) <= 0) {
                        if (property_exists($rv, 'processed')) {
                            $rv->processed = true;
                            $rv->save();
                            Log::info('Marked empty-amount receipt processed', ['rv_id' => $rv->id]);
                        }
                        continue;
                    }

                    Log::info('Applying receipt rows (Draft)', ['rv_id' => $rv->id, 'accounts' => $rowAccountIds, 'amounts' => $rowAmounts]);

                    foreach ($rowAccountIds as $i => $accId) {
                        $rowAmount = $rowAmounts[$i] ?? ($rowAmounts[0] ?? 0);
                        if ($rowAmount <= 0) continue;

                        $signature = ($rv->booking_id ?? $rv->reference_no) . '|' . $accId . '|' . number_format((float)$rowAmount, 2, '.', '');
                        if (in_array($signature, $appliedSignatures, true)) {
                            Log::warning('Skipping duplicate receipt-account-amount combination', ['signature' => $signature, 'rv_id' => $rv->id]);
                            continue;
                        }

                        $rowAccount = Account::lockForUpdate()->find($accId);
                        if (! $rowAccount) continue;
                        Log::info('Applying to account (Draft)', ['rv_id' => $rv->id, 'account_id' => $rowAccount->id, 'amount' => $rowAmount, 'type' => $rowAccount->type]);

                        $appliedSignatures[] = $signature;
                    }

                    $rv->processed = true;
                    $rv->save();
                    Log::info('Marked receipt processed (Draft)', ['rv_id' => $rv->id]);
                }

                /* ================= MARK BOOKING POSTED ================= */
                $booking->update([
                    'is_posted' => 1,
                    'posted_at' => now(),
                    'status'    => 'draft_posted',
                ]);

                /* ================= CREATE NOTIFICATION IF NOTIFY_ME IS SET ================= */
                if (!empty($booking->notify_me) && (int)$booking->notify_me > 0) {
                    $notificationDate = Carbon::today()->addDays($booking->notify_me);
                    
                    Notification::create([
                        'booking_id' => $booking->id,
                        'sale_id' => $sale->id,
                        'customer_id' => $booking->customer_id,
                        'type' => 'booking_payment',
                        'title' => 'Payment Reminder - ' . $booking->invoice_no,
                        'description' => 'Payment reminder for booking ' . $booking->invoice_no . ' (Amount: ' . $sale->total_net . ')',
                        'notification_date' => $notificationDate,
                        'status' => 'pending',
                        'created_by' => auth()->id(),
                    ]);

                    Log::info('Created payment notification (Draft)', [
                        'booking_id' => $booking->id,
                        'notification_date' => $notificationDate,
                        'days' => $booking->notify_me,
                        'customer_id' => $booking->customer_id,
                    ]);
                }

                /* ================= RESPONSE ================= */
                return response()->json([
                    'ok'          => true,
                    'sale_id'     => $sale->id,
                    'invoice_no'  => $sale->invoice_no,
                    'invoice_url' => route('sale.invoice', $sale->id),
                    'msg'         => 'Draft saved! Full sale details recorded. Stock will be deducted when gate pass is generated.',
                    'mode'        => 'draft_posted',
                    'postings_count' => $booking->items->count()
                ]);

            });
        } catch (\Exception $e) {
            Log::error('Draft post failed', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $status = 422;
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                $status = $e->getStatusCode();
            }
            return response()->json(['ok' => false, 'error' => $e->getMessage()], $status);
        }
    }

    /**
     * Post a booking using the Main Store warehouse for all items.
     * Intended for quick sales (cash / walking customers) where warehouse selection
     * should default to the main store and the warehouse modal should be skipped.
     */
    public function ajaxPostMainStore(Request $request)
    {
        if (!$request->booking_id) {
            abort(422, 'Booking ID required');
        }

        $booking = ProductBooking::with('items')->findOrFail($request->booking_id);

        // Update booking branch if admin selected one from modal
        $requestBranchId = $request->input('branch_id');
        if ($requestBranchId && auth()->user() && auth()->user()->hasRole('super admin')) {
            $booking->branch_id = (int) $requestBranchId;
            $booking->save();
            
            Log::info('Admin selected branch for sale', [
                'user_id' => auth()->id(),
                'booking_id' => $booking->id,
                'selected_branch_id' => $requestBranchId
            ]);
        }

        // Update booking with party type from request (sent from frontend "Sale" button)
        // If partyType provided in request, use it (convert to lowercase)
        // Otherwise use stored party_type in booking
        $requestPartyType = $request->input('partyType');
        if ($requestPartyType) {
            $partyType = strtolower($requestPartyType);
            if (in_array($partyType, ['cash', 'walking', 'credit'])) {
                $booking->party_type = $partyType;
                $booking->save();
            }
        }

        // Check if party_type is valid for main store quick post
        $ptype = strtolower($booking->party_type ?? '');
        if (!$ptype || !in_array($ptype, ['cash', 'walking','credit'])) {
            abort(403, 'Main-store quick post allowed only for cash, walking or credit customers. Current party type: ' . ($booking->party_type ?? 'not set'));
        }

        /* ================= CHECK WAREHOUSE STOCK AVAILABILITY ================= */
        // ERP STANDARD: Validate TOTAL available stock (shop + all warehouses)
        // For "Sale" button: check total stock in branch (warehouse_id = NULL + warehouse_id > 0)
        $missingProducts = [];
        $stockCheckDetails = [];
        
        $forceSale = (bool) ($request->input('force_sale', 0));

        foreach ($booking->items as $item) {
            // Sum ALL stock for this product in this branch (both shop and warehouses)
            $totalStock = (float) WarehouseStock::where('product_id', $item->product_id)
                ->where('branch_id', $booking->branch_id)
                // Include BOTH: warehouse_id = NULL (shop) and warehouse_id > 0 (warehouses)
                ->sum('quantity');
            
            $product = Product::find($item->product_id);
            $productName = $product?->item_name ?? "Product #{$item->product_id}";
            
            $stockCheckDetails[] = [
                'product_id' => $item->product_id,
                'product_name' => $productName,
                'branch_id' => $booking->branch_id,
                'total_stock_found' => $totalStock,
                'required_qty' => $item->sales_qty
            ];
            
            if ($totalStock < $item->sales_qty) {
                $missingProducts[] = $productName;
            }
        }

        // If any products missing AND not force sale, abort with error
        if (!empty($missingProducts) && !$forceSale) {
            Log::warning('Sale button: Product stock not available - Detailed Check', [
                'booking_id' => $booking->id,
                'missing_products' => $missingProducts,
                'branch_id' => $booking->branch_id,
                'stock_check_details' => $stockCheckDetails
            ]);
            abort(422, 'Shop does not have product stock available: ' . implode(', ', $missingProducts));
        }
        
        Log::info('Sale button: Stock validation passed or force_sale applied', [
            'booking_id' => $booking->id,
            'branch_id' => $booking->branch_id,
            'force_sale' => $forceSale,
            'stock_check_details' => $stockCheckDetails
        ]);

        // All products have stock or force sale accepted - proceed with intelligent warehouse allocation
        // For each product: prefer shop stock (warehouse_id = NULL), fallback to warehouses
        $map = [];
        foreach ($booking->items as $item) {
            $warehouseId = null;  // Default to NULL (shop/branch stock)
            
            // Check if shop stock exists for this product
            $shopStock = WarehouseStock::where('product_id', $item->product_id)
                ->where('branch_id', $booking->branch_id)
                ->whereNull('warehouse_id')
                ->first();
            
            if ($shopStock && $shopStock->quantity >= $item->sales_qty) {
                // Shop has sufficient stock - use it (warehouse_id = NULL)
                $warehouseId = null;
            } else {
                // Find first warehouse with sufficient stock
                $warehouseStock = WarehouseStock::where('product_id', $item->product_id)
                    ->where('branch_id', $booking->branch_id)
                    ->whereNotNull('warehouse_id')
                    ->where('quantity', '>=', $item->sales_qty)
                    ->first();
                
                if ($warehouseStock) {
                    $warehouseId = $warehouseStock->warehouse_id;  // Use warehouse_id > 0
                } else {
                    // Default to shop stock (warehouse_id = NULL) so shop stock will be decremented into negative
                    $warehouseId = null;
                }
            }
            
            $map[$item->product_id] = $warehouseId;
        }

        Log::info('Sale button: All products validated, proceeding with smart warehouse allocation', [
            'booking_id' => $booking->id,
            'party_type' => $ptype,
            'product_count' => count($map),
            'warehouse_map' => $map,
            'has_receipt_data' => !empty($request->receipt_account_id) ? 'yes' : 'no'
        ]);

        // Merge mapping into request and delegate to existing ajaxPost (reuses full posting logic)
        // Include receipt data from the request so customer ledger and receipt vouchers are created
        $request->merge(['warehouse_id' => $map]);
        // Receipt data is already in request (receipt_account_id[], receipt_amount[])

        // Delegate to ajaxPost which already performs transaction/stock/ledger/receipt updates
        return $this->ajaxPost($request);
    }

    public function ajaxPostPartialDelivery(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                if (!$request->booking_id) {
                    abort(422, 'Booking ID required');
                }

                Log::info('ajaxPostPartialDelivery received request', [
                    'booking_id' => $request->booking_id,
                    'warehouse_id' => $request->warehouse_id,
                    'dispatch_qty' => $request->dispatch_qty
                ]);

                $booking = ProductBooking::with('items')
                    ->lockForUpdate()
                    ->findOrFail($request->booking_id);

                if ($booking->is_posted) {
                    Log::warning('ajaxPostPartialDelivery: booking already posted', [
                        'booking_id' => $booking->id,
                        'is_posted' => $booking->is_posted,
                        'invoice_no' => $booking->invoice_no
                    ]);
                    abort(422, 'Invoice already posted');
                }

                // Selected warehouse or branch location for partial dispatch
                $locationVal = $request->input('warehouse_id', '');
                if (!$locationVal) {
                    abort(422, 'Dispatch location / warehouse selection required');
                }

                $warehouseId = null;
                $targetBranchId = $booking->branch_id ?? (auth()->user()->branch_id ?? 1);
                $deliveryLocationType = 'branch';

                if (str_starts_with($locationVal, 'warehouse_')) {
                    $warehouseId = (int) str_replace('warehouse_', '', $locationVal);
                    $deliveryLocationType = 'warehouse';
                } elseif (str_starts_with($locationVal, 'branch_')) {
                    $targetBranchId = (int) str_replace('branch_', '', $locationVal);
                    $warehouseId = null;
                    $deliveryLocationType = 'branch';
                } elseif (is_numeric($locationVal) && (int)$locationVal > 0) {
                    $whExists = Warehouse::where('id', (int)$locationVal)->exists();
                    if ($whExists) {
                        $warehouseId = (int)$locationVal;
                        $deliveryLocationType = 'warehouse';
                    } else {
                        $targetBranchId = (int)$locationVal;
                        $warehouseId = null;
                        $deliveryLocationType = 'branch';
                    }
                }

                // Update booking branch if super admin selected one
                $requestBranchId = $request->input('branch_id') ?? $targetBranchId;
                if ($requestBranchId && auth()->user() && auth()->user()->hasRole('super admin')) {
                    $booking->branch_id = (int) $requestBranchId;
                    $booking->save();
                }

                $branchId = $booking->branch_id ?? $targetBranchId;

                // Update party type if passed
                $requestPartyType = $request->input('partyType');
                if ($requestPartyType) {
                    $partyType = strtolower($requestPartyType);
                    if (in_array($partyType, ['cash', 'walking', 'credit'])) {
                        $booking->party_type = $partyType;
                        $booking->save();
                    }
                }

                // Generate Invoice Number
                $invoiceNo = null;
                if ($booking->branch_id) {
                    $branch = Branch::lockForUpdate()->find($booking->branch_id);
                    if ($branch) {
                        $branch->invoice_counter = ((int) ($branch->invoice_counter ?? 0)) + 1;
                        $branch->save();
                        $invoiceNo = 'INV-' . str_pad($branch->invoice_counter, 4, '0', STR_PAD_LEFT);
                    }
                }
                if (!$invoiceNo) {
                    $maxSaleId = Sale::where('branch_id', $booking->branch_id)->max('id') ?? 0;
                    $invoiceNo = 'INV-' . str_pad($maxSaleId + 1, 4, '0', STR_PAD_LEFT);
                }

                // Create Sale Record (full order value)
                $saleData = [
                    'invoice_no'       => $invoiceNo,
                    'manual_invoice'   => $request->input('manual_invoice', $request->input('Invoice_main', $booking->manual_invoice)),
                    'customer_id'      => $booking->customer_id,
                    'salesman_id'      => $booking->salesman_id ?? $request->salesman_id ?? null,
                    'sub_customer'     => (($booking->party_type ?? '') === 'walking') ? ($booking->customer_name ?? null) : null,
                    'party_type'       => $booking->party_type,
                    'address'          => $booking->address,
                    'tel'              => $booking->tel,
                    'remarks'          => $request->input('remarks', $request->input('reference', $booking->remarks)),
                    'sub_total1'       => $booking->sub_total1,
                    'sub_total2'       => $booking->sub_total2,
                    'discount_percent' => $booking->discount_percent,
                    'discount_amount'  => $booking->discount_amount,
                    'additional_discount' => $booking->additional_discount ?? 0,
                    'extra_charges'    => $booking->extra_charges ?? 0,
                    'previous_balance' => $booking->previous_balance,
                    'total_balance'    => $booking->total_balance,
                    'total_net'        => $booking->sub_total2 ?? 0,
                ];

                if (\Illuminate\Support\Facades\Schema::hasColumn('sales', 'branch_id')) {
                    $saleData['branch_id'] = $booking->branch_id;
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('sales', 'booking_id')) {
                    $saleData['booking_id'] = $booking->id;
                }

                $sale = Sale::create($saleData);

                // Customer Ledger (Credit Customers)
                if (($booking->party_type ?? '') === 'credit' && $booking->customer_id) {
                    $lastLedger = CustomerLedger::where('customer_id', $booking->customer_id)
                        ->latest('id')
                        ->lockForUpdate()
                        ->first();
                    $customer = Customer::find($booking->customer_id);
                    $customerOpeningBalance = $customer->opening_balance ?? 0;
                    $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : $customerOpeningBalance;
                    $closingBalance = (float)($booking->total_balance ?? 0);
                    $saleAmount = ($booking->sub_total2 ?? 0) - ($booking->additional_discount ?? 0) + ($booking->extra_charges ?? 0);

                    $totalReceipts = 0;
                    if (!empty($request->receipt_amount) && is_array($request->receipt_amount)) {
                        foreach ($request->receipt_amount as $amt) {
                            $amt = (float)$amt;
                            if ($amt > 0) $totalReceipts += $amt;
                        }
                    }

                    CustomerLedger::create([
                        'customer_id'      => $booking->customer_id,
                        'admin_or_user_id' => auth()->id(),
                        'opening_balance'  => $customerOpeningBalance,
                        'previous_balance' => $previousBalance,
                        'total_debit'      => $saleAmount,
                        'total_credit'     => $totalReceipts,
                        'closing_balance'  => $closingBalance,
                    ]);
                }

                // Process Sale Items, Partial Dispatch & Remaining Quantities
                $dcItems = [];
                $dispatchMap = $request->input('dispatch_qty', []);

                foreach ($booking->items as $it) {
                    $pid = $it->product_id;
                    $fullSaleQty = (float)$it->sales_qty;
                    
                    // User specified dispatch quantity for this item
                    $dispatchQty = isset($dispatchMap[$pid]) ? (float)$dispatchMap[$pid] : $fullSaleQty;
                    $dispatchQty = max(0, min($fullSaleQty, $dispatchQty));
                    $remainingQty = $fullSaleQty - $dispatchQty;

                    // Save SaleItem for full sale record
                    $saleItem = SaleItem::create([
                        'invoice_no'       => $sale->invoice_no,
                        'branch_id'        => $sale->branch_id,
                        'sale_id'          => $sale->id,
                        'warehouse_id'     => $warehouseId,
                        'product_id'       => $pid,
                        'sales_qty'        => $fullSaleQty,
                        'retail_price'     => $it->retail_price,
                        'discount_percent' => (float)($it->discount_percent ?? 0),
                        'discount_amount'  => (float)($it->discount_amount ?? 0),
                        'amount'           => $it->amount,
                    ]);

                    $product = Product::find($pid);
                    $productName = $product?->item_name ?? "Product #{$pid}";
                    $itemCode = $product?->item_code ?? '';
                    $unit = $product?->unit ?? 'Pcs';

                    // 1. Stock Deduction ONLY for current dispatched quantity
                    if ($dispatchQty > 0) {
                        // Deduct from WarehouseStock
                        $whStock = WarehouseStock::lockForUpdate()
                            ->where('product_id', $pid)
                            ->where('branch_id', $branchId)
                            ->where(function($q) use ($warehouseId) {
                                if ($warehouseId) {
                                    $q->where('warehouse_id', $warehouseId);
                                } else {
                                    $q->whereNull('warehouse_id')->orWhere('warehouse_id', 0);
                                }
                            })
                            ->first();

                        if (!$whStock) {
                            $whStock = WarehouseStock::create([
                                'product_id'   => $pid,
                                'branch_id'    => $branchId,
                                'warehouse_id' => $warehouseId,
                                'quantity'     => 0,
                            ]);
                        }
                        $whStock->quantity -= $dispatchQty;
                        $whStock->save();

                        // Deduct from overall Stock (branch level)
                        $bStock = Stock::lockForUpdate()
                            ->where('product_id', $pid)
                            ->where('branch_id', $branchId)
                            ->first();

                        if (!$bStock) {
                            $bStock = Stock::create([
                                'product_id' => $pid,
                                'branch_id'  => $branchId,
                                'qty'        => 0,
                            ]);
                        }
                        $bStock->qty -= $dispatchQty;
                        $bStock->save();

                        // Track Stock Movement
                        StockMovement::create([
                            'branch_id'     => $sale->branch_id ?? ($branchId ?? 1),
                            'product_id'    => $pid,
                            'type'          => 'out',
                            'qty'           => $dispatchQty,
                            'ref_type'      => 'SALE_PARTIAL',
                            'ref_id'        => $sale->id,
                            'ref_uuid'      => $booking->invoice_no,
                            'is_auto_pluck' => 1,
                            'note'          => 'Partial Sale ' . $booking->invoice_no . ($warehouseId ? ' (Warehouse: ' . $warehouseId . ')' : ' (Branch Stock)'),
                        ]);

                        StockAlertService::checkAndCreateAlert($pid, $warehouseId);

                        // Collect item for initial Delivery Challan
                        $dcItems[] = [
                            'sale_item_id' => $saleItem->id,
                            'product_id'   => $pid,
                            'product_name' => $productName,
                            'item_code'    => $itemCode,
                            'qty'          => $dispatchQty,
                            'warehouse_id' => $warehouseId,
                            'retail_price' => (float)$it->retail_price,
                            'amount'       => $dispatchQty * (float)$it->retail_price,
                        ];
                    }

                    // 2. Track Remaining Quantity in customer_remaining
                    if ($remainingQty > 0) {
                        CustomerRemaining::create([
                            'sale_id'           => $sale->id,
                            'customer_id'       => $sale->customer_id,
                            'sub_customer_name' => $sale->sub_customer ?? null,
                            'product_id'        => $pid,
                            'warehouse_id'      => $warehouseId,
                            'remaining_qty'     => $remainingQty,
                            'unit'              => $unit,
                            'item_code'         => $itemCode,
                            'product_name'      => $productName,
                            'status'            => $dispatchQty > 0 ? 'partial' : 'pending',
                            'created_by'        => auth()->id(),
                            'updated_by'        => auth()->id(),
                        ]);
                    }
                }

                // Create Delivery Challan (WarehouseOrder) for current partial dispatch
                if (!empty($dcItems)) {
                    $branchForCounter = Branch::lockForUpdate()->find($branchId) ?? Branch::lockForUpdate()->first();
                    $branchForCounter->dc_counter = ($branchForCounter->dc_counter ?? 0) + 1;
                    $branchForCounter->save();
                    $dcNo = 'DC-' . str_pad($branchForCounter->dc_counter, 4, '0', STR_PAD_LEFT);

                    $warehouseOrder = WarehouseOrder::create([
                        'dc_no'                  => $dcNo,
                        'warehouse_id'           => $warehouseId,
                        'delivery_location_type' => $deliveryLocationType ?? ($warehouseId ? 'warehouse' : 'branch'),
                        'branch_id'              => $sale->branch_id ?? $branchId,
                        'customer_id'            => $sale->customer_id,
                        'sale_id'                => $sale->id,
                        'status'                 => 'pending',
                        'remarks'                => 'Partial Delivery DC for Sale ' . $sale->invoice_no,
                        'prepared_by'            => auth()->user()->name ?? null,
                        'created_by'             => auth()->id(),
                        'updated_by'             => auth()->id(),
                        'items'                  => $dcItems,
                    ]);

                    Log::info('Partial Sale DC created', ['dc_no' => $dcNo, 'sale_id' => $sale->id]);
                }

                // Accounts update for Sales Head
                $salesHead = AccountHead::where('name', 'like', '%Sales%')->first();
                if ($salesHead) {
                    $saleAccount = Account::lockForUpdate()->where('head_id', $salesHead->id)->first();
                }

                // Process Receipts (if advance payments provided)
                if (!empty($request->receipt_account_id) && is_array($request->receipt_account_id)) {
                    $rvNo = 'RV-' . time();
                    $receiptRowAccounts = [];
                    $receiptRowAmounts = [];

                    foreach ($request->receipt_account_id as $i => $accId) {
                        $amt = (float)($request->receipt_amount[$i] ?? 0);
                        if ($amt > 0 && !empty($accId) && is_numeric($accId)) {
                            $receiptRowAccounts[] = (int)$accId;
                            $receiptRowAmounts[] = $amt;

                            $targetAcc = Account::lockForUpdate()->find($accId);
                            if ($targetAcc) {
                                \App\Services\VoucherService::postLedgerEntry(
                                    (int)$accId,
                                    'receipt',
                                    $sale->invoice_no ?? $rvNo,
                                    null,
                                    Carbon::today()->toDateString(),
                                    "Sale Receipt — Invoice #" . ($sale->invoice_no ?? $rvNo),
                                    $amt,
                                    0,
                                    auth()->id()
                                );
                            }
                        }
                    }

                    if (!empty($receiptRowAmounts)) {
                        ReceiptsVoucher::create([
                            'rvid'            => $rvNo,
                            'reference_no'    => $sale->invoice_no,
                            'booking_id'      => $booking->id,
                            'type'            => 'SALE_RECEIPT',
                            'total_amount'    => array_sum($receiptRowAmounts),
                            'amount'          => json_encode($receiptRowAmounts),
                            'row_account_id'  => json_encode($receiptRowAccounts),
                            'processed'       => true,
                            'admin_or_user_id'=> auth()->id(),
                        ]);
                    }
                }

                // Mark booking as posted
                $booking->update([
                    'is_posted' => 1,
                    'posted_at' => now(),
                    'status'    => 'sale',
                ]);

                return response()->json([
                    'ok'          => true,
                    'success'     => true,
                    'sale_id'     => $sale->id,
                    'dc_id'       => $sale->id,
                    'invoice_url' => route('sales.dc', $sale->id),
                    'msg'         => 'Partial Delivery Sale posted successfully!',
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Partial sale post failed', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $status = 422;
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                $status = $e->getStatusCode();
            }
            return response()->json(['ok' => false, 'error' => $e->getMessage()], $status);
        }
    }

    public function ajaxSave(Request $request)
    {
        // handle ajax payload — DO NOT return early (previous debug return removed)
        return DB::transaction(function () use ($request) {

            /* ================= UPDATE / CREATE BOOKING ================= */
            if ($request->filled('booking_id')) {

                $booking = ProductBooking::findOrFail($request->booking_id);

                ProductBookingItem::where('booking_id', $booking->id)->delete();
                ReceiptsVoucher::where('reference_no', $booking->invoice_no)->delete();
            } else {

                /* ================= DETERMINE BRANCH FIRST ================= */
                // Determine branch: if super admin, allow request value; otherwise use user's branch
                $branchId = 1;
                if (Auth::check()) {
                    $user = Auth::user();
                    if ($user->hasRole('super admin')) {
                        $branchId = (int) ($request->input('branch_id') ?? $user->branch_id ?? 1);
                    } else {
                        $branchId = (int) ($user->branch_id ?? 1);
                    }
                } else {
                    $branchId = (int) ($request->input('branch_id') ?? 1);
                }

                /* ================= CREATE BOOKING WITH INDEPENDENT COUNTER ================= */
                // IMPORTANT: Bookings have INDEPENDENT counter from Sales
                // Each branch has its own counter (branch.booking_counter)
                // Bookings use: BINV-0001, BINV-0002, BINV-0003, etc.
                // Sales use: INV-0001, INV-0002, INV-0003, etc. (separate)
                
                $invoiceNo = null;
                $maxRetries = 10;
                $attempt = 0;
                
                while (!$invoiceNo && $attempt < $maxRetries) {
                    $attempt++;
                    try {
                        if ($branchId) {
                            $branch = Branch::lockForUpdate()->find($branchId);
                            if ($branch) {
                                $branch->booking_counter = ((int) ($branch->booking_counter ?? 0)) + 1;
                                $branch->save();
                                
                                $candidateInvoice = 'BINV-' . str_pad($branch->booking_counter, 4, '0', STR_PAD_LEFT);
                                
                                // CRITICAL: Check if this invoice_no exists ANYWHERE (unique constraint is global)
                                $exists = ProductBooking::where('invoice_no', $candidateInvoice)
                                    ->exists();
                                
                                if (!$exists) {
                                    $invoiceNo = $candidateInvoice;
                                    Log::info('Generated booking invoice', ['branch_id' => $branchId, 'invoice_no' => $invoiceNo, 'counter' => $branch->booking_counter, 'attempt' => $attempt]);
                                } else {
                                    // Duplicate detected globally, increment and retry
                                    Log::warning('BINV already exists globally, incrementing counter', ['invoice_no' => $candidateInvoice, 'branch_id' => $branchId, 'counter' => $branch->booking_counter, 'attempt' => $attempt]);
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to generate booking counter', ['branch_id' => $branchId, 'error' => $e->getMessage(), 'attempt' => $attempt]);
                    }
                }
                
                // Fallback: find next available BINV globally
                if (!$invoiceNo) {
                    $maxBookingId = ProductBooking::whereRaw("invoice_no LIKE 'BINV-%'")->max('id') ?? 0;
                    $invoiceNo = 'BINV-' . str_pad($maxBookingId + 1, 4, '0', STR_PAD_LEFT);
                    
                    // Double-check this doesn't exist
                    $counter = 1;
                    while(ProductBooking::where('invoice_no', $invoiceNo)->exists() && $counter < 1000) {
                        $invoiceNo = 'BINV-' . str_pad($maxBookingId + $counter, 4, '0', STR_PAD_LEFT);
                        $counter++;
                    }
                    
                    Log::warning('Using fallback BINV after retries exhausted', ['invoice_no' => $invoiceNo, 'branch_id' => $branchId, 'attempts' => $attempt]);
                }

                $booking = new ProductBooking();
                $booking->invoice_no = $invoiceNo;
                $booking->branch_id = $branchId;
            }

            /* ================= SAVE HEADER ================= */
            // Determine branch: if super admin, allow request value; otherwise use user's branch
            $branchId = 1;
            if (Auth::check()) {
                $user = Auth::user();
                if ($user->hasRole('super admin')) {
                    $branchId = (int) ($request->input('branch_id') ?? $user->branch_id ?? 1);
                } else {
                    $branchId = (int) ($user->branch_id ?? 1);
                }
            } else {
                $branchId = (int) ($request->input('branch_id') ?? 1);
            }

            $booking->branch_id = $branchId;

            $booking->manual_invoice   = $request->input('manual_invoice', $request->input('Invoice_main', $booking->manual_invoice));
            $booking->party_type       = $request->partyType;
            $booking->customer_id      = $request->customer_id;

            // If customer is a walking/customer typed manually, store the display name
            if (($request->input('partyType') ?? '') === 'walking') {
                // `customer` hidden input is populated from `customerDisplay` on the form
                $booking->customer_name = $request->input('customer') ?? $request->input('customer_display');
            }
            $booking->address          = $request->address;
            $booking->tel              = $request->tel;
            $booking->remarks          = $request->input('remarks', $request->input('reference', $booking->remarks));
            $booking->salesman_id      = $request->salesman_id;
            $booking->sub_total1       = $request->subTotal1 ?? 0;
            $booking->sub_total2       = $request->subTotal2 ?? 0;
            $booking->additional_discount = $request->additional_discount ?? 0;
            $booking->extra_charges  = $request->extra_charges ?? 0;
            $booking->previous_balance = $request->previousBalance ?? 0;
            $booking->total_balance    = $request->totalBalance ?? 0;
            $booking->status    = 'pending';
            $booking->notify_me         = $request->notify_me ?? 0;

            $booking->discount_percent = 0;
            $booking->discount_amount = 0;

            $booking->quantity = 0;

            // ✅ Support user-selected custom sale date (past or today)
            $selectedDate = $request->input('sale_date') ?? $request->input('Invoice_date') ?? null;
            if (!empty($selectedDate)) {
                try {
                    $dt = Carbon::parse($selectedDate)->setTime(now()->hour, now()->minute, now()->second);
                    $booking->created_at = $dt;
                    $booking->updated_at = $dt;
                } catch (\Exception $e) {}
            }

            $booking->save();

            /* ================= SAVE ITEMS ================= */
            $totalQty = 0;

            foreach ($request->product_id ?? [] as $i => $productId) {

                $qty = (float) ($request->sales_qty[$i] ?? 0);
                if (!$productId || $qty <= 0) continue;

                // Extract warehouse_id / location if provided
                $rawWh = null;
                if (!empty($request->warehouse_id)) {
                    if (is_array($request->warehouse_id)) {
                        $rawWh = $request->warehouse_id[$i] ?? $request->warehouse_id[$productId] ?? null;
                    } else {
                        $rawWh = $request->warehouse_id;
                    }
                }
                if (!$rawWh && $request->filled('global_warehouse_id')) {
                    $rawWh = $request->global_warehouse_id;
                }

                $warehouseId = null;
                if ($rawWh) {
                    if (is_string($rawWh) && str_starts_with($rawWh, 'warehouse_')) {
                        $warehouseId = (int) str_replace('warehouse_', '', $rawWh);
                    } elseif (is_string($rawWh) && (str_starts_with($rawWh, 'branch_') || $rawWh === 'branch' || $rawWh === 'shop')) {
                        $warehouseId = null; // NULL represents Branch / Shop stock in warehouse_stocks table
                    } elseif (is_numeric($rawWh) && (int) $rawWh > 0) {
                        $warehouseId = (int) $rawWh;
                    }
                }

                ProductBookingItem::create([
                    'invoice_no' => $booking->invoice_no,
                    'booking_id' => $booking->id,
                    'branch_id'  => $branchId,
                    'product_id' => $productId,
                    'watt' => (float) ($request->input("watt.$i", is_array($request->input('watt')) ? ($request->input('watt')[$i] ?? 0) : 0)),
                    'sales_qty' => $qty,
                    'retail_price' => $request->retail_price[$i] ?? 0,
                    'discount_amount' => $request->discount_amount[$i] ?? 0,
                    'discount_percent' => $request->discount_percentage[$i] ?? 0,
                    'discount_type' => $request->discount_type[$i] ?? 'percent',
                    'amount' => $request->sales_amount[$i] ?? 0,
                    'warehouse_id' => $warehouseId,
                ]);
            }


            $booking->quantity = $totalQty;
            $booking->save();

            /* ================= SAVE RECEIPTS ================= */
            // NOTE: receipts are created later during posting (ajaxPost)
            // to ensure all writes for posting are performed inside a single
            // DB transaction. Do not persist receipts here to avoid partial
            // commits if posting fails.

            return response()->json([
                'ok'         => true,
                'success'    => true,
                'id'         => $booking->id,
                'booking_id' => $booking->id,
                'invoice_no' => $booking->invoice_no
            ]);
        });
    }

    public function getWarehousesByProducts(Request $request)
    {
        $productIds = $request->product_ids; // array from query string
        if (empty($productIds) || !is_array($productIds)) return response()->json([]);

        // ── ERP: Role-Based Warehouse Data Security ──────────────────────
        // Determine which warehouses this user is allowed to see.
        // Super Admin → all; Branch Admin → branch warehouses; Others → assigned only
        $user = Auth::user();
        $allowedWarehouseIds = null; // null = no restriction

        if (!$user->hasRole('super admin')) {
            $isBranchAuthority = $user->hasRole('branch admin') || $user->hasRole('admin') || $user->hasRole('branch manager') || $user->hasRole('manager');
            if ($isBranchAuthority) {
                // Branch Authority: all warehouses mapped to their branch in branch_warehouse
                $branch = \App\Models\Branch::with('warehouses')->find($user->branch_id);
                $allowedWarehouseIds = $branch ? $branch->warehouses()->pluck('warehouses.id')->toArray() : [];
            } else {
                // Other staff: only assigned warehouses, fallback to branch warehouses
                $assigned = $user->assignedWarehouseIds();
                if (!empty($assigned)) {
                    $allowedWarehouseIds = $assigned;
                } else {
                    $branch = \App\Models\Branch::with('warehouses')->find($user->branch_id);
                    $allowedWarehouseIds = $branch ? $branch->warehouses()->pluck('warehouses.id')->toArray() : [];
                }
            }
        }
        // ─────────────────────────────────────────────────────────────────

        // Fetch warehouse stocks for the requested products
        $query = \App\Models\WarehouseStock::whereIn('product_id', $productIds)
            ->where('quantity', '>', 0);

        if ($user && $user->branch_id && !$user->hasRole('super admin')) {
            $query->where('branch_id', $user->branch_id);
        }

        // Apply warehouse filter if not super admin
        if ($allowedWarehouseIds !== null) {
            $query->whereIn('warehouse_id', $allowedWarehouseIds);
        }

        $rows = $query->get(['warehouse_id', 'product_id', 'quantity']);

        // group by product_id
        $grouped = $rows->groupBy('product_id');

        $response = [];
        foreach ($productIds as $pid) {
            $product = \App\Models\Product::find($pid);
            $warehouses = ($grouped[$pid] ?? collect())->map(function ($r) {
                $name = \App\Models\Warehouse::where('id', $r->warehouse_id)->value('warehouse_name');
                return [
                    'warehouse_id' => $r->warehouse_id,
                    'warehouse_name' => $name,
                    'quantity' => $r->quantity,
                ];
            })->values();

            $response[] = [
                'product_id' => $pid,
                'product_name' => $product?->item_name ?? 'Product ' . $pid,
                'warehouses' => $warehouses,
            ];
        }

        return response()->json($response);
    }







    public function getCustomerData($id, Request $request)
    {
        $type = strtolower($request->query('type', 'customer'));

        if ($type === 'vendor') {
            // Fetch Vendor data
            $v = Vendor::find($id);
            if (!$v) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            return response()->json([
                'address' => $v->address,
                'mobile' => $v->phone, // assuming 'phone' field for vendors
                'remarks' => '', // No remarks for vendors
                'previous_balance' => 0, // Vendors may not have balance logic
            ]);
        }

        // Default: Fetch Customer data (including walking)
        $c = Customer::find($id);
        if (!$c) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        // Retrieve the latest ledger entry for the customer
        $latestLedger = CustomerLedger::where('customer_id', $id)->latest()->first();

        // If a ledger entry exists, use its closing_balance; otherwise, use opening_balance
        $previous_balance = $latestLedger ? (float)$latestLedger->closing_balance : (float)($c->opening_balance ?? 0);

        return response()->json([
            'id' => $c->id,
            'customer_id' => $c->customer_id,
            'customer_name' => $c->customer_name,
            'filer_type' => $c->filer_type,
            'customer_type' => $c->customer_type,
            'address' => $c->address,
            'mobile' => $c->mobile,
            'remarks' => $c->remarks ?? '',
            'previous_balance' => $previous_balance,
            'closing_balance' => $previous_balance,
            'credit_upto' => $c->credit_upto,
            'credit_limit' => $c->credit_limit,
            'opening_balance' => (float)($c->opening_balance ?? 0),
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    //////////////
    // public function index  (Request $request)
    // {
    //     $type = $request->type ?? 'customer';

    //     $customers = Customer::where('type', $type)
    //         ->orderBy('name')
    //         ->get(['id', 'name', 'mobile']);
    //         dd($customers);

    //     return response()->json($customers);
    // }

    // // 🔹 Single customer detail
    // public function show($id, Request $request)
    // {
    //     $type = $request->type ?? 'customer';

    //     $customer = Customer::where('id', $id)
    //         ->where('type', $type)
    //         ->firstOrFail();

    //     return response()->json([
    //         'address' => $customer->address,
    //         'mobile' => $customer->mobile,
    //         'remarks' => $customer->remarks,
    //         'previous_balance' => $customer->previous_balance,
    //     ]);
    // }



    ////////////
public function search(Request $request)
{
    $query = Sale::with(['customer','saleItems.product','branch']);

    $requestedBranch = $request->query('branch_id');

    if (Auth::check() && !Auth::user()->hasRole('super admin')) {
        $branchId = (int) (Auth::user()->branch_id ?? 0);
    } else {
        $branchId = ($requestedBranch && $requestedBranch !== 'all') ? (int) $requestedBranch : null;
    }

    if ($branchId) {
        $query->where('branch_id', $branchId);
    }

    if ($request->filled('invoice')) {
        $query->where('invoice_no', 'LIKE', "%{$request->invoice}%");
    }

    $sales = $query->orderByDesc('id')->get();

    return view('admin_panel.sale.partials.sales_rows', compact('sales'))->render();
}



   public function showdc()
    {
        $query = Sale::with(['customer', 'saleItems.product', 'branch'])->orderBy('id', 'desc');

        if (Auth::check()) {
            if (!Auth::user()->hasRole('super admin')) {
                $branchId = (int) (Auth::user()->branch_id ?? 0);
                $query->where('branch_id', $branchId);
            } elseif (request()->filled('branch_id') && request('branch_id') !== 'all') {
                $query->where('branch_id', (int) request('branch_id'));
            }
        }

        $sales = $query->get();
     
        // return response()->json($sales);
    
        return view('admin_panel.sale.DCindex', compact('sales'));
    }


   public function finddcview()
{
    $sales = collect(); // empty collection

    return view('admin_panel.sale.finddc', compact('sales'));
}

public function finddc($invoice)
{
    $query = Sale::with(['customer.branch', 'saleItems.product', 'branch'])
        ->where('invoice_no', $invoice);

    if (Auth::check() && !Auth::user()->hasRole('super admin')) {
        $branchId = (int) (Auth::user()->branch_id ?? 0);
        $query->where('branch_id', $branchId);
    }

    $sales = $query->latest()->get();

    return view('admin_panel.sale.partials.sales_rows', compact('sales'))->render();
}



    public function index()
    {
        // ✅ Eager load booking data to check draft_posted status
        $query = Sale::with([
            'customer.branch', 
            'saleItems.product'
        ])->orderBy('id', 'desc');

        if (Auth::check()) {
            if (!Auth::user()->hasRole('super admin')) {
                $branchId = (int) (Auth::user()->branch_id ?? 0);
                $query->where('branch_id', $branchId);
            } elseif (request()->filled('branch_id') && request('branch_id') !== 'all') {
                $query->where('branch_id', (int) request('branch_id'));
            }
        }

        $sales = $query->get();

        // ✅ Efficient bulk calculation for Delivered & Remaining Qty per sale
        $saleIds = $sales->pluck('id')->toArray();

        if (!empty($saleIds)) {
            // Bulk fetch gatepasses for all loaded sales
            $gatepassesBySale = DB::table('outward_gatepasses')
                ->join('warehouse_orders', 'outward_gatepasses.order_id', '=', 'warehouse_orders.id')
                ->whereIn('warehouse_orders.sale_id', $saleIds)
                ->select('warehouse_orders.sale_id', 'outward_gatepasses.items')
                ->get()
                ->groupBy('sale_id');

            // Bulk fetch DCs (warehouse_orders)
            $dcsBySale = DB::table('warehouse_orders')
                ->whereIn('sale_id', $saleIds)
                ->select('sale_id', 'id')
                ->get()
                ->groupBy('sale_id');

            // Bulk fetch pending/partial customer_remaining
            $pendingRemainingSales = DB::table('customer_remaining')
                ->whereIn('sale_id', $saleIds)
                ->whereIn('status', ['pending', 'partial'])
                ->pluck('sale_id')
                ->flip()
                ->toArray();

            foreach ($sales as $sale) {
                $totalOrdered = $sale->saleItems ? (float)$sale->saleItems->sum('sales_qty') : 0;
                
                $saleGps = $gatepassesBySale->get($sale->id, collect());
                $hasDcs = $dcsBySale->has($sale->id);
                $hasPendingRemaining = isset($pendingRemainingSales[$sale->id]);

                $isRegularDirect = ($sale->status !== 'draft_posted' && $saleGps->isEmpty() && !$hasDcs && !$hasPendingRemaining);

                if ($isRegularDirect) {
                    $totalDelivered = $totalOrdered;
                    $totalRemaining = 0;
                } else {
                    $totalDelivered = 0;
                    foreach ($saleGps as $gp) {
                        $gpItems = json_decode($gp->items, true) ?? [];
                        foreach ($gpItems as $gpi) {
                            $totalDelivered += (float)($gpi['qty'] ?? 0);
                        }
                    }
                    $totalRemaining = max(0, $totalOrdered - $totalDelivered);
                }

                $sale->total_ordered_qty = $totalOrdered;
                $sale->total_delivered_qty = $totalDelivered;
                $sale->total_remaining_qty = $totalRemaining;
            }
        }
        
        return view('admin_panel.sale.index', compact('sales'));
    }            

    public function addsale()
    {
        // Branch-aware listing for non-super admins. Super admin sees everything.
        $isSuper = Auth::check() && Auth::user()->hasRole('super admin');
        $branchId = Auth::check() ? Auth::user()->branch_id : null;

        $products = Product::when(!$isSuper && $branchId, function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })->get();

        $customer = Customer::when(!$isSuper && $branchId, function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })->get();

        $branches = Branch::with('warehouses')->get();

        // Prepare per-branch invoice counters so the view/JS can compute next invoice
        $branchCounters = Branch::pluck('invoice_counter', 'id')->toArray();

        // Determine default branch for initial invoice shown in the form:
        // - non-super users => their branch
        // - super admin => first branch in list (the select will default to first option)
        $defaultBranchId = null;
        if (Auth::check() && !Auth::user()->hasRole('super admin')) {
            $defaultBranchId = Auth::user()->branch_id ?? null;
        } else {
            $defaultBranchId = optional($branches->first())->id ?? null;
        }

        // Build branch-to-warehouses mapping for instant client-side dropdown filtering
        $branchWarehouseMap = [];
        foreach ($branches as $b) {
            $branchWarehouseMap[$b->id] = $b->warehouses->map(function ($w) {
                return [
                    'id'             => $w->id,
                    'name'           => $w->warehouse_name ?? $w->name,
                    'warehouse_name' => $w->warehouse_name ?? $w->name,
                ];
            })->values()->toArray();
        }

        // Active branch assigned warehouses
        $activeBranch = $branches->firstWhere('id', $defaultBranchId) ?? $branches->first();
        $warehouse = ($activeBranch && $activeBranch->warehouses) ? $activeBranch->warehouses : collect();
        $allWarehouses = $warehouse;

        // ✅ BRANCH-AWARE ACCOUNTS: Accounts filtered by default/active branch
        $accounts = Account::when($defaultBranchId, function ($q) use ($defaultBranchId) {
            $q->where('branch_id', $defaultBranchId);
        })->get();

        // ✅ BRANCH-AWARE SALESMEN: Salesmen filtered by default/active branch
        $salesmen = SalesOfficer::when($defaultBranchId, function ($q) use ($defaultBranchId) {
            $q->where('branch_id', $defaultBranchId);
        })->get();

        if ($defaultBranchId && isset($branchCounters[$defaultBranchId])) {
            $next = ((int) $branchCounters[$defaultBranchId]) + 1;
            $nextInvoiceNumber = 'INV-' . str_pad($next, 4, '0', STR_PAD_LEFT);
        } else {
            // Fallback: keep existing generator
            $nextInvoiceNumber = Sale::generateInvoiceNo();
        }

        // Get warehouse_stocks for client-side validation
        $warehouseStocks = WarehouseStock::all()->toArray();

        $saleSettings = SaleSetting::getSettings();

        return view('admin_panel.sale.add_sale222', compact('warehouse', 'allWarehouses', 'customer', 'accounts', 'nextInvoiceNumber', 'products', 'branches', 'branchCounters', 'warehouseStocks', 'salesmen', 'saleSettings', 'defaultBranchId', 'branchWarehouseMap'));
    }

    public function getBranchSalesmen($branchId)
    {
        $salesmen = SalesOfficer::where('branch_id', $branchId)->get();
        return response()->json($salesmen);
    }

    public function getBranchAccounts($branchId)
    {
        $accounts = Account::where('branch_id', $branchId)->get();
        return response()->json($accounts);
    }

    public function getBranchInvoiceNo($branchId)
    {
        $branch = Branch::find($branchId);
        $counter = $branch ? ((int) ($branch->invoice_counter ?? 0)) : 0;
        $next = $counter + 1;
        $invoiceNo = 'INV-' . str_pad($next, 4, '0', STR_PAD_LEFT);
        return response()->json([
            'status' => 'success',
            'branch_id' => (int)$branchId,
            'invoice_no' => $invoiceNo,
            'counter' => $counter,
        ]);
    }

    public function searchpname(Request $request)
    {
        $q = $request->get('q');

        $products = Product::with(['brand'])
            ->where(function ($query) use ($q) {
                $query->where('item_name', 'like', "%{$q}%")
                    ->orWhere('item_code', 'like', "%{$q}%")
                    ->orWhere('barcode_path', 'like', "%{$q}%");
            })
            ->when(Auth::check() && !Auth::user()->hasRole('super admin'), function ($q2) {
                $branchId = Auth::user()->branch_id ?? 0;
                $q2->where('branch_id', $branchId);
            })
            ->get();

        // Append stock quantity for each product
        $branchId = Auth::check() ? (Auth::user()->branch_id ?? null) : null;
        $products = $products->map(function ($product) use ($branchId) {
            $stockQuery = WarehouseStock::where('product_id', $product->id);
            if ($branchId) {
                $stockQuery->where('branch_id', $branchId);
            }
            $product->available_stock = $stockQuery->sum('quantity');
            return $product;
        });

        return response()->json($products);
    }

    /**
     * Check available stock for a product (AJAX).
     * Returns: { available: int }
     */
    public function checkStock(Request $request)
    {
        $productId = $request->get('product_id');
        $branchId  = $request->get('branch_id') ?: (Auth::check() ? (Auth::user()->branch_id ?? null) : null);
        $location  = $request->get('location_id') ?: $request->get('warehouse_id');

        // Overall stock across entire system
        $overallStock = (float) WarehouseStock::where('product_id', $productId)->sum('quantity');
        if ($overallStock == 0) {
            $overallStock = (float) Stock::where('product_id', $productId)->sum('qty');
        }

        // Branch total stock
        $branchTotalStock = (float) WarehouseStock::where('product_id', $productId)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('quantity');
        if ($branchTotalStock == 0) {
            $branchTotalStock = (float) Stock::where('product_id', $productId)
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->sum('qty');
        }

        // Location specific stock
        $locationStock = $branchTotalStock;
        if ($location) {
            if (str_starts_with($location, 'branch_')) {
                $targetBranch = (int) str_replace('branch_', '', $location);
                $locationStock = (float) WarehouseStock::where('product_id', $productId)
                    ->where('branch_id', $targetBranch)
                    ->whereNull('warehouse_id')
                    ->sum('quantity');
                if ($locationStock == 0) {
                    $locationStock = (float) Stock::where('product_id', $productId)
                        ->where('branch_id', $targetBranch)
                        ->sum('qty');
                }
            } elseif ($location === 'branch' || $location === 'shop') {
                $locationStock = (float) WarehouseStock::where('product_id', $productId)
                    ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                    ->whereNull('warehouse_id')
                    ->sum('quantity');
                if ($locationStock == 0) {
                    $locationStock = (float) Stock::where('product_id', $productId)
                        ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                        ->sum('qty');
                }
            } else {
                $whId = (int) str_replace('warehouse_', '', $location);
                $locationStock = (float) WarehouseStock::where('product_id', $productId)
                    ->where('warehouse_id', $whId)
                    ->sum('quantity');
            }
        }

        // Location breakdown map for all branches and warehouses
        $locationBreakdown = [];
        if ($productId) {
            $bStocks = WarehouseStock::where('product_id', $productId)
                ->whereNull('warehouse_id')
                ->select('branch_id', DB::raw('SUM(quantity) as total_qty'))
                ->groupBy('branch_id')
                ->pluck('total_qty', 'branch_id')
                ->toArray();
            foreach ($bStocks as $bId => $qty) {
                $locationBreakdown['branch_' . $bId] = (float) $qty;
            }

            $wStocks = WarehouseStock::where('product_id', $productId)
                ->whereNotNull('warehouse_id')
                ->select('warehouse_id', DB::raw('SUM(quantity) as total_qty'))
                ->groupBy('warehouse_id')
                ->pluck('total_qty', 'warehouse_id')
                ->toArray();
            foreach ($wStocks as $wId => $qty) {
                $locationBreakdown['warehouse_' . $wId] = (float) $qty;
            }

            // Also include branches from stocks table if not in warehouse_stocks
            $shopStocks = Stock::where('product_id', $productId)
                ->select('branch_id', DB::raw('SUM(qty) as total_qty'))
                ->groupBy('branch_id')
                ->pluck('total_qty', 'branch_id')
                ->toArray();
            foreach ($shopStocks as $bId => $qty) {
                if (!isset($locationBreakdown['branch_' . $bId])) {
                    $locationBreakdown['branch_' . $bId] = (float) $qty;
                }
            }
        }

        return response()->json([
            'available'          => $overallStock,
            'overall_stock'      => $overallStock,
            'branch_stock'       => $branchTotalStock,
            'location_stock'     => $locationStock,
            'location_breakdown' => $locationBreakdown,
        ]);
    }

    public function store(Request $request)
    {
        $isBooking = $request->has('booking');
        if ($isBooking) {
            // Normalize customer id: form may send numeric id or a display string
            $customerVal = $request->input('customer') ?? $request->input('customer_id') ?? null;
            $customerId = null;
            if (is_numeric($customerVal)) {
                $customerId = (int) $customerVal;
            } elseif (is_string($customerVal) && strlen(trim($customerVal)) > 0) {
                // Try to extract a customer code like CUST-0001
                if (preg_match('/([A-Za-z0-9]+-\d+)/', $customerVal, $m)) {
                    $code = $m[1];
                    $cust = Customer::where('customer_id', $code)->first();
                    if ($cust) $customerId = $cust->id;
                }
                // Fallback: try matching by name (text before separator)
                if (!$customerId) {
                    $parts = preg_split('/[-—–]{1,3}/u', $customerVal);
                    $namePart = trim($parts[0] ?? $customerVal);
                    $cust = Customer::where('customer_name', $namePart)->first();
                    if ($cust) $customerId = $cust->id;
                }
            }

            // Determine branch id for this booking: super-admin may provide branch_id
            $branchId = 1;
            if (Auth::check()) {
                $user = Auth::user();
                if ($user->hasRole('super admin')) {
                    $branchId = (int) ($request->input('branch_id') ?? $user->branch_id ?? 1);
                } else {
                    $branchId = (int) ($user->branch_id ?? 1);
                }
            } else {
                $branchId = (int) ($request->input('branch_id') ?? 1);
            }

            /* ================= GENERATE BOOKING INVOICE (BINV) ================= */
            // IMPORTANT: Bookings have INDEPENDENT counter from Sales
            // Each branch has its own counter (branch.booking_counter)
            // Bookings use: BINV-0001, BINV-0002, BINV-0003, etc.
            
            $invoiceNo = null;
            $maxRetries = 10;
            $attempt = 0;
            
            while (!$invoiceNo && $attempt < $maxRetries) {
                $attempt++;
                try {
                    if ($branchId) {
                        $branch = Branch::lockForUpdate()->find($branchId);
                        if ($branch) {
                            $branch->booking_counter = ((int) ($branch->booking_counter ?? 0)) + 1;
                            $branch->save();
                            
                            $candidateInvoice = 'BINV-' . str_pad($branch->booking_counter, 4, '0', STR_PAD_LEFT);
                            
                            // CRITICAL: Check if this invoice_no exists ANYWHERE (unique constraint is global)
                            $exists = ProductBooking::where('invoice_no', $candidateInvoice)
                                ->exists();
                            
                            if (!$exists) {
                                $invoiceNo = $candidateInvoice;
                                Log::info('Generated booking invoice', ['branch_id' => $branchId, 'invoice_no' => $invoiceNo, 'counter' => $branch->booking_counter, 'attempt' => $attempt]);
                            } else {
                                // Duplicate detected globally, increment and retry
                                Log::warning('BINV already exists globally, incrementing counter', ['invoice_no' => $candidateInvoice, 'branch_id' => $branchId, 'counter' => $branch->booking_counter, 'attempt' => $attempt]);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to generate booking counter', ['branch_id' => $branchId, 'error' => $e->getMessage(), 'attempt' => $attempt]);
                }
            }
            
            // Fallback: find next available BINV globally
            if (!$invoiceNo) {
                $maxBookingId = ProductBooking::whereRaw("invoice_no LIKE 'BINV-%'")->max('id') ?? 0;
                $invoiceNo = 'BINV-' . str_pad($maxBookingId + 1, 4, '0', STR_PAD_LEFT);
                
                // Double-check this doesn't exist
                $counter = 1;
                while(ProductBooking::where('invoice_no', $invoiceNo)->exists() && $counter < 1000) {
                    $invoiceNo = 'BINV-' . str_pad($maxBookingId + $counter, 4, '0', STR_PAD_LEFT);
                    $counter++;
                }
                
                Log::warning('Using fallback BINV after retries exhausted', ['invoice_no' => $invoiceNo, 'branch_id' => $branchId, 'attempts' => $attempt]);
            }

            // Support user-selected custom sale date
            $storeDateInput = $request->input('sale_date') ?? $request->input('Invoice_date') ?? null;
            $storeDateTime = Carbon::now();
            if (!empty($storeDateInput)) {
                try {
                    $storeDateTime = Carbon::parse($storeDateInput)->setTime(now()->hour, now()->minute, now()->second);
                } catch (\Exception $e) {
                    $storeDateTime = Carbon::now();
                }
            }

            $booking = ProductBooking::create([
                'invoice_no' => $invoiceNo,
                'manual_invoice' => $request->input('manual_invoice', $request->input('Invoice_main')),
                'branch_id' => $branchId,
                'customer_id' => $customerId,
                'salesman_id' => $request->salesman_id ?? null,
                'party_type' => $request->input('partyType') ?? null,
                'sub_customer' => $request->customerType,
                'filer_type' => $request->filerType,
                'address' => $request->address,
                'tel' => $request->tel,
                'remarks' => $request->input('remarks', $request->input('reference')),
                'sub_total1' => $request->subTotal1 ?? 0,
                'sub_total2' => $request->subTotal2 ?? 0,
                'discount_percent' => $request->discountPercent ?? 0,
                'discount_amount' => $request->discountAmount ?? 0,
                'previous_balance' => $request->previousBalance ?? 0,
                'total_balance' => $request->totalBalance ?? 0,
                'receipt1' => $request->receipt1 ?? 0,
                'receipt2' => $request->receipt2 ?? 0,
                'final_balance1' => $request->finalBalance1 ?? 0,
                'final_balance2' => $request->finalBalance2 ?? 0,
                'created_at' => $storeDateTime,
                'updated_at' => $storeDateTime,
                // 'weight' => $request->weight ?? null,
            ]);

            $totalQty = 0;

            foreach ($request->product_id ?? [] as $i => $productId) {

                $qty = (float) ($request->sales_qty[$i] ?? 0);
                if (!$productId || $qty <= 0) continue;

                $totalQty += $qty; // ✅ YEH LINE MISSING THI

                ProductBookingItem::create([
                    'invoice_no' => $booking->invoice_no,
                    'booking_id' => $booking->id,
                    'branch_id'  => $branchId,
                    'product_id' => $productId,
                    'sales_qty' => $qty,
                    'retail_price' => $request->retail_price[$i] ?? 0,
                    'discount_amount' => $request->discount_amount[$i] ?? 0,
                    'amount' => $request->sales_amount[$i] ?? 0,
                ]);
            }

            $booking->quantity = $totalQty;
            $booking->save();


            return back()->with('success', 'Booking saved successfully!');
        }

        // Direct Sale (stock minus)
        return DB::transaction(function () use ($request) {
            // Determine branch: super-admin may select branch_id, otherwise use authenticated user's branch
            if (Auth::check()) {
                $user = Auth::user();
                if ($user->hasRole('super admin')) {
                    $branchId = (int) ($request->input('branch_id') ?? $user->branch_id ?? 1);
                } else {
                    $branchId = (int) ($user->branch_id ?? 1);
                }
            } else {
                $branchId = (int) ($request->input('branch_id') ?? 1);
            }

            $branch = null;
            if ($branchId) {
                $branch = Branch::lockForUpdate()->find($branchId);
            }
            if (! $branch) {
                $branch = Branch::lockForUpdate()->first();
            }

            // Ensure counter exists and increment safely under transaction
            $branch->invoice_counter = ((int) ($branch->invoice_counter ?? 0)) + 1;
            $branch->save();

            $invoiceNo = 'INV-' . str_pad($branch->invoice_counter, 4, '0', STR_PAD_LEFT);
            // Normalize customer id for direct sale as well
            $customerVal = $request->input('customer') ?? $request->input('customer_id') ?? null;
            $customerId = null;
            if (is_numeric($customerVal)) {
                $customerId = (int) $customerVal;
            } elseif (is_string($customerVal) && strlen(trim($customerVal)) > 0) {
                if (preg_match('/([A-Za-z0-9]+-\d+)/', $customerVal, $m)) {
                    $code = $m[1];
                    $cust = Customer::where('customer_id', $code)->first();
                    if ($cust) $customerId = $cust->id;
                }
                if (!$customerId) {
                    $parts = preg_split('/[-—–]{1,3}/u', $customerVal);
                    $namePart = trim($parts[0] ?? $customerVal);
                    $cust = Customer::where('customer_name', $namePart)->first();
                    if ($cust) $customerId = $cust->id;
                }
            }

            // ✅ CREDIT LIMIT CHECK
            if ($customerId) {
                $customer = Customer::find($customerId);
                $saleAmount = (float)($request->subTotal1 ?? 0) - (float)($request->discountAmount ?? 0);
                $latestLedger = CustomerLedger::where('customer_id', $customerId)->latest()->first();
                $currentBalance = $latestLedger ? $latestLedger->closing_balance : $customer->opening_balance;

                // Total credit = current balance + new sale
                $totalCredit = $currentBalance + $saleAmount;

                // Check if exceeds credit limit
                if ($customer->credit_limit && $totalCredit > $customer->credit_limit) {
                    return back()->withError(
                        "❌ کریڈٹ حد سے زیادہ ہے! \n" .
                        "موجودہ بقایا: " . number_format($currentBalance, 2) . "\n" .
                        "نیا سیل رقم: " . number_format($saleAmount, 2) . "\n" .
                        "کل کریڈٹ: " . number_format($totalCredit, 2) . "\n" .
                        "کریڈٹ حد: " . number_format($customer->credit_limit, 2)
                    )->withInput();
                }

                // Check credit expiry date
                if ($customer->credit_upto && Carbon::now() > Carbon::parse($customer->credit_upto)) {
                    return back()->withError(
                        "❌ کریڈٹ کی تاریخ ختم ہو گئی ہے! (" . $customer->credit_upto . ")"
                    )->withInput();
                }
            }

            $directSaleDateInput = $request->input('sale_date') ?? $request->input('Invoice_date') ?? null;
            $directSaleDateTime = Carbon::now();
            if (!empty($directSaleDateInput)) {
                try {
                    $directSaleDateTime = Carbon::parse($directSaleDateInput)->setTime(now()->hour, now()->minute, now()->second);
                } catch (\Exception $e) {
                    $directSaleDateTime = Carbon::now();
                }
            }

            $sale = Sale::create([
                'branch_id' => $branch->id,
                'invoice_no' => $invoiceNo,
                'manual_invoice' => $request->input('manual_invoice', $request->input('Invoice_main')),
                'party_type' => $request->input('partyType') ?? null,
                'customer_id' => $customerId ?? ($request->customer ?? null),
                'salesman_id' => $request->salesman_id ?? null,
                'sub_customer' => $request->customerType ?? null,
                'filer_type' => $request->filerType ?? null,
                'address' => $request->address ?? null,
                'tel' => $request->tel ?? null,
                'remarks' => $request->input('remarks', $request->input('reference')),
                'sub_total1' => $request->subTotal1 ?? 0,
                'sub_total2' => $request->subTotal2 ?? 0,
                'discount_percent' => $request->discountPercent ?? 0,
                'discount_amount' => $request->discountAmount ?? 0,
                'previous_balance' => $request->previousBalance ?? 0,
                'total_balance' => $request->totalBalance ?? 0,
                'receipt1' => $request->receipt1 ?? 0,
                'receipt2' => $request->receipt2 ?? 0,
                'final_balance1' => $request->finalBalance1 ?? 0,
                'final_balance2' => $request->finalBalance2 ?? 0,
                'created_at' => $directSaleDateTime,
                'updated_at' => $directSaleDateTime,
                // 'weight' => $request->weight ?? null,
            ]);

            // Persist optional notify_me value on sale (days integer)
            $notifyDays = (int) ($request->input('notify_me') ?? $request->input('notify_days') ?? 0);
            if ($notifyDays > 0) {
                try {
                    $sale->notify_me = $notifyDays;
                    $sale->save();

                    $notifyAt = now()->addDays($notifyDays);
                    Notification::create([
                        'user_id' => auth()->id(),
                        'customer_id' => $customerId,
                        'message' => 'Sale #' . $sale->invoice_no . ' notification scheduled for ' . $notifyAt->toDateString(),
                        'notify_at' => $notifyAt,
                        'is_read' => false,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to create sale notification', ['err' => $e->getMessage()]);
                }
            }

            // Support multiple form structures (add_sale222 sending product_id[] array OR legacy product_name[] / warehouse_name[])
            $productsList = $request->product_id ?? $request->product_name ?? $request->warehouse_name ?? [];
            if (!is_array($productsList)) {
                $productsList = [];
            }

            foreach ($productsList as $i => $itemVal) {
                $productId = is_numeric($itemVal) ? (int) $itemVal : ($request->product_id[$i] ?? $request->input("product_name.$i") ?? null);
                if (!$productId) continue;

                $rawWid = null;
                if (is_array($request->warehouse_id) && isset($request->warehouse_id[$i])) {
                    $rawWid = $request->warehouse_id[$i];
                } elseif (is_array($request->warehouse_name) && isset($request->warehouse_name[$i])) {
                    $rawWid = $request->warehouse_name[$i];
                } else {
                    $rawWid = $request->input('warehouse_id') ?? $request->input('global_warehouse_id') ?? null;
                }

                $itemBranch = $sale->branch_id ?? $branch->id ?? 1;
                $cleanWhId = null;

                if (!empty($rawWid)) {
                    if (is_string($rawWid) && str_starts_with($rawWid, 'branch_')) {
                        $itemBranch = (int) str_replace('branch_', '', $rawWid);
                        $cleanWhId = null;
                    } elseif (is_string($rawWid) && ($rawWid === 'branch' || $rawWid === 'shop')) {
                        $cleanWhId = null;
                    } elseif (is_string($rawWid) && str_starts_with($rawWid, 'warehouse_')) {
                        $cleanWhId = (int) str_replace('warehouse_', '', $rawWid);
                    } elseif (is_numeric($rawWid) && (int) $rawWid > 0) {
                        if (Warehouse::where('id', (int) $rawWid)->exists()) {
                            $cleanWhId = (int) $rawWid;
                        } else {
                            $itemBranch = (int) $rawWid;
                            $cleanWhId = null;
                        }
                    }
                }

                $saleQty = (float) ($request->sales_qty[$i] ?? $request->qty[$i] ?? $request->carton_qty[$i] ?? $request->input("sales-qty.$i", 0));
                if ($saleQty <= 0) continue;

                // Per-location warehouse stock (allow negative)
                $wsQuery = WarehouseStock::where('product_id', $productId)
                    ->where('branch_id', $itemBranch);
                if ($cleanWhId) {
                    $wsQuery->where('warehouse_id', $cleanWhId);
                } else {
                    $wsQuery->whereNull('warehouse_id');
                }
                $ws = $wsQuery->first();

                if ($ws) {
                    $ws->quantity = ($ws->quantity ?? 0) - $saleQty;
                    $ws->save();
                } else {
                    WarehouseStock::create([
                        'branch_id'    => $itemBranch,
                        'warehouse_id' => $cleanWhId,
                        'product_id'   => $productId,
                        'quantity'     => -1 * $saleQty,
                    ]);
                }

                // Global stock via Stock model (allow negative)
                $stockRow = Stock::where('product_id', $productId)
                    ->first();

                if ($stockRow) {
                    $stockRow->qty = ($stockRow->qty ?? 0) - $saleQty;
                    $stockRow->save();
                } else {
                    Stock::create([
                        'branch_id' => $branch->id ?? 1,
                        'product_id' => $productId,
                        'qty' => -1 * $saleQty,
                        'reserved_qty' => 0,
                    ]);
                }

                $retailPrice = (float) ($request->retail_price[$i] ?? $request->input("retail-price.$i", 0));
                $discPercent = (float) ($request->discount_percentage[$i] ?? $request->input("discount-percent.$i", 0));
                $discAmount = (float) ($request->discount_amount[$i] ?? $request->input("discount-amount.$i", 0));
                $salesAmount = (float) ($request->sales_amount[$i] ?? $request->input("sales-amount.$i", 0));

                SaleItem::create([
                    'invoice_no' => $sale->invoice_no,
                    'branch_id' => $sale->branch_id,
                    'sale_id' => $sale->id,
                    'warehouse_id' => $warehouse_id,
                    'product_id' => $productId,
                    'watt' => (float) ($request->input("watt.$i", is_array($request->input('watt')) ? ($request->input('watt')[$i] ?? 0) : 0)),
                    'stock' => (float) ($request->stock[$i] ?? $request->input("stock.$i", 0)),
                    'price_level' => (float) ($request->price[$i] ?? $request->input("price.$i", 0)),
                    'sales_price' => $retailPrice,
                    'sales_qty' => $saleQty,
                    'retail_price' => $retailPrice,
                    'discount_percent' => $discPercent,
                    'discount_amount' => $discAmount,
                    'amount' => $salesAmount,
                ]);
            }

            // If receipts provided for direct sale, create per-account receipt vouchers
            if (!empty($request->receipt_account_id) && is_array($request->receipt_account_id)) {
                $rowAccountIds = [];
                $rowAmounts = [];
                foreach ($request->receipt_account_id as $i => $accId) {
                    $acc = $accId;
                    $amt = (float) ($request->receipt_amount[$i] ?? 0);
                    if ($amt <= 0 || empty($acc) || !is_numeric($acc)) continue;
                    $rowAccountIds[] = (int) $acc;
                    $rowAmounts[] = $amt;
                }

                if (!empty($rowAccountIds)) {
                    // Deduplicate
                    $unique = [];
                    $uniqueAccountIds = [];
                    $uniqueAmounts = [];
                    foreach ($rowAccountIds as $i => $acctId) {
                        $amt = $rowAmounts[$i] ?? ($rowAmounts[0] ?? 0);
                        if ($amt <= 0) continue;
                        $sig = $acctId . '|' . number_format((float)$amt, 2, '.', '');
                        if (isset($unique[$sig])) continue;
                        $unique[$sig] = true;
                        $uniqueAccountIds[] = $acctId;
                        $uniqueAmounts[] = $amt;
                    }

                    foreach ($uniqueAccountIds as $i => $acctId) {
                        $amt = $uniqueAmounts[$i];
                        $rv = ReceiptsVoucher::create([
                            'branch_id' => $sale->branch_id ?? (auth()->user()->branch_id ?? 1),
                            'rvid' => ReceiptsVoucher::generateRVID(auth()->id()),
                            'receipt_date' => $directSaleDateTime->toDateString(),
                            'entry_date' => $directSaleDateTime,
                            'type' => 'SALE_RECEIPT',
                            'party_id' => $customerId,
                            'booking_id' => null,
                            'sale_id' => $sale->id,
                            'tel' => $request->tel ?? null,
                            'remarks' => $request->remarks ?? null,
                            'reference_no' => $sale->invoice_no,
                            'row_account_head' => 'Cash/Bank',
                            'row_account_id' => is_array($acctId) ? json_encode($acctId) : $acctId,
                            'amount' => is_array($amt) ? json_encode($amt) : $amt,
                            'total_amount' => $amt,
                            'processed' => false,
                            'created_at' => $directSaleDateTime,
                            'updated_at' => $directSaleDateTime,
                        ]);

                        // Immediately apply this receipt to account and customer ledger
                        try {
                            $rowAccount = Account::lockForUpdate()->find($acctId);
                            if ($rowAccount) {
                                // Post to Account Ledger
                                \App\Services\VoucherService::postLedgerEntry(
                                    $acctId,
                                    'receipt',
                                    $sale->invoice_no ?? $rv->rvid,
                                    $rv->id,
                                    $directSaleDateTime->toDateString(),
                                    "Sale Receipt — Invoice #" . ($sale->invoice_no ?? '') . ($request->remarks ? " ({$request->remarks})" : ""),
                                    $amt,
                                    0,
                                    auth()->id()
                                );
                            }

                            $custPrev = CustomerLedger::where('customer_id', $customerId)->latest('id')->lockForUpdate()->value('closing_balance') ?? 0;
                            $custNew = $custPrev - $amt;
                            CustomerLedger::create([
                                'customer_id' => $customerId,
                                'admin_or_user_id' => auth()->id(),
                                'previous_balance' => $custPrev,
                                'opening_balance' => 0,
                                'closing_balance' => $custNew,
                                'created_at' => $directSaleDateTime,
                                'updated_at' => $directSaleDateTime,
                            ]);

                            $rv->processed = true;
                            $rv->save();
                        } catch (\Exception $e) {
                            Log::error('Failed to apply direct-sale receipt', ['error' => $e->getMessage(), 'rv' => $rv->id ?? null]);
                        }
                    }
                }
            }

            // ✅ UPDATE CUSTOMER LEDGER WITH SALE AMOUNT
            if ($customerId) {
                $saleAmount = (float)($request->subTotal1 ?? 0) - (float)($request->discountAmount ?? 0);
                $latestLedger = CustomerLedger::where('customer_id', $customerId)->latest()->first();

                $previousBalance = $latestLedger ? $latestLedger->closing_balance : 0;

                // Ledger already adjusted by any direct-sale receipts applied above.
                $newClosingBalance = $previousBalance + $saleAmount;

                CustomerLedger::create([
                    'customer_id'      => $customerId,
                    'admin_or_user_id' => auth()->id(),
                    'transaction_type' => 'Sale',
                    'reference_id'     => (string)$sale->id,
                    'description'      => 'Sale Invoice #' . $sale->invoice_no,
                    'previous_balance' => $previousBalance,
                    'opening_balance'  => 0,
                    'total_debit'      => $saleAmount,
                    'total_credit'     => 0,
                    'closing_balance'  => $newClosingBalance,
                    'created_at'       => $directSaleDateTime,
                    'updated_at'       => $directSaleDateTime,
                ]);
            }

            return back()->with('success', 'Sale saved successfully!');
        });
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sale $sale)
    {
        //
    }

    /**
     * Convert booking to sale form prefill.
     */
    public function convertFromBooking($id)
    {
        // ================= BASIC DATA =================
        $booking = ProductBooking::findOrFail($id);

        if ($booking->party_type == 'credit' || $booking->party_type == 'cash') {
            $booking_customer = Customer::findOrFail($booking->customer_id);
        } else {
            // For walking / on-the-spot customers we don't have a Customer model row.
            $booking_customer = (object) [
                'id' => null,
                'customer_name' => $booking->customer_name ?? null,
                'customer_type' => 'walking',
                'address' => $booking->address ?? null,
                'mobile' => $booking->tel ?? null,
                'filer_type' => $booking->filer_type ?? null,
                'credit_limit' => 0,
                'credit_upto' => null,
                'opening_balance' => 0,
            ];
        }

        // ✅ BRANCH-AWARE DATA
        $isSuper = Auth::check() && Auth::user()->hasRole('super admin');
        $branchId = $booking->branch_id;
        
        $products = Product::when(!$isSuper && $branchId, function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })->get();
        
        $customer = Customer::when(!$isSuper && $branchId, function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })->get();

        $warehouse = Warehouse::when(!$isSuper && $branchId, function ($q) use ($products) {
            if ($products->isEmpty()) {
                $q->whereRaw('0 = 1');
            } else {
                $ids = $products->pluck('id')->toArray();
                $whIds = WarehouseStock::whereIn('product_id', $ids)
                    ->pluck('warehouse_id')
                    ->unique()
                    ->toArray();
                if (!empty($whIds)) {
                    $q->whereIn('id', $whIds);
                } else {
                    $q->whereRaw('0 = 1');
                }
            }
        })->get();

        $accounts = Account::when(!$isSuper && $branchId, function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })->get();

        // ================= ITEM DATA FOR PREFILL =================
        $bookingItemsRaw = \App\Models\ProductBookingItem::where('booking_id', $booking->id)->get();

        $stockMap = DB::table('v_stock_onhand')
            ->pluck('onhand_qty', 'product_id');

        $warehouseStocks = WarehouseStock::with('warehouse')
            ->whereIn(
                'product_id',
                $bookingItemsRaw->pluck('product_id')->unique()
            )
            ->get();

        $items = [];

        if ($bookingItemsRaw->count() > 0) {
            foreach ($bookingItemsRaw as $item) {
                $product = Product::find($item->product_id);
                $qty = (int) $item->sales_qty;
                $discount_type = 'pkr';
                $total_disc = (float) $item->discount_amount;
                
                // If PKR, we need per-unit discount for the sale222 UI
                $disc_per_unit = ($discount_type === 'pkr' && $qty > 0) 
                    ? ($total_disc / $qty) 
                    : $total_disc;

                $items[] = [
                    'product_id' => $item->product_id,
                    'item_name'  => $product->item_name ?? '',
                    'item_code'  => $product->item_code ?? '',
                    'uom'        => $product && $product->brand ? $product->brand->name : '',
                    'unit'       => $product->unit_id ?? '',
                    'price'      => (float) $item->retail_price,
                    'discount'   => (float) $disc_per_unit,
                    'discount_amount' => (float) $total_disc,
                    'discount_percent' => 0,
                    'discount_type' => $discount_type,
                    'qty'        => $qty,
                    'total'      => (float) $item->amount,
                    'color'      => [],
                    'onhand_qty' => (float) ($stockMap[$item->product_id] ?? 0),
                    'warehouse_id' => $item->warehouse_id,
                    'warehouses' => $warehouseStocks,
                ];
            }
        } else {
            // Legacy CSV fallback
            $products_arr = explode(',', $booking->product);
            $codes       = explode(',', $booking->product_code);
            $brands      = explode(',', $booking->brand);
            $units       = explode(',', $booking->unit);
            $prices      = explode(',', $booking->per_price);
            $discounts   = explode(',', $booking->per_discount);
            $qtys        = explode(',', $booking->qty);
            $totals      = explode(',', $booking->per_total);
            $colors_json = json_decode($booking->color, true);

            foreach ($products_arr as $index => $p) {
                $product = Product::where('item_name', trim($p))
                    ->orWhere('item_code', trim($codes[$index] ?? ''))
                    ->first();
                $productId = $product->id ?? null;

                $qty = (int) ($qtys[$index] ?? 1);
                $total_disc = (float) ($discounts[$index] ?? 0);
                // In booking, discount is total for the row. In sale222, PKR discount is per unit.
                $disc_per_unit = $qty > 0 ? ($total_disc / $qty) : 0;

                $items[] = [
                    'product_id' => $productId,
                    'item_name'  => $product->item_name ?? $p,
                    'item_code'  => $product->item_code ?? ($codes[$index] ?? ''),
                    'uom'        => $product->brand->name ?? ($brands[$index] ?? ''),
                    'unit'       => $product->unit ?? ($units[$index] ?? ''),
                    'price'      => (float) ($prices[$index] ?? 0),
                    'discount'   => (float) $disc_per_unit,
                    'discount_amount' => (float) $total_disc,
                    'discount_percent' => 0,
                    'discount_type' => 'pkr',
                    'qty'        => $qty,
                    'total'      => (float) ($totals[$index] ?? 0),
                    'color'      => isset($colors_json[$index]) ? json_decode($colors_json[$index], true) : [],
                    'onhand_qty' => (float) ($stockMap[$productId] ?? 0),
                    'warehouse_id' => null,
                    'warehouses' => $warehouseStocks,
                ];
            }
        }

        // ================= INVOICE NUMBER =================
        $branches = Branch::all();
        $branchCounters = Branch::pluck('invoice_counter', 'id')->toArray();
        
        if ($branchId && isset($branchCounters[$branchId])) {
            $next = ((int) $branchCounters[$branchId]) + 1;
            $nextInvoiceNumber = 'INV-' . str_pad($next, 4, '0', STR_PAD_LEFT);
        } else {
            $nextInvoiceNumber = Sale::generateInvoiceNo();
        }

        $warehouseStocksArray = WarehouseStock::all()->toArray();
        $salesmen = SalesOfficer::all();

        // ================= RETURN SAME VIEW AS ADDSALE() WITH PREFILL DATA =================
        return view('admin_panel.sale.add_sale222', [
            'warehouse'       => $warehouse,
            'customer'        => $customer,
            'accounts'        => $accounts,
            'nextInvoiceNumber' => $nextInvoiceNumber,
            'products'        => $products,
            'branches'        => $branches,
            'branchCounters'  => $branchCounters,
            'warehouseStocks'  => $warehouseStocksArray,
            'salesmen'        => $salesmen,
            // ✅ Prefill data from booking
            'booking'         => $booking,
            'booking_customer' => $booking_customer,
            'bookingItems'    => $items,
            'saleSettings'    => SaleSetting::getSettings(),
        ]);
    }


    // sale return start
    public function saleretun($id)
    {
        $sale = Sale::with('saleItems.product', 'customer')->findOrFail($id);
        $customers = Customer::all();
        
        // Fetch accounts specific to the branch of the sale, and check for active status (boolean 1 or string 'active')
        $accounts = \App\Models\Account::with('head')
            ->where('branch_id', $sale->branch_id)
            ->where(function ($query) {
                $query->where('status', 1)
                      ->orWhere('status', 'active');
            })
            ->get();

        // Calculate already returned quantities from SalesReturn
        $salesReturns = SalesReturn::where('sale_id', $sale->id)->get();
        $alreadyReturned = [];

        foreach ($salesReturns as $sr) {
            $srProducts = explode(',', $sr->product);
            $srCodes = explode(',', $sr->product_code);
            $srQtys = explode(',', $sr->qty);
            
            foreach ($srProducts as $i => $pname) {
                // Try to match by code or name
                $code = $srCodes[$i] ?? '';
                $q = floatval($srQtys[$i] ?? 0);
                
                $product = Product::where('item_name', trim($pname))
                    ->orWhere('item_code', trim($code))
                    ->first();
                
                if ($product) {
                    $pid = $product->id;
                    if (!isset($alreadyReturned[$pid])) {
                        $alreadyReturned[$pid] = 0;
                    }
                    $alreadyReturned[$pid] += $q;
                }
            }
        }

        $items = [];
        
        // Use sale_items if available to get TRUE purchased quantity
        if ($sale->saleItems && $sale->saleItems->count() > 0) {
            foreach ($sale->saleItems as $sItem) {
                $pid = $sItem->product_id;
                $purchasedQty = floatval($sItem->sales_qty ?? $sItem->qty ?? 0);
                $retQty = $alreadyReturned[$pid] ?? 0;
                $remQty = max(0, $purchasedQty - $retQty);

                $items[] = [
                    'product_id' => $pid,
                    'item_name'  => $sItem->product->item_name ?? '',
                    'item_code'  => $sItem->product->item_code ?? '',
                    'brand'      => $sItem->product->brand->name ?? '',
                    'unit'       => $sItem->product->unit ?? '',
                    'price'      => floatval($sItem->retail_price ?? $sItem->per_price ?? 0),
                    'discount'   => floatval($sItem->discount_amount ?? $sItem->per_discount ?? 0),
                    'qty'        => $purchasedQty,
                    'already_returned' => $retQty,
                    'remaining_qty' => $remQty,
                    'total'      => floatval($sItem->amount ?? $sItem->per_total ?? 0),
                    'color'      => json_decode($sItem->color ?? '[]', true),
                    'warehouse_id' => $sItem->warehouse_id,
                ];
            }
        } else {
            // Legacy comma-separated decoding
            $products = explode(',', $sale->product);
            $codes = explode(',', $sale->product_code);
            $brands = explode(',', $sale->brand);
            $units = explode(',', $sale->unit);
            $prices = explode(',', $sale->per_price);
            $discounts = explode(',', $sale->per_discount);
            // In the legacy system, $sale->qty was modified in-place, so it represents remaining.
            $qtys = explode(',', $sale->qty);
            $totals = explode(',', $sale->per_total);
            $colors_json = json_decode($sale->color, true);

            foreach ($products as $index => $p) {
                $product = Product::where('item_name', trim($p))
                    ->orWhere('item_code', trim($codes[$index] ?? ''))
                    ->first();
                    
                $pid = $product->id ?? null;
                $remQty = floatval($qtys[$index] ?? 1);
                
                $items[] = [
                    'product_id' => $pid,
                    'item_name'  => $product->item_name ?? $p,
                    'item_code'  => $product->item_code ?? ($codes[$index] ?? ''),
                    'brand'      => $product->brand->name ?? ($brands[$index] ?? ''),
                    'unit'       => $product->unit ?? ($units[$index] ?? ''),
                    'price'      => floatval($prices[$index] ?? 0),
                    'discount'   => floatval($discounts[$index] ?? 0),
                    'qty'        => $remQty, // Display remaining as purchased for legacy
                    'already_returned' => 0,
                    'remaining_qty' => $remQty,
                    'total'      => floatval($totals[$index] ?? 0),
                    'color'      => isset($colors_json[$index]) ? json_decode($colors_json[$index], true) : [],
                    'warehouse_id' => null,
                ];
            }
        }

        return view('admin_panel.sale.return.create', [
            'sale'      => $sale,
            'Customer'  => $customers,
            'saleItems' => $items,
            'accounts'  => $accounts,
        ]);
    }

    private function upsertStocks(int $productId, float $qtyDelta, int $branchId, int $warehouseId): void
    {
        $now = now();
        $whId = $warehouseId > 0 ? $warehouseId : null;

        // 1. Update warehouse_stocks
        $query = DB::table('warehouse_stocks')
            ->where('product_id', $productId)
            ->where('branch_id', $branchId);
        
        if ($whId) {
            $query->where('warehouse_id', $whId);
        } else {
            $query->whereNull('warehouse_id');
        }

        $affectedWarehouse = $query->update([
            'quantity'   => DB::raw('quantity + '.((int)$qtyDelta)),
            'updated_at' => $now,
        ]);

        if ($affectedWarehouse === 0) {
            DB::table('warehouse_stocks')->insert([
                'branch_id'    => $branchId,
                'warehouse_id' => $whId,
                'product_id'   => $productId,
                'quantity'     => (int)$qtyDelta,
                'price'        => null,
                'remarks'      => 'Sale Return stock',
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }

        // 2. Update stocks (summary table)
        $affectedStocks = DB::table('stocks')
            ->where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->update([
                'qty'        => DB::raw('qty + '.((int)$qtyDelta)),
                'updated_at' => $now,
            ]);

        if ($affectedStocks === 0) {
            DB::table('stocks')->insert([
                'product_id'   => $productId,
                'branch_id'    => $branchId,
                'qty'          => (int)$qtyDelta,
                'reserved_qty' => 0,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }
    }

    public function storeSaleReturn(Request $request)
    {
        DB::beginTransaction();

        try {
            $branchId    = (int) ($request->input('branch_id', 1));
            $warehouseId = (int) ($request->input('warehouse_id', 1));
            $saleId      = (int) $request->sale_id;

            $product_ids   = $request->product_id   ?? [];
            $product_names = $request->product       ?? [];
            $product_codes = $request->item_code     ?? [];
            $brands        = $request->uom           ?? [];
            $units         = $request->unit          ?? [];
            $prices        = $request->price         ?? [];
            $discounts     = $request->item_disc     ?? [];
            $quantities    = $request->qty           ?? [];
            $totals        = $request->total         ?? [];
            $colors        = $request->color         ?? [];

            $combined_products = $combined_codes   = $combined_brands  = $combined_units = [];
            $combined_prices   = $combined_discounts = $combined_qtys = $combined_totals = $combined_colors = [];

            $total_items = 0;
            // returnMap: product_id => qty being returned (for ERP stage logic)
            $returnMap = [];

            foreach ($product_ids as $index => $product_id) {
                $qty   = max(0.0, (float) ($quantities[$index] ?? 0));
                $price = max(0.0, (float) ($prices[$index]    ?? 0));

                if (! $product_id || $qty <= 0 || $price <= 0) {
                    continue;
                }

                $combined_products[]  = $product_names[$index] ?? '';
                $combined_codes[]     = $product_codes[$index] ?? '';
                $combined_brands[]    = $brands[$index]        ?? '';
                $combined_units[]     = $units[$index]         ?? '';
                $combined_prices[]    = $price;
                $combined_discounts[] = $discounts[$index]     ?? 0;
                $combined_qtys[]      = $qty;
                $combined_totals[]    = $totals[$index]        ?? 0;

                $decodedColor       = $colors[$index] ?? [];
                $combined_colors[]  = is_array($decodedColor)
                    ? json_encode($decodedColor)
                    : json_encode((array) json_decode($decodedColor, true));

                $returnMap[(int)$product_id] = ($returnMap[(int)$product_id] ?? 0) + $qty;
                $total_items += $qty;
            }

            if ($total_items <= 0) {
                throw new \Exception('No items were returned.');
            }

            // ------------------------------------------------------------------
            // ERP STAGE DETECTION
            // Stage A: No DC exists            → affect Ledger + Accounts only
            // Stage B: DC exists, no Gate Pass → affect Ledger + Accounts + reserved_qty + DC items
            // Stage C: Gate Pass exists         → affect Ledger + Accounts + physical stock + DC + GP items
            // ------------------------------------------------------------------
            $dc = DB::table('warehouse_orders')
                ->where('sale_id', $saleId)
                ->first();

            $gatepass  = null;
            $movements = []; // physical stock movement records, only populated in Stage C

            if ($dc) {
                $gatepass = DB::table('outward_gatepasses')
                    ->where('order_id', $dc->id)
                    ->first();
            }

            // ------------------------------------------------------------------
            // STAGE B: DC exists, NO Gate Pass → reduce reserved_qty + update DC
            // ------------------------------------------------------------------
            if ($dc && ! $gatepass) {
                foreach ($returnMap as $pid => $retQty) {
                    // Release reserved stock (goods not yet dispatched)
                    DB::table('stocks')
                        ->where('product_id', $pid)
                        ->where('branch_id', $branchId)
                        ->decrement('reserved_qty', $retQty);

                    // Reduce DC line-item qty
                    $dcItem = DB::table('warehouse_order_items')
                        ->where('warehouse_order_id', $dc->id)
                        ->where('product_id', $pid)
                        ->first();
                    if ($dcItem) {
                        $newQty = max(0, (float)$dcItem->qty - $retQty);
                        DB::table('warehouse_order_items')
                            ->where('id', $dcItem->id)
                            ->update([
                                'qty'        => $newQty,
                                'amount'     => $newQty * (float)($dcItem->retail_price ?? 0),
                                'updated_at' => now(),
                            ]);
                    }

                    // Update DC JSON items column
                    $this->updateWarehouseOrderItemsJson($dc->id, $pid, $retQty);
                }
            }

            // ------------------------------------------------------------------
            // STAGE C: Gate Pass exists → restore physical stock + update GP + DC
            // ------------------------------------------------------------------
            if ($dc && $gatepass) {
                foreach ($returnMap as $pid => $retQty) {
                    // Restore physical stock in stocks table
                    $affected = DB::table('stocks')
                        ->where('product_id', $pid)
                        ->where('branch_id', $branchId)
                        ->update([
                            'qty'        => DB::raw('qty + ' . (float)$retQty),
                            'updated_at' => now(),
                        ]);
                    if ($affected === 0) {
                        DB::table('stocks')->insert([
                            'product_id'   => $pid,
                            'branch_id'    => $branchId,
                            'warehouse_id' => $warehouseId > 0 ? $warehouseId : 1,
                            'qty'          => (int)$retQty,
                            'reserved_qty' => 0,
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ]);
                    }

                    // Restore warehouse_stocks
                    $whId   = $warehouseId > 0 ? $warehouseId : null;
                    $wsQry  = DB::table('warehouse_stocks')
                        ->where('product_id', $pid)
                        ->where('branch_id', $branchId);
                    $whId ? $wsQry->where('warehouse_id', $whId) : $wsQry->whereNull('warehouse_id');
                    if ($wsQry->update(['quantity' => DB::raw('quantity + ' . (float)$retQty), 'updated_at' => now()]) === 0) {
                        DB::table('warehouse_stocks')->insert([
                            'product_id'   => $pid,
                            'branch_id'    => $branchId,
                            'warehouse_id' => $whId,
                            'quantity'     => (int)$retQty,
                            'price'        => null,
                            'remarks'      => 'Sale Return stock restore',
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ]);
                    }

                    // Reduce Gate Pass items JSON
                    if (! empty($gatepass->items)) {
                        $gpItems = json_decode($gatepass->items, true) ?? [];
                        foreach ($gpItems as &$gpItem) {
                            if ((int)($gpItem['product_id'] ?? 0) === $pid) {
                                $gpItem['qty']    = max(0, (float)$gpItem['qty'] - $retQty);
                                $gpItem['amount'] = $gpItem['qty'] * (float)($gpItem['retail_price'] ?? 0);
                            }
                        }
                        unset($gpItem);
                        DB::table('outward_gatepasses')
                            ->where('id', $gatepass->id)
                            ->update(['items' => json_encode($gpItems), 'updated_at' => now()]);
                    }

                    // Reduce DC line-item qty
                    $dcItem = DB::table('warehouse_order_items')
                        ->where('warehouse_order_id', $dc->id)
                        ->where('product_id', $pid)
                        ->first();
                    if ($dcItem) {
                        $newQty = max(0, (float)$dcItem->qty - $retQty);
                        DB::table('warehouse_order_items')
                            ->where('id', $dcItem->id)
                            ->update([
                                'qty'        => $newQty,
                                'amount'     => $newQty * (float)($dcItem->retail_price ?? 0),
                                'updated_at' => now(),
                            ]);
                    }

                    // Update DC JSON items column
                    $this->updateWarehouseOrderItemsJson($dc->id, $pid, $retQty);
                }

                // Insert physical stock movement records (goods came back)
                $movements = [];
                foreach ($returnMap as $pid => $retQty) {
                    $movements[] = [
                        'product_id' => $pid,
                        'type'       => 'in',
                        'qty'        => $retQty,
                        'ref_type'   => 'SALE_RETURN',
                        'ref_id'     => null, // will be updated after SalesReturn saved
                        'note'       => 'Sale return — Gate Pass stage',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // ------------------------------------------------------------------
            // ALL STAGES: Reduce original sale_items qty, recalculate sale totals
            // ------------------------------------------------------------------
            foreach ($returnMap as $pid => $retQty) {
                DB::table('sale_items')
                    ->where('sale_id', $saleId)
                    ->where('product_id', $pid)
                    ->update([
                        'sales_qty'  => DB::raw('GREATEST(0, sales_qty - ' . (float)$retQty . ')'),
                        'amount'     => DB::raw('GREATEST(0, sales_qty - ' . (float)$retQty . ') * retail_price'),
                        'updated_at' => now(),
                    ]);
            }

            // Recalculate sale header totals from updated sale_items
            $sit = DB::table('sale_items')
                ->where('sale_id', $saleId)
                ->selectRaw('SUM(amount) as subtotal, SUM(sales_qty) as total_qty, SUM(discount_amount) as total_disc')
                ->first();

            $newSubtotal = (float)($sit->subtotal  ?? 0);
            $newQty      = (float)($sit->total_qty ?? 0);
            $newDisc     = (float)($sit->total_disc ?? 0);
            $newNet      = $newSubtotal - $newDisc;

            DB::table('sales')->where('id', $saleId)->update([
                'total_bill_amount'   => $newSubtotal,
                'total_extradiscount' => $newDisc,
                'total_net'           => $newNet,
                'total_items'         => $newQty,
                'updated_at'          => now(),
            ]);

            // ------------------------------------------------------------------
            // Create SalesReturn record
            // ------------------------------------------------------------------
            $saleReturn = new SalesReturn;
            $saleReturn->sale_id             = $request->sale_id;
            $saleReturn->customer            = $request->customer_id;
            $saleReturn->reference           = $request->reference;
            $saleReturn->product             = implode(',', $combined_products);
            $saleReturn->product_code        = implode(',', $combined_codes);
            $saleReturn->brand               = implode(',', $combined_brands);
            $saleReturn->unit                = implode(',', $combined_units);
            $saleReturn->per_price           = implode(',', $combined_prices);
            $saleReturn->per_discount        = implode(',', $combined_discounts);
            $saleReturn->qty                 = implode(',', $combined_qtys);
            $saleReturn->per_total           = implode(',', $combined_totals);
            $saleReturn->color               = json_encode($combined_colors);
            $saleReturn->total_amount_Words  = $request->total_amount_Words;
            $saleReturn->total_bill_amount   = $request->total_subtotal;
            $saleReturn->total_extradiscount = $request->total_extra_cost;
            $saleReturn->total_net           = $request->total_net;
            $saleReturn->cash                = $request->cash;
            $saleReturn->card                = $request->card;
            $saleReturn->change              = $request->change;
            $saleReturn->total_items         = $total_items;
            $saleReturn->return_note         = $request->return_note;
            $saleReturn->save();

            // Update movement ref_id now that SalesReturn is persisted (Stage C only)
            if ($dc && $gatepass && ! empty($movements)) {
                foreach ($movements as &$mv) {
                    $mv['ref_id'] = $saleReturn->id;
                }
                unset($mv);
                DB::table('stock_movements')->insert($movements);
            }

            // ------------------------------------------------------------------
            // Customer Ledger: Credit Note (customer owes less after return)
            // ------------------------------------------------------------------
            $customer_id = $request->customer_id;
            if ($customer_id) {
                $latestLedger = CustomerLedger::where('customer_id', $customer_id)->latest('id')->first();
                $prevBal      = $latestLedger ? (float)$latestLedger->closing_balance : 0;
                $newClosing   = $prevBal - (float)$request->total_net;

                CustomerLedger::create([
                    'customer_id'      => $customer_id,
                    'admin_or_user_id' => auth()->id(),
                    'previous_balance' => $prevBal,
                    'total_credit'     => (float)$request->total_net,
                    'closing_balance'  => $newClosing,
                    'opening_balance'  => 0,
                ]);
            }

            // ------------------------------------------------------------------
            // Accounts: Cash Refund — debit selected accounts
            // ------------------------------------------------------------------
            if ($request->refund_type === 'cash') {
                $refundAccountIds = $request->input('refund_account_id', []);
                $refundAmounts    = $request->input('refund_amount', []);
                foreach ($refundAccountIds as $i => $accountId) {
                    $refAmt = (float)($refundAmounts[$i] ?? 0);
                    if (! $accountId || $refAmt <= 0) continue;
                    DB::table('accounts')
                        ->where('id', (int)$accountId)
                        ->decrement('opening_balance', $refAmt);
                }
            }

            DB::commit();

            $stage = $dc ? ($gatepass ? 'C (Gate Pass)' : 'B (DC Only)') : 'A (Invoice Only)';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sale return saved successfully. Stage: ' . $stage,
                ]);
            }

            return redirect()->route('sale.index')->with('success', 'Sale return saved successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sale return failed: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Sale return failed: ' . $e->getMessage());
        }
    }

    /**
     * Reduce the qty of a specific product in the warehouse_orders.items JSON column.
     * Called from storeSaleReturn for Stage B and Stage C.
     */
    private function updateWarehouseOrderItemsJson(int $dcId, int $productId, float $retQty): void
    {
        $wo = DB::table('warehouse_orders')->where('id', $dcId)->first();
        if (! $wo || empty($wo->items)) return;

        $items = json_decode($wo->items, true);
        if (! is_array($items)) return;

        foreach ($items as &$item) {
            if ((int)($item['product_id'] ?? 0) === $productId) {
                $item['qty'] = max(0, (float)$item['qty'] - $retQty);
                if (isset($item['retail_price'])) {
                    $item['amount'] = $item['qty'] * (float)$item['retail_price'];
                }
            }
        }
        unset($item);

        DB::table('warehouse_orders')->where('id', $dcId)->update([
            'items'      => json_encode($items),
            'updated_at' => now(),
        ]);
    }

    public function salereturnview()
    {
        $query = SalesReturn::with('sale.customer')->orderBy('created_at', 'desc');

        if (Auth::check() && !Auth::user()->hasRole('super admin')) {
            $branchId = (int) (Auth::user()->branch_id ?? 0);
            $query->whereHas('sale', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        $salesReturns = $query->get();

        return view('admin_panel.sale.return.index', [
            'salesReturns' => $salesReturns,
        ]);
    }

    public function saleinvoice($id)
    {
        $sale = Sale::with('customer')->with('saleItems')->findOrFail($id);

        // Decode sale pivot or comma fields
        $products = explode(',', $sale->product);
        $codes = explode(',', $sale->product_code);
        $brands = explode(',', $sale->brand);
        $units = explode(',', $sale->unit);
        $prices = explode(',', $sale->per_price);
        $discounts = explode(',', $sale->per_discount);
        $qtys = explode(',', $sale->qty);
        $totals = explode(',', $sale->per_total);
        $colors_json = json_decode($sale->color, true);

        $items = [];

        foreach ($products as $index => $p) {
            $product = Product::where('item_name', trim($p))
                ->orWhere('item_code', trim($codes[$index] ?? ''))
                ->first();

            $items[] = [
                'product_id' => $product->id ?? '',
                'item_name'  => $product->item_name ?? $p,
                'item_code'  => $product->item_code ?? ($codes[$index] ?? ''),
                'brand'      => $product->brand->name ?? ($brands[$index] ?? ''),
                'unit'       => $product->unit ?? ($units[$index] ?? ''),
                'price'      => floatval($prices[$index] ?? 0),
                'discount'   => floatval($discounts[$index] ?? 0),
                'qty'        => intval($qtys[$index] ?? 1),
                'total'      => floatval($totals[$index] ?? 0),
                'color'      => isset($colors_json[$index]) ? json_decode($colors_json[$index], true) : [],
            ];
        }
        
        // 🎯 GET RECEIPTS FOR THIS SALE
        $receipts = ReceiptsVoucher::where('sale_id', $sale->id)
            ->where('type', 'SALE_RECEIPT')
            ->where('processed', true)
            ->get();
        
        $receivedAmount = 0;
        foreach ($receipts as $receipt) {
            // Handle both single value and JSON array formats
            $amount = 0;
            if (!empty($receipt->total_amount)) {
                $amount = (float) $receipt->total_amount;
            } elseif (!empty($receipt->amount)) {
                $decoded = json_decode($receipt->amount, true);
                if (is_array($decoded)) {
                    $amount = array_sum(array_map('floatval', $decoded));
                } else {
                    $amount = (float) $receipt->amount;
                }
            }
            $receivedAmount += $amount;
        }
        
        $balanceDue = (float)$sale->total_net - $receivedAmount;
        
        return view('admin_panel.sale.saleinvoice', [
            'sale'           => $sale,
            'saleItems'      => $items,
            'receivedAmount' => $receivedAmount,
            'balanceDue'     => $balanceDue,
            'receipts'       => $receipts,
        ]);
    }

    public function saleedit($id)
    {
        $sale = Sale::with(['customer', 'saleItems.product'])->findOrFail($id);
        
        $isSuper = Auth::check() && Auth::user()->hasRole('super admin');
        if (!$isSuper && Auth::check() && Auth::user()->branch_id && $sale->branch_id != Auth::user()->branch_id) {
            return redirect()->route('sale.index')->with('error', 'Unauthorized. You can only edit sales from your own branch.');
        }
        $branchId = $sale->branch_id ?? (Auth::check() ? Auth::user()->branch_id : 1);
        
        $customer = Customer::when(!$isSuper && $branchId, function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })->get();
        
        $accounts = Account::when(!$isSuper && $branchId, function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })->get();
        
        $products = Product::when(!$isSuper && $branchId, function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })->get();

        $warehouse = Warehouse::when(!$isSuper && $branchId, function ($q) use ($products) {
            if ($products->isEmpty()) {
                $q->whereRaw('0 = 1');
            } else {
                $ids = $products->pluck('id')->toArray();
                $whIds = WarehouseStock::whereIn('product_id', $ids)
                    ->pluck('warehouse_id')
                    ->unique()
                    ->toArray();
                if (!empty($whIds)) {
                    $q->whereIn('id', $whIds);
                } else {
                    $q->whereRaw('0 = 1');
                }
            }
        })->get();

        $branches = Branch::with('warehouses')->get();
        $branchCounters = Branch::pluck('invoice_counter', 'id')->toArray();
        $warehouseStocks = WarehouseStock::all()->toArray();
        $salesmen = SalesOfficer::when(!$isSuper && $branchId, function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })->get();
        $saleSettings = \App\Models\SaleSetting::getSettings();

        // Build branch-to-warehouses mapping
        $branchWarehouseMap = [];
        foreach ($branches as $b) {
            $branchWarehouseMap[$b->id] = $b->warehouses->map(function ($w) {
                return [
                    'id'             => $w->id,
                    'name'           => $w->warehouse_name ?? $w->name,
                    'warehouse_name' => $w->warehouse_name ?? $w->name,
                ];
            })->values()->toArray();
        }

        $activeBranch = $branches->firstWhere('id', $branchId) ?? $branches->first();
        $warehouse = ($activeBranch && $activeBranch->warehouses) ? $activeBranch->warehouses : collect();
        $allWarehouses = $warehouse;

        // Fetch receipt vouchers for this sale (branch-aware and sale-specific)
        $receipts = ReceiptsVoucher::where(function ($q) use ($sale, $branchId) {
                $q->where('sale_id', $sale->id)
                  ->orWhere(function ($q2) use ($sale, $branchId) {
                      $q2->where('reference_no', $sale->invoice_no)
                         ->where(function ($q3) use ($sale) {
                             $q3->whereNull('sale_id')->orWhere('sale_id', $sale->id);
                         });
                      if ($branchId) {
                          $q2->where('branch_id', $branchId);
                      }
                  });
                if ($sale->booking_id) {
                    $q->orWhere(function ($q2) use ($sale, $branchId) {
                        $q2->where('booking_id', $sale->booking_id)
                           ->where(function ($q3) use ($sale) {
                               $q3->whereNull('sale_id')->orWhere('sale_id', $sale->id);
                           });
                        if ($branchId) {
                            $q2->where('branch_id', $branchId);
                        }
                    });
                }
            })
            ->whereIn('type', ['SALE_RECEIPT', 'RECEIPT'])
            ->orderBy('id', 'asc')
            ->get();

        // If no explicit receipts in table, synthesize from account ledger entry or branch account
        if ($receipts->isEmpty()) {
            $paidAmt = (float)($sale->receipt1 ?? 0) + (float)($sale->receipt2 ?? 0);
            if ($paidAmt <= 0 && ($sale->party_type === 'cash' || $sale->party_type === 'walking' || $sale->status === 'posted' || $sale->is_posted)) {
                $paidAmt = (float)($sale->total_balance ?: ($sale->sub_total2 ?: ($sale->total_net ?: 0)));
            }

            if ($paidAmt > 0) {
                // Check if an account ledger entry exists for this sale's invoice
                $ledgerAccId = AccountLedgerEntry::where('voucher_no', $sale->invoice_no)
                    ->where('branch_id', $branchId)
                    ->value('account_id');

                if ($ledgerAccId && $accounts->contains('id', $ledgerAccId)) {
                    $defaultAccId = $ledgerAccId;
                } else {
                    $branchAccounts = $accounts->where('branch_id', $branchId);
                    $defaultAcc = $branchAccounts->first(fn($a) => str_contains(strtolower($a->title), 'cash') || str_contains(strtolower($a->title), 'counter'))
                               ?? $branchAccounts->first()
                               ?? $accounts->first();
                    $defaultAccId = $defaultAcc ? $defaultAcc->id : 1;
                }

                $receipts = collect([
                    (object) [
                        'row_account_id' => $defaultAccId,
                        'amount'         => $paidAmt,
                        'total_amount'   => $paidAmt,
                        'type'           => 'SALE_RECEIPT',
                    ]
                ]);
            }
        }

        // Fetch accurate stock map from WarehouseStock and Stock tables
        $whStockMap = WarehouseStock::select('product_id', DB::raw('SUM(quantity) as qty'))
            ->groupBy('product_id')
            ->pluck('qty', 'product_id');

        $mainStockMap = Stock::select('product_id', DB::raw('SUM(qty) as qty'))
            ->groupBy('product_id')
            ->pluck('qty', 'product_id');

        $vStockMap = DB::table('v_stock_onhand')->pluck('onhand_qty', 'product_id');

        $items = [];
        if ($sale->saleItems && $sale->saleItems->count() > 0) {
            foreach ($sale->saleItems as $saleItem) {
                $product = $saleItem->product;
                $pid = $saleItem->product_id;
                $onhand = 0;
                if ($saleItem->warehouse_id) {
                    $locStock = WarehouseStock::where('product_id', $pid)->where('warehouse_id', $saleItem->warehouse_id)->sum('quantity');
                    $onhand = (float)$locStock;
                } elseif ($branchId) {
                    $locStock = WarehouseStock::where('product_id', $pid)->where('branch_id', $branchId)->whereNull('warehouse_id')->sum('quantity');
                    if ($locStock != 0) {
                        $onhand = (float)$locStock;
                    } else {
                        $bStock = Stock::where('product_id', $pid)->where('branch_id', $branchId)->sum('qty');
                        $onhand = (float)$bStock;
                    }
                }
                if ($onhand == 0) {
                    $onhand = (float)($whStockMap[$pid] ?? ($mainStockMap[$pid] ?? ($vStockMap[$pid] ?? 0)));
                }

                $itemWatt = floatval($saleItem->watt ?? 0);
                if ($itemWatt == 0 && $product) {
                    if (preg_match('/\b(\d{3,4})\s*(?:watts?|w)\b/i', $product->item_name ?? '', $matches)) {
                        $itemWatt = floatval($matches[1]);
                    } else if (preg_match('/\b(645|585|590|575|580|550|540|530|700|650)\b/i', $product->item_name ?? '', $matches)) {
                        $itemWatt = floatval($matches[1]);
                    } else {
                        $qty = floatval($saleItem->sales_qty ?? 0);
                        $price = floatval($saleItem->retail_price ?? $saleItem->sales_price ?? 0);
                        $amt = floatval($saleItem->amount ?? 0);
                        if ($qty > 0 && $price > 0 && $amt > ($qty * $price)) {
                            $possibleWatt = round($amt / ($qty * $price));
                            if ($possibleWatt >= 100 && $possibleWatt <= 1000) {
                                $itemWatt = $possibleWatt;
                            }
                        }
                    }
                }

                $items[] = [
                    'product_id'       => $saleItem->product_id,
                    'item_name'        => $product->item_name ?? '',
                    'item_code'        => $product->item_code ?? '',
                    'brand'            => $product->brand ? $product->brand->name : '',
                    'unit'             => $product->unit ?? '',
                    'watt'             => $itemWatt,
                    'retail_price'     => floatval($saleItem->retail_price ?? $saleItem->sales_price ?? 0),
                    'sales_price'      => floatval($saleItem->retail_price ?? $saleItem->sales_price ?? 0),
                    'discount'         => floatval($saleItem->discount_amount ?? 0),
                    'discount_amount'  => floatval($saleItem->discount_amount ?? 0),
                    'discount_percent' => floatval($saleItem->discount_percent ?? 0),
                    'discount_type'    => floatval($saleItem->discount_percent ?? 0) > 0 ? 'percent' : 'pkr',
                    'sales_qty'        => floatval($saleItem->sales_qty ?? 0),
                    'qty'              => floatval($saleItem->sales_qty ?? 0),
                    'amount'           => floatval($saleItem->amount ?? 0),
                    'total'            => floatval($saleItem->amount ?? 0),
                    'onhand_qty'       => $onhand,
                    'warehouse_id'     => $saleItem->warehouse_id,
                ];
            }
        } else if ($sale->product) {
            $productsArr  = explode(',', $sale->product);
            $codesArr     = explode(',', $sale->product_code ?? '');
            $pricesArr    = explode(',', $sale->per_price ?? '');
            $discountsArr = explode(',', $sale->per_discount ?? '');
            $qtysArr      = explode(',', $sale->qty ?? '');
            $totalsArr    = explode(',', $sale->per_total ?? '');

            foreach ($productsArr as $index => $p) {
                $product = Product::where('item_name', trim($p))
                    ->orWhere('item_code', trim($codesArr[$index] ?? ''))
                    ->first();

                $productId = $product->id ?? null;
                $qty   = floatval($qtysArr[$index] ?? 1);
                $price = floatval($pricesArr[$index] ?? 0);
                $disc  = floatval($discountsArr[$index] ?? 0);
                $tot   = floatval($totalsArr[$index] ?? 0);

                $items[] = [
                    'product_id'       => $productId,
                    'item_name'        => $product->item_name ?? $p,
                    'item_code'        => $product->item_code ?? ($codesArr[$index] ?? ''),
                    'brand'            => $product->brand ? $product->brand->name : '',
                    'unit'             => $product->unit ?? '',
                    'retail_price'     => $price,
                    'sales_price'      => $price,
                    'discount'         => $disc,
                    'discount_amount'  => $disc,
                    'discount_percent' => 0,
                    'discount_type'    => 'pkr',
                    'sales_qty'        => $qty,
                    'qty'              => $qty,
                    'amount'           => $tot,
                    'total'            => $tot,
                    'onhand_qty'       => floatval($stockMap[$productId] ?? 0),
                    'warehouse_id'     => null,
                ];
            }
        }

        $sale_customer = $sale->customer;
        $partyType = $sale->party_type ?? $sale->partyType ?? (!empty($sale->sub_customer) ? 'walking' : 'credit');

        return view('admin_panel.sale.add_sale222', [
            'warehouse'          => $warehouse,
            'allWarehouses'      => $allWarehouses,
            'customer'           => $customer,
            'sale_customer'      => $sale_customer,
            'accounts'           => $accounts,
            'nextInvoiceNumber'  => $sale->invoice_no,
            'products'           => $products,
            'branches'           => $branches,
            'branchCounters'     => $branchCounters,
            'warehouseStocks'    => $warehouseStocks,
            'salesmen'           => $salesmen,
            'saleSettings'       => $saleSettings,
            'isEditMode'         => true,
            'sale'               => $sale,
            'saleItems'          => $items,
            'receipts'           => $receipts,
            'partyType'          => $partyType,
            'defaultBranchId'    => $branchId,
            'branchWarehouseMap' => $branchWarehouseMap,
        ]);
    }

    /**
     * Update Sale with proper SaleItem relationship handling
     * ✅ Full business logic: updates sale header, items, stock, and ledger
     */
    public function update(Request $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                
                $sale = Sale::with(['saleItems', 'customer'])->lockForUpdate()->findOrFail($id);
                $oldTotal = floatval($sale->total_net ?? 0);
                $customerId = $request->input('customer_id');

                $requestedBranchId = $request->input('branch_id');
                $branchId = $requestedBranchId ? (int)$requestedBranchId : ($sale->branch_id ?? (auth()->user()->branch_id ?? 1));

                $globalLoc = $request->input('global_warehouse_id');
                if (!$requestedBranchId && !empty($globalLoc) && is_string($globalLoc) && str_starts_with($globalLoc, 'branch_')) {
                    $extractedBranch = (int) str_replace('branch_', '', $globalLoc);
                    if ($extractedBranch > 0) {
                        $branchId = $extractedBranch;
                    }
                }

                /* ================= STEP 1: REVERSE OLD STOCK (Add back) ================= */
                foreach ($sale->saleItems as $oldItem) {
                    $itemBranchId = $oldItem->branch_id ?: $branchId;
                    $rawOldWid = $oldItem->warehouse_id;
                    $oldWid = null;

                    if ($rawOldWid) {
                        if (is_string($rawOldWid) && str_starts_with($rawOldWid, 'warehouse_')) {
                            $oldWid = (int) str_replace('warehouse_', '', $rawOldWid);
                        } elseif (is_numeric($rawOldWid) && (int)$rawOldWid > 0) {
                            $oldWid = (int)$rawOldWid;
                        }
                    }

                    // Restore warehouse_stocks (shop stock has warehouse_id = null)
                    $whStockQuery = WarehouseStock::lockForUpdate()
                        ->where('product_id', $oldItem->product_id)
                        ->where('branch_id', $itemBranchId);

                    if ($oldWid) {
                        $whStockQuery->where('warehouse_id', $oldWid);
                    } else {
                        $whStockQuery->whereNull('warehouse_id');
                    }

                    $whStock = $whStockQuery->first();
                    if ($whStock) {
                        $whStock->quantity += $oldItem->sales_qty;
                        $whStock->save();
                    } else {
                        WarehouseStock::create([
                            'branch_id'    => $itemBranchId,
                            'warehouse_id' => $oldWid,
                            'product_id'   => $oldItem->product_id,
                            'quantity'     => $oldItem->sales_qty,
                        ]);
                    }

                    // Restore main stock (branch-level overall)
                    $mainStock = Stock::lockForUpdate()
                        ->where('product_id', $oldItem->product_id)
                        ->where('branch_id', $itemBranchId)
                        ->first();
                    if ($mainStock) {
                        $mainStock->qty += $oldItem->sales_qty;
                        $mainStock->save();
                    } else {
                        Stock::create([
                            'branch_id'    => $itemBranchId,
                            'product_id'   => $oldItem->product_id,
                            'qty'          => $oldItem->sales_qty,
                            'reserved_qty' => 0,
                        ]);
                    }
                }

                /* ================= STEP 2: UPDATE SALE HEADER ================= */
                $newSubTotal1 = floatval($request->input('subTotal1', 0));
                $newSubTotal2 = floatval($request->input('subTotal2', 0));
                $newAddDiscount = floatval($request->input('additional_discount', $request->input('discountAmount', 0)));
                $newExtraCharges = floatval($request->input('extra_charges', 0));
                $newTotal = floatval($request->input('totalBalance', 0));

                $partyType = $request->input('partyType', $sale->partyType ?? 'credit');
                $customerName = null;
                if ($partyType === 'walking') {
                    $customerName = $request->input('walkin_name') ?? $request->input('customer') ?? $request->input('customer_display') ?? 'Walk-in Customer';
                }

                // Support custom / edited sale date
                $selectedDate = $request->input('sale_date') ?? $request->input('Invoice_date') ?? null;
                $saleDateTime = $sale->created_at ?: now();
                if (!empty($selectedDate)) {
                    try {
                        $parsedDate = \Carbon\Carbon::parse($selectedDate);
                        $saleDateTime = $parsedDate->setTime(
                            $sale->created_at ? $sale->created_at->hour : now()->hour,
                            $sale->created_at ? $sale->created_at->minute : now()->minute,
                            $sale->created_at ? $sale->created_at->second : now()->second
                        );
                    } catch (\Exception $e) {
                        $saleDateTime = $sale->created_at ?: now();
                    }
                }

                $sale->update([
                    'branch_id'           => $branchId,
                    'customer_id'         => ($partyType === 'walking' ? null : ($customerId ?: $sale->customer_id)),
                    'sub_customer'        => $customerName,
                    'party_type'          => $partyType,
                    'salesman_id'         => $request->input('salesman_id', $sale->salesman_id),
                    'manual_invoice'      => $request->input('manual_invoice', $request->input('Invoice_main', $sale->manual_invoice)),
                    'address'             => $request->input('address', $sale->address),
                    'tel'                 => $request->input('tel', $sale->tel),
                    'remarks'             => $request->input('remarks', $request->input('reference', $sale->remarks)),
                    'sub_total1'          => $newSubTotal1,
                    'sub_total2'          => $newSubTotal2,
                    'additional_discount' => $newAddDiscount,
                    'extra_charges'       => $newExtraCharges,
                    'total_net'           => $newTotal,
                    'previous_balance'    => floatval($request->input('previousBalance', $sale->previous_balance)),
                    'total_balance'       => $newTotal,
                    'created_at'          => $saleDateTime,
                ]);

                /* ================= STEP 3: DELETE OLD SALE ITEMS ================= */
                SaleItem::where('sale_id', $sale->id)->delete();

                /* ================= STEP 4: CREATE NEW SALE ITEMS & DEDUCT NEW STOCK ================= */
                $productIds = $request->input('product_id', []);
                $qtys = $request->input('sales_qty', []);
                $retailPrices = $request->input('retail_price', []);
                $discountAmounts = $request->input('discount_amount', []);
                $discountPercentages = $request->input('discount_percentage', []);
                $discountTypes = $request->input('discount_type', []);
                $salesAmounts = $request->input('sales_amount', []);
                $warehouseIds = $request->input('warehouse_id', []);
                $watts = $request->input('watt', []);

                foreach ($productIds as $i => $productId) {
                    $qty = floatval($qtys[$i] ?? 0);
                    if (!$productId || $qty <= 0) continue;

                    $retailPrice = floatval($retailPrices[$i] ?? 0);
                    $discAmount = floatval($discountAmounts[$i] ?? 0);
                    $discPercent = floatval($discountPercentages[$i] ?? 0);
                    $discType = $discountTypes[$i] ?? 'percent';
                    $amount = floatval($salesAmounts[$i] ?? 0);

                    // Extract raw location identifier for this line item
                    $rawLoc = null;
                    if (is_array($warehouseIds)) {
                        $rawLoc = $warehouseIds[$i] ?? ($warehouseIds[$productId] ?? null);
                    } elseif (!empty($warehouseIds)) {
                        $rawLoc = $warehouseIds;
                    }
                    if (!$rawLoc) {
                        $rawLoc = $globalLoc ?? $sale->warehouse_id ?? null;
                    }

                    // Cleanly parse branch vs warehouse
                    $itemBranchId = $branchId;
                    $cleanWarehouseId = null;
                    $deliveryLocationType = 'branch';

                    if (!empty($rawLoc)) {
                        if (is_string($rawLoc) && str_starts_with($rawLoc, 'branch_')) {
                            $itemBranchId = (int) str_replace('branch_', '', $rawLoc);
                            $cleanWarehouseId = null;
                            $deliveryLocationType = 'branch';
                        } elseif (is_string($rawLoc) && ($rawLoc === 'branch' || $rawLoc === 'shop')) {
                            $cleanWarehouseId = null;
                            $deliveryLocationType = 'branch';
                        } elseif (is_string($rawLoc) && str_starts_with($rawLoc, 'warehouse_')) {
                            $cleanWarehouseId = (int) str_replace('warehouse_', '', $rawLoc);
                            $deliveryLocationType = 'warehouse';
                        } elseif (is_numeric($rawLoc) && (int) $rawLoc > 0) {
                            if (Warehouse::where('id', (int) $rawLoc)->exists()) {
                                $cleanWarehouseId = (int) $rawLoc;
                                $deliveryLocationType = 'warehouse';
                            } else {
                                $itemBranchId = (int) $rawLoc;
                                $cleanWarehouseId = null;
                                $deliveryLocationType = 'branch';
                            }
                        }
                    }

                    if (!$itemBranchId) {
                        $itemBranchId = $branchId;
                    }

                    $watt = floatval(is_array($watts) ? ($watts[$i] ?? 0) : ($request->input("watt.$i", 0)));

                    // Create new sale item
                    $itemData = [
                        'invoice_no'       => $sale->invoice_no,
                        'branch_id'        => $itemBranchId,
                        'sale_id'          => $sale->id,
                        'warehouse_id'     => $cleanWarehouseId, // Valid integer or NULL (never string like 'branch_12')
                        'product_id'       => $productId,
                        'watt'             => $watt,
                        'sales_qty'        => $qty,
                        'retail_price'     => $retailPrice,
                        'discount_percent' => $discPercent,
                        'discount_amount'  => $discAmount,
                        'discount_type'    => $discType,
                        'amount'           => $amount,
                        'created_at'       => $saleDateTime,
                        'updated_at'       => $saleDateTime,
                    ];
                    if (\Illuminate\Support\Facades\Schema::hasColumn('sale_items', 'delivery_location_type')) {
                        $itemData['delivery_location_type'] = $deliveryLocationType;
                    }
                    SaleItem::create($itemData);

                    // Deduct from warehouse_stocks (unified inventory table)
                    // If cleanWarehouseId is null -> deduct from branch/shop stock (warehouse_id IS NULL)
                    // If cleanWarehouseId is integer -> deduct from specific warehouse
                    $whStockQuery = WarehouseStock::lockForUpdate()
                        ->where('product_id', $productId)
                        ->where('branch_id', $itemBranchId);

                    if ($cleanWarehouseId) {
                        $whStockQuery->where('warehouse_id', $cleanWarehouseId);
                    } else {
                        $whStockQuery->whereNull('warehouse_id');
                    }

                    $whStock = $whStockQuery->first();
                    if ($whStock) {
                        $whStock->quantity -= $qty;
                        $whStock->save();
                    } else {
                        WarehouseStock::create([
                            'branch_id'    => $itemBranchId,
                            'warehouse_id' => $cleanWarehouseId,
                            'product_id'   => $productId,
                            'quantity'     => -$qty,
                        ]);
                    }

                    // Deduct from main stock (branch-level overall)
                    $mainStock = Stock::lockForUpdate()
                        ->where('product_id', $productId)
                        ->where('branch_id', $itemBranchId)
                        ->first();

                    if ($mainStock) {
                        $mainStock->qty -= $qty;
                        $mainStock->save();
                    } else {
                        Stock::create([
                            'branch_id'    => $itemBranchId,
                            'product_id'   => $productId,
                            'qty'          => -$qty,
                            'reserved_qty' => 0,
                        ]);
                    }
                }

                /* ================= STEP 5: UPDATE CUSTOMER LEDGER ================= */
                $difference = $newTotal - $oldTotal;
                if ($difference != 0 && $customerId) {
                    $latestLedger = CustomerLedger::lockForUpdate()
                        ->where('customer_id', $customerId)
                        ->latest('id')
                        ->first();

                    $previousBalance = $latestLedger ? $latestLedger->closing_balance : 0;
                    $newClosing = $previousBalance + $difference;

                    CustomerLedger::create([
                        'customer_id'      => $customerId,
                        'admin_or_user_id' => auth()->id(),
                        'transaction_type' => 'Sale Update',
                        'reference_id'     => (string) $sale->id,
                        'description'      => 'Sale Update Invoice #' . $sale->invoice_no,
                        'previous_balance' => $previousBalance,
                        'opening_balance'  => 0,
                        'total_debit'      => $difference > 0 ? $difference : 0,
                        'total_credit'     => $difference < 0 ? abs($difference) : 0,
                        'closing_balance'  => $newClosing,
                        'created_at'       => $saleDateTime,
                        'updated_at'       => $saleDateTime,
                    ]);
                }

                /* ================= STEP 6: UPDATE RECEIPT VOUCHERS & ACCOUNT LEDGER ================= */
                // 1. Find all old receipt vouchers for this sale (branch-aware and sale-specific)
                $oldReceipts = ReceiptsVoucher::where(function ($q) use ($sale, $branchId) {
                        $q->where('sale_id', $sale->id)
                          ->orWhere(function ($q2) use ($sale, $branchId) {
                              $q2->where('reference_no', $sale->invoice_no)
                                 ->where(function ($q3) use ($sale) {
                                     $q3->whereNull('sale_id')->orWhere('sale_id', $sale->id);
                                 });
                              if ($branchId) {
                                  $q2->where('branch_id', $branchId);
                              }
                          });
                        if ($sale->booking_id) {
                            $q->orWhere(function ($q2) use ($sale, $branchId) {
                                $q2->where('booking_id', $sale->booking_id)
                                   ->where(function ($q3) use ($sale) {
                                       $q3->whereNull('sale_id')->orWhere('sale_id', $sale->id);
                                   });
                                if ($branchId) {
                                    $q2->where('branch_id', $branchId);
                                }
                            });
                        }
                    })
                    ->whereIn('type', ['SALE_RECEIPT', 'RECEIPT'])
                    ->get();

                $oldRvIds = $oldReceipts->pluck('id')->filter()->toArray();

                // 2. Find all old account ledger entries tied to this sale's invoice or its receipt vouchers
                $oldLedgerEntries = AccountLedgerEntry::where(function ($q) use ($sale, $oldRvIds, $branchId) {
                        $q->where(function ($q2) use ($sale, $branchId) {
                            $q2->where('voucher_no', $sale->invoice_no)
                               ->where('voucher_type', 'receipt');
                            if ($branchId) {
                                $q2->where('branch_id', $branchId);
                            }
                        });
                        if (!empty($oldRvIds)) {
                            $q->orWhere(function ($q2) use ($oldRvIds) {
                                $q2->whereIn('voucher_id', $oldRvIds)
                                   ->where('voucher_type', 'receipt');
                            });
                        }
                    })
                    ->get();

                // 3. Track all affected account IDs for balance recalculation
                $affectedAccountIds = $oldLedgerEntries->pluck('account_id')->filter()->map(fn($id) => (int)$id)->unique()->toArray();

                // 4. Delete old ledger entries and old receipts so no duplicates or orphans remain
                if ($oldLedgerEntries->isNotEmpty()) {
                    AccountLedgerEntry::whereIn('id', $oldLedgerEntries->pluck('id'))->delete();
                }
                if ($oldReceipts->isNotEmpty()) {
                    ReceiptsVoucher::whereIn('id', $oldReceipts->pluck('id'))->delete();
                }

                $receiptAccounts = $request->input('receipt_account_id', []);
                $receiptAmounts = $request->input('receipt_amount', []);

                if (is_array($receiptAccounts)) {
                    foreach ($receiptAccounts as $i => $accId) {
                        $amt = floatval($receiptAmounts[$i] ?? 0);
                        if ($amt <= 0 || !$accId) continue;

                        $accId = (int)$accId;
                        $affectedAccountIds[] = $accId;

                        $rv = ReceiptsVoucher::create([
                            'branch_id'        => $branchId,
                            'rvid'             => ReceiptsVoucher::generateRVID(auth()->id()),
                            'receipt_date'     => $saleDateTime ? $saleDateTime->toDateString() : now()->toDateString(),
                            'entry_date'       => $saleDateTime ?: now(),
                            'type'             => 'SALE_RECEIPT',
                            'party_id'         => $customerId,
                            'sale_id'          => $sale->id,
                            'reference_no'     => $sale->invoice_no,
                            'row_account_id'   => $accId,
                            'row_account_head' => 'Cash/Bank',
                            'amount'           => $amt,
                            'total_amount'     => $amt,
                            'processed'        => true,
                            'created_at'       => $saleDateTime ?: now(),
                            'updated_at'       => $saleDateTime ?: now(),
                        ]);

                        try {
                            $rowAccount = Account::lockForUpdate()->find($accId);
                            if ($rowAccount) {
                                // Post to Account Ledger
                                \App\Services\VoucherService::postLedgerEntry(
                                    $accId,
                                    'receipt',
                                    $sale->invoice_no ?? $rv->rvid,
                                    $rv->id,
                                    $saleDateTime ? $saleDateTime->toDateString() : now()->toDateString(),
                                    "Sale Receipt — Invoice #" . ($sale->invoice_no ?? ''),
                                    $amt,
                                    0,
                                    auth()->id()
                                );
                            }
                        } catch (\Exception $e) {
                            \Log::warning('Failed to apply receipt to account', ['error' => $e->getMessage()]);
                        }
                    }
                }

                // 5. Recalculate running balance for all affected accounts to ensure balances are 100% accurate
                foreach (array_unique($affectedAccountIds) as $affectedAccId) {
                    AccountLedgerEntry::recalculateRunningBalance($affectedAccId);
                }

                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'ok'          => true,
                        'sale_id'     => $sale->id,
                        'invoice_no'  => $sale->invoice_no,
                        'invoice_url' => route('sale.invoice', $sale->id),
                        'msg'         => 'Sale #' . $sale->invoice_no . ' updated successfully!',
                    ]);
                }

                return redirect()->route('sale.index')
                    ->with('success', 'Sale #' . $sale->invoice_no . ' updated successfully!');
            });
        } catch (\Exception $e) {
            \Log::error('Sale update failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['ok' => false, 'error' => $e->getMessage()], 422);
            }
            return back()
                ->withError('❌ Error updating sale: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function updatesale(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // --- Arrays from request ---
            $product_ids = $request->product_id;
            $product_names = $request->product ?? []; // ✅ ab match karega
            $product_codes = $request->item_code;
            $brands = $request->brand;  // ✅ request me brand aata hai
            $units = $request->unit;
            $prices = $request->price;
            $discounts = $request->item_disc;
            $quantities = $request->qty;
            $totals = $request->total;
            $colors = $request->color;

            $combined_products = [];
            $combined_codes = [];
            $combined_brands = [];
            $combined_units = [];
            $combined_prices = [];
            $combined_discounts = [];
            $combined_qtys = [];
            $combined_totals = [];
            $combined_colors = [];

            $total_items = 0;

            foreach ($product_ids as $index => $product_id) {
                $qty = $quantities[$index] ?? 0;
                $price = $prices[$index] ?? 0;

                if (! $product_id || ! $qty || ! $price) {
                    continue;
                }

                $combined_products[] = $product_names[$index] ?? '';
                $combined_codes[] = $product_codes[$index] ?? '';
                $combined_brands[] = $brands[$index] ?? '';
                $combined_units[] = $units[$index] ?? '';
                $combined_prices[] = $price;
                $combined_discounts[] = $discounts[$index] ?? 0;
                $combined_qtys[] = $qty;
                $combined_totals[] = $totals[$index] ?? 0;
                $combined_colors[] = json_encode($colors[$index] ?? []);

                $total_items += $qty;
            }

            // --- Find existing Sale ---
            $sale = Sale::findOrFail($id);

            // Save old total before update
            $old_total = $sale->total_net;

            // --- Fill fields ---
            $sale->customer_id = $request->customer_id;
            $sale->reference = $request->reference;
            $sale->product = implode(',', $combined_products);
            $sale->product_code = implode(',', $combined_codes);
            $sale->brand = implode(',', $combined_brands);
            $sale->unit = implode(',', $combined_units);
            $sale->per_price = implode(',', $combined_prices);
            $sale->per_discount = implode(',', $combined_discounts);
            $sale->qty = implode(',', $combined_qtys);
            $sale->per_total = implode(',', $combined_totals);
            $sale->color = json_encode($combined_colors);
            $sale->total_amount_Words = $request->total_amount_Words;
            $sale->total_bill_amount = $request->total_subtotal;
            $sale->total_extradiscount = $request->total_extra_cost;
            $sale->total_net = $request->total_net;
            $sale->cash = $request->cash;
            $sale->card = $request->card;
            $sale->change = $request->change;
            $sale->total_items = $total_items;
            $sale->save();

            // Ledger update
            $customer_id = $request->customer_id;
            $ledger = CustomerLedger::where('customer_id', $customer_id)->latest('id')->first();

            // Difference nikal lo
            $difference = $request->total_net - $old_total;

            if ($ledger) {
                $ledger->previous_balance = $ledger->closing_balance;
                $ledger->closing_balance = $ledger->closing_balance + $difference;
                $ledger->save();
            } else {
                CustomerLedger::create([
                    'customer_id'      => $customer_id,
                    'admin_or_user_id' => auth()->id(),
                    'previous_balance' => 0,
                    'closing_balance'  => $request->total_net,
                    'opening_balance'  => $request->total_net,
                ]);
            }

            DB::commit();

            return redirect()->route('sale.index')->with('success', 'Sale updated successfully!');
        } catch (\Exception $e) {
            DB::rollback();

            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function saleDC1($id)
    {
        $sale = Sale::with('customer')->findOrFail($id);

        // Decode sale pivot or comma fields
        $products = explode(',', $sale->product);
        $codes = explode(',', $sale->product_code);
        $brands = explode(',', $sale->brand);
        $units = explode(',', $sale->unit);
        $prices = explode(',', $sale->per_price);
        $discounts = explode(',', $sale->per_discount);
        $qtys = explode(',', $sale->qty);
        $totals = explode(',', $sale->per_total);
        $colors_json = json_decode($sale->color, true);

        $items = [];

        foreach ($products as $index => $p) {
            $product = Product::where('item_name', trim($p))
                ->orWhere('item_code', trim($codes[$index] ?? ''))
                ->first();

            $items[] = [
                'product_id' => $product->id ?? '',
                'item_name'  => $product->item_name ?? $p,
                'item_code'  => $product->item_code ?? ($codes[$index] ?? ''),
                'brand'      => $product->brand->name ?? ($brands[$index] ?? ''),
                'unit'       => $product->unit ?? ($units[$index] ?? ''),
                'price'      => floatval($prices[$index] ?? 0),
                'discount'   => floatval($discounts[$index] ?? 0),
                'qty'        => intval($qtys[$index] ?? 1),
                'total'      => floatval($totals[$index] ?? 0),
                'color'      => isset($colors_json[$index]) ? json_decode($colors_json[$index], true) : [],
            ];
        }

        return view('admin_panel.sale.saledc', [
            'sale'      => $sale,
            'saleItems' => $items,
        ]);
    }

    public function salerecepit($id)
    {
        $sale = Sale::with('customer')->findOrFail($id);

        // Decode sale pivot or comma fields
        $products = explode(',', $sale->product);
        $codes = explode(',', $sale->product_code);
        $brands = explode(',', $sale->brand);
        $units = explode(',', $sale->unit);
        $prices = explode(',', $sale->per_price);
        $discounts = explode(',', $sale->per_discount);
        $qtys = explode(',', $sale->qty);
        $totals = explode(',', $sale->per_total);
        $colors_json = json_decode($sale->color, true);

        $items = [];

        foreach ($products as $index => $p) {
            $product = Product::where('item_name', trim($p))
                ->orWhere('item_code', trim($codes[$index] ?? ''))
                ->first();

            $items[] = [
                'product_id' => $product->id ?? '',
                'item_name'  => $product->item_name ?? $p,
                'item_code'  => $product->item_code ?? ($codes[$index] ?? ''),
                'brand'      => $product->brand->name ?? ($brands[$index] ?? ''),
                'unit'       => $product->unit ?? ($units[$index] ?? ''),
                'price'      => floatval($prices[$index] ?? 0),
                'discount'   => floatval($discounts[$index] ?? 0),
                'qty'        => intval($qtys[$index] ?? 1),
                'total'      => floatval($totals[$index] ?? 0),
                'color'      => isset($colors_json[$index]) ? json_decode($colors_json[$index], true) : [],
            ];
        }

        return view('admin_panel.sale.salerecepit', [
            'sale'      => $sale,
            'saleItems' => $items,
        ]);
    }


    /* -------- Prints -------- */
    public function invoice(ProductBooking $booking)
    {


        $booking->load([
            'items.product',
            'customer.ledgers'
        ]);

        // return response()->json([
        //     'booking' => $booking   
        // ]);

        $user = Auth::user();
        if ($user && $user->hasRole('super admin')) {
            $branch = Branch::find($booking->branch_id ?? 1) ?? Branch::find(1) ?? (object)['name' => 'ZAIN TRADERS', 'address' => '17th-Brandreth Road, Lahore, Pakistan.', 'mobile' => '0300-4235114 0300-4235114', 'phone' => '042-37635383 042-37651862'];
        } else {
            $branch = ($user ? $user->branch : null) ?? (object)['name' => 'ZAIN TRADERS', 'address' => '17th-Brandreth Road, Lahore, Pakistan.', 'mobile' => '0300-4235114 0300-4235114', 'phone' => '042-37635383 042-37651862'];
        }

        return view('admin_panel.sale.invoice2', compact('booking', 'branch'));
    }

    /**
     * Invoice for Posted Sale (from Sales table)
     * Shows finalized sale data with sale_items
     */
    public function invoiceSale(Sale $sale)
    {
        $sale->load([
            'customer.ledgers',
            'saleItems.product'
        ]);

        // Determine branch to display
        $user = Auth::user();
        if ($user->hasRole('super admin')) {
            // Super admin: show branch 1
            $branch = Branch::find(1) ?? (object)['name' => 'ZAIN TRADERS', 'address' => '17th-Brandreth Road, Lahore, Pakistan.', 'mobile' => '0300-4235114 0300-4235114', 'phone' => '042-37635383 042-37651862'];
        } else {
            // Regular user: show their branch
            $branch = $user->branch ?? (object)['name' => 'ZAIN TRADERS', 'address' => '17th-Brandreth Road, Lahore, Pakistan.', 'mobile' => '0300-4235114 0300-4235114', 'phone' => '042-37635383 042-37651862'];
        }

        return view('admin_panel.sale.invoicesale', compact('sale', 'branch'));
    }

    /**
     * Dedicated A4 Size Printable Invoice
     * Matches user custom PDF format with dynamic settings
     */
    public function invoiceA4(Sale $sale)
    {
        $sale->load([
            'customer.ledgers',
            'saleItems.product',
            'branch'
        ]);

        $settings = SaleSetting::getSettings();

        // Calculate rows padding for fixed A4 structure
        $minRows = $settings->invoice_min_rows ?? 16;
        $itemsCount = $sale->saleItems ? $sale->saleItems->count() : 0;
        $emptyRowsCount = max(0, $minRows - $itemsCount);

        return view('admin_panel.sale.prints.invoice_a4', compact('sale', 'settings', 'emptyRowsCount'));
    }

    public function print2(Sale $sale)
    {
        $sale->load([
            'customer.ledgers',
            'saleItems.product.unit',
            'branch',
            'salesman'
        ]);

        $settings = SaleSetting::getSettings();

        // Determine branch to display
        $user = Auth::user();
        if ($user && $user->hasRole('super admin')) {
            $branch = Branch::find(1) ?? (object)['name' => $settings->invoice_company_name ?? 'GLC'];
        } else {
            $branch = ($user ? $user->branch : null) ?? (object)['name' => $settings->invoice_company_name ?? 'GLC'];
        }

        return view('admin_panel.sale.prints.print2', compact('sale', 'branch', 'settings'));
    }
    public function dc(Sale $sale)
    {
        return view('admin_panel.sale.saledc', compact('sale'));
    }

    public function bookingPrint(Productbooking $booking)
    {
        return view('admin_panel.sale.booking.prints.print', compact('booking'));
    }
    public function bookingPrint2(Productbooking $booking)
    {
        return view('admin_panel.sale.booking.prints.print2', compact('booking'));
    }

      
    
        
       
    

    public function saleDc($sale)
    {
        try {
            if (!($sale instanceof Sale)) {
                $sale = Sale::findOrFail($sale);
            }
            return DB::transaction(function () use ($sale) {
                $sale->load(['customer', 'saleItems.product', 'saleItems.warehouse']);

                // Determine branch to display
                $user = Auth::user();
                if ($user->hasRole('super admin')) {
                    $branch = Branch::find(1) ?? (object)['name' => 'ZAIN TRADERS'];
                } else {
                    $branch = $user->branch ?? Branch::find(1) ?? (object)['name' => 'ZAIN TRADERS'];
                }

                // ✅ FIRST: Check if warehouse_orders already exist for this sale
                $existingOrders = \App\Models\WarehouseOrder::where('sale_id', $sale->id)
                    ->with(['warehouse', 'branch'])  // ✅ Load both relationships
                    ->orderBy('id', 'asc')
                    ->get();

                $dcData = [];

                if ($existingOrders->count() > 0) {
                    // 📦 DC already exists - fetch from database
                    foreach ($existingOrders as $order) {
                        // Get warehouse name or branch name based on delivery type
                        $locationName = '-';
                        if ($order->delivery_location_type === 'branch' && $order->branch) {
                            $locationName = $order->branch->name ?? '-';
                        } elseif ($order->warehouse) {
                            $locationName = $order->warehouse->warehouse_name ?? '-';
                        }
                        
                        $dcData[] = [
                            'dc_no' => $order->dc_no,
                            'warehouse' => $order->warehouse,
                            'branch' => $order->branch,
                            'delivery_location_type' => $order->delivery_location_type,
                            'location_name' => $locationName,
                            'items' => $order->items ?? [],
                            'warehouse_order_id' => $order->id,
                        ];
                    }

                    Log::info('Existing DCs fetched from database', [
                        'sale_id' => $sale->id,
                        'dc_count' => count($dcData)
                    ]);

                } else {
                    // 🆕 DC does not exist - create new ones
                    $groupedItems = $sale->saleItems->groupBy('warehouse_id');

                    foreach ($groupedItems as $warehouseId => $items) {
                        $whId = (!empty($warehouseId) && $warehouseId != '0') ? (int) $warehouseId : null;
                        $deliveryLocationType = $whId ? 'warehouse' : 'branch';

                        // ✅ GENERATE UNIQUE DC NUMBER using dedicated counter
                        $branchForCounter = Branch::lockForUpdate()->find($branch->id ?? 1);
                        if (!$branchForCounter) {
                            $branchForCounter = Branch::lockForUpdate()->find(1);
                        }

                        $branchForCounter->dc_counter = ($branchForCounter->dc_counter ?? 0) + 1;
                        $branchForCounter->save();
                        $dcNo = 'DC-' . str_pad($branchForCounter->dc_counter, 4, '0', STR_PAD_LEFT);

                        // Create a WarehouseOrder for this DC
                        $warehouseOrder = new \App\Models\WarehouseOrder();
                        $warehouseOrder->dc_no = $dcNo;
                        $warehouseOrder->warehouse_id = $whId;
                        $warehouseOrder->delivery_location_type = $deliveryLocationType;
                        $warehouseOrder->branch_id = $sale->branch_id;
                        $warehouseOrder->customer_id = $sale->customer_id;
                        $warehouseOrder->sale_id = $sale->id;
                        $warehouseOrder->status = 'pending';
                        $warehouseOrder->remarks = $sale->remarks ?? null;
                        $warehouseOrder->prepared_by = auth()->user()->name ?? null;
                        $warehouseOrder->created_by = auth()->id();
                        $warehouseOrder->updated_by = auth()->id();

                        // Map sale items into array for storage
                        $itemsArray = $items->map(function($si) use ($whId) {
                            return [
                                'sale_item_id' => $si->id ?? null,
                                'product_id' => $si->product_id ?? null,
                                'product_name' => optional($si->product)->item_name ?? $si->product_name ?? null,
                                'item_code' => optional($si->product)->item_code ?? null,
                                'qty' => $si->sales_qty ?? $si->qty ?? 0,
                                'warehouse_id' => $whId,
                                'retail_price' => isset($si->retail_price) ? (float) $si->retail_price : null,
                                'amount' => isset($si->amount) ? (float) $si->amount : null,
                            ];
                        })->values()->toArray();

                        $warehouseOrder->items = $itemsArray;
                        $warehouseOrder->save();

                        Log::info('New DC created and stored', [
                            'dc_no' => $dcNo,
                            'warehouse_order_id' => $warehouseOrder->id,
                            'sale_id' => $sale->id,
                            'items_count' => count($itemsArray)
                        ]);

                        // ✅ Notify assigned warehouse staff & branch incharge for new DC
                        try {
                            $whName = $whId ? (\App\Models\Warehouse::find($whId)?->warehouse_name ?? 'Warehouse') : 'Branch';
                            $custName = $sale->customer?->customer_name ?? ($sale->sub_customer ?? 'Customer');
                            \App\Models\Notification::create([
                                'branch_id'         => $sale->branch_id,
                                'warehouse_id'      => $whId,
                                'sale_id'           => $sale->id,
                                'customer_id'       => $sale->customer_id,
                                'type'              => 'dc_created',
                                'title'             => 'New DC: ' . $dcNo,
                                'description'       => 'Delivery Challan ' . $dcNo . ' generated for ' . $custName . ' (' . $whName . ')',
                                'notification_date' => \Carbon\Carbon::today(),
                                'status'            => 'pending',
                                'is_read'           => false,
                                'created_by'        => auth()->id(),
                            ]);
                        } catch (\Throwable $e) {
                            \Log::warning('DC Notification creation failed: ' . $e->getMessage());
                        }

                        // ✅ AUDIT: Record in stock_hold table for draft_posted sales
                        if ($sale->status === 'draft_posted') {
                            foreach ($items as $item) {
                                // Get current available stock
                                $warehouseStockQuery = WarehouseStock::where('product_id', $item->product_id)
                                    ->where('branch_id', $sale->branch_id);
                                if ($whId) {
                                    $warehouseStockQuery->where('warehouse_id', $whId);
                                } else {
                                    $warehouseStockQuery->whereNull('warehouse_id');
                                }
                                $warehouseStock = $warehouseStockQuery->first();

                                $availableQty = $warehouseStock ? (float)$warehouseStock->quantity : 0;
                                $deliverQty = (float)($item->sales_qty ?? 0);
                                $remainingQty = max(0, $availableQty - $deliverQty);

                                \App\Models\StockHold::create([
                                    'sale_id' => $sale->id,
                                    'warehouse_order_id' => $warehouseOrder->id,
                                    'product_id' => $item->product_id,
                                    'warehouse_id' => $whId,
                                    'customer_id' => $sale->customer_id,
                                    'invoice_no' => $sale->invoice_no,
                                    'dc_no' => $dcNo,
                                    'available_qty' => $availableQty,
                                    'deliver_qty' => $deliverQty,
                                    'remaining_qty' => $remainingQty,
                                    'product_name' => optional($item->product)->item_name ?? null,
                                    'product_code' => optional($item->product)->item_code ?? null,
                                    'unit_price' => $item->retail_price ?? 0,
                                    'remarks' => $sale->remarks ?? null,
                                    'created_by' => auth()->id(),
                                    'updated_by' => auth()->id(),
                                ]);

                                Log::info('Stock hold recorded', [
                                    'product_id' => $item->product_id,
                                    'invoice_no' => $sale->invoice_no,
                                    'dc_no' => $dcNo,
                                    'available' => $availableQty,
                                    'deliver' => $deliverQty,
                                    'remaining' => $remainingQty,
                                ]);
                            }
                        }

                        $locationName = '-';
                        if (!$whId) {
                            $locationName = $branch->name ?? $branch->branch_name ?? 'Branch';
                        } else {
                            $whObj = \App\Models\Warehouse::find($whId);
                            $locationName = $whObj->warehouse_name ?? '-';
                        }

                        $dcData[] = [
                            'dc_no' => $dcNo,
                            'warehouse' => $whId ? \App\Models\Warehouse::find($whId) : null,
                            'branch' => $items->first()->branch ?? $branch,
                            'delivery_location_type' => $deliveryLocationType,
                            'location_name' => $locationName,
                            'items' => $itemsArray,
                            'warehouse_order_id' => $warehouseOrder->id,
                        ];
                    }
                }

                // Fetch customer remaining items & latest ledger balance
                $remainingItems = \App\Models\CustomerRemaining::where('sale_id', $sale->id)->get();
                $latestLedger = \App\Models\CustomerLedger::where('customer_id', $sale->customer_id)->latest('id')->first();

                // Return the DC view
                return view('admin_panel.sale.booking.prints.dc2', [
                    'sale' => $sale,
                    'dcData' => $dcData,
                    'branch' => $branch,
                    'remainingItems' => $remainingItems,
                    'latestLedger' => $latestLedger
                ]);

            });
        } catch (\Exception $e) {
            Log::error('DC generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'sale_id' => $sale->id
            ]);
            return back()->with('error', 'Error generating DC: ' . $e->getMessage());
        }
    }

    /**
     * Server-rendered thermal DC print
     */
    public function saleDcThermal(Request $request, $sale)
    {
        if (!($sale instanceof Sale)) {
            $sale = Sale::findOrFail($sale);
        }
        $sale->load(['customer', 'saleItems.product', 'saleItems.warehouse']);

        // Determine branch to display
        $user = Auth::user();
        if ($user->hasRole('super admin')) {
            // Super admin: show branch 1
            $branch = Branch::find(1) ?? (object)['name' => 'ZAIN TRADERS'];
        } else {
            // Regular user: show their branch
            $branch = $user->branch ?? (object)['name' => 'ZAIN TRADERS'];
        }

        // 1. Check if warehouse_orders already exist for this sale (same as saleDc)
        $existingOrders = \App\Models\WarehouseOrder::where('sale_id', $sale->id)
            ->with(['warehouse', 'branch'])
            ->orderBy('id', 'asc')
            ->get();

        $dcData = [];

        if ($existingOrders->count() > 0) {
            foreach ($existingOrders as $order) {
                // If a specific warehouse filter is requested, filter accordingly
                if ($request->has('warehouse')) {
                    $requestedWh = (string) $request->get('warehouse');
                    $currentWh = (string) ($order->warehouse_id ?? '');
                    if ($requestedWh !== $currentWh) {
                        continue;
                    }
                }

                $locationName = '-';
                if ($order->delivery_location_type === 'branch' && $order->branch) {
                    $locationName = $order->branch->name ?? '-';
                } elseif ($order->warehouse) {
                    $locationName = $order->warehouse->warehouse_name ?? '-';
                } elseif ($order->branch) {
                    $locationName = $order->branch->name ?? '-';
                }

                // Map stored items into rich objects for the thermal view
                $storedItems = is_string($order->items) ? json_decode($order->items, true) : (is_array($order->items) ? $order->items : []);
                $itemsList = [];

                if (!empty($storedItems)) {
                    foreach ($storedItems as $si) {
                        $pId = $si['product_id'] ?? null;
                        $product = $pId ? \App\Models\Product::find($pId) : null;
                        $whId = $si['warehouse_id'] ?? $order->warehouse_id ?? null;
                        $whObj = $whId ? \App\Models\Warehouse::find($whId) : null;

                        $itemsList[] = (object) [
                            'sales_qty'    => $si['qty'] ?? $si['sales_qty'] ?? 0,
                            'qty'          => $si['qty'] ?? $si['sales_qty'] ?? 0,
                            'product_id'   => $pId,
                            'product'      => $product,
                            'product_name' => $si['product_name'] ?? optional($product)->item_name ?? '-',
                            'item_code'    => $si['item_code'] ?? optional($product)->item_code ?? '',
                            'warehouse_id' => $whId,
                            'warehouse'    => $whObj,
                            'retail_price' => $si['retail_price'] ?? null,
                            'amount'       => $si['amount'] ?? null,
                        ];
                    }
                } else {
                    // Fallback to saleItems for this warehouse/branch if items column is empty
                    $itemsList = $sale->saleItems->filter(function($si) use ($order) {
                        if ($order->delivery_location_type === 'warehouse' && $order->warehouse_id) {
                            return (string)$si->warehouse_id === (string)$order->warehouse_id;
                        }
                        return empty($si->warehouse_id) || $si->warehouse_id == 0;
                    })->values();
                }

                $dcData[] = [
                    'dc_no'                  => $order->dc_no,
                    'warehouse_id'           => $order->warehouse_id,
                    'warehouse'              => $order->warehouse,
                    'branch'                 => $order->branch ?? $branch,
                    'delivery_location_type' => $order->delivery_location_type,
                    'location_name'          => $locationName,
                    'items'                  => $itemsList,
                    'order'                  => $order,
                    'warehouse_order_id'     => $order->id,
                ];
            }
        } else {
            // 2. No warehouse_orders exist yet: create them cleanly matching saleDc
            $groupedItems = $sale->saleItems->groupBy('warehouse_id');

            foreach ($groupedItems as $warehouseId => $items) {
                $whId = (!empty($warehouseId) && $warehouseId != '0') ? (int) $warehouseId : null;
                $deliveryLocationType = $whId ? 'warehouse' : 'branch';

                if ($request->has('warehouse')) {
                    $requestedWh = (string) $request->get('warehouse');
                    $currentWh = (string) ($whId ?? '');
                    if ($requestedWh !== $currentWh) {
                        continue;
                    }
                }

                // Dedicated DC counter per branch
                $branchForCounter = Branch::lockForUpdate()->find($branch->id ?? 1) ?? Branch::lockForUpdate()->find(1);
                $branchForCounter->dc_counter = ($branchForCounter->dc_counter ?? 0) + 1;
                $branchForCounter->save();
                $dcNo = 'DC-' . str_pad($branchForCounter->dc_counter, 4, '0', STR_PAD_LEFT);

                $warehouseOrder = new \App\Models\WarehouseOrder();
                $warehouseOrder->dc_no = $dcNo;
                $warehouseOrder->warehouse_id = $whId; // correctly null if branch delivery
                $warehouseOrder->delivery_location_type = $deliveryLocationType;
                $warehouseOrder->branch_id = $sale->branch_id ?? 1;
                $warehouseOrder->customer_id = $sale->customer_id;
                $warehouseOrder->sale_id = $sale->id;
                $warehouseOrder->status = 'pending';
                $warehouseOrder->remarks = $sale->remarks ?? null;
                $warehouseOrder->prepared_by = auth()->user()->name ?? null;
                $warehouseOrder->created_by = auth()->id();
                $warehouseOrder->updated_by = auth()->id();

                $itemsArray = $items->map(function($si) use ($whId) {
                    return [
                        'sale_item_id' => $si->id ?? null,
                        'product_id'   => $si->product_id ?? null,
                        'product_name' => optional($si->product)->item_name ?? $si->product_name ?? null,
                        'item_code'    => optional($si->product)->item_code ?? null,
                        'qty'          => $si->sales_qty ?? $si->qty ?? 0,
                        'warehouse_id' => $whId,
                        'retail_price' => isset($si->retail_price) ? (float) $si->retail_price : null,
                        'amount'       => isset($si->amount) ? (float) $si->amount : null,
                    ];
                })->values()->toArray();

                $warehouseOrder->items = $itemsArray;
                $warehouseOrder->save();

                $locationName = '-';
                if (!$whId) {
                    $locationName = $branch->name ?? $branch->branch_name ?? 'Branch';
                } else {
                    $whObj = \App\Models\Warehouse::find($whId);
                    $locationName = $whObj->warehouse_name ?? '-';
                }

                $dcData[] = [
                    'dc_no'                  => $dcNo,
                    'warehouse_id'           => $whId,
                    'warehouse'              => $whId ? \App\Models\Warehouse::find($whId) : null,
                    'branch'                 => $branch,
                    'delivery_location_type' => $deliveryLocationType,
                    'location_name'          => $locationName,
                    'items'                  => $items,
                    'order'                  => $warehouseOrder,
                    'warehouse_order_id'     => $warehouseOrder->id,
                ];
            }
        }

        return view('admin_panel.sale.booking.prints.dc2_thermal', [
            'sale'   => $sale,
            'dcData' => $dcData,
            'branch' => $branch
        ]);
    }
    /**
     * Delete a sale record
     */
    public function destroy($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $sale = Sale::with(['saleItems', 'customer'])->lockForUpdate()->findOrFail($id);

                // 1. REVERSE STOCKS (WarehouseStock, Stock, StockMovement)
                if ($sale->saleItems && $sale->saleItems->count() > 0) {
                    foreach ($sale->saleItems as $item) {
                        if (!$item->product_id || !$item->sales_qty) continue;
                        $qty = floatval($item->sales_qty);

                        // WarehouseStock
                        $itemBranch = $item->branch_id ?? $sale->branch_id ?? 1;
                        if ($item->warehouse_id) {
                            $whStock = WarehouseStock::where('product_id', $item->product_id)
                                ->where('warehouse_id', $item->warehouse_id)
                                ->where('branch_id', $itemBranch)
                                ->first();

                            if ($whStock) {
                                $whStock->quantity += $qty;
                                $whStock->save();
                            } else {
                                WarehouseStock::create([
                                    'branch_id'    => $itemBranch,
                                    'warehouse_id' => $item->warehouse_id,
                                    'product_id'   => $item->product_id,
                                    'quantity'     => $qty,
                                ]);
                            }
                        } else {
                            $whStock = WarehouseStock::where('product_id', $item->product_id)
                                ->where('branch_id', $itemBranch)
                                ->whereNull('warehouse_id')
                                ->first();

                            if ($whStock) {
                                $whStock->quantity += $qty;
                                $whStock->save();
                            } else {
                                WarehouseStock::create([
                                    'branch_id'    => $itemBranch,
                                    'warehouse_id' => null,
                                    'product_id'   => $item->product_id,
                                    'quantity'     => $qty,
                                ]);
                            }
                        }

                        // Global Stock
                        $stock = Stock::where('product_id', $item->product_id)
                            ->where('branch_id', $sale->branch_id ?? 1)
                            ->first();

                        if ($stock) {
                            $stock->qty += $qty;
                            $stock->save();
                        } else {
                            Stock::create([
                                'branch_id'    => $sale->branch_id ?? 1,
                                'product_id'   => $item->product_id,
                                'qty'          => $qty,
                                'reserved_qty' => 0,
                            ]);
                        }

                        // Stock Movement Entry
                        StockMovement::create([
                            'branch_id'  => $sale->branch_id ?? 1,
                            'product_id' => $item->product_id,
                            'type'       => 'in',
                            'qty'        => $qty,
                            'ref_type'   => 'SALE_DELETE',
                            'ref_id'     => $sale->id,
                            'ref_uuid'   => $sale->invoice_no,
                            'note'       => 'Sale Deleted - Invoice #' . $sale->invoice_no,
                        ]);
                    }
                }

                // 2. REVERSE RECEIPTS, ACCOUNTS & TRANSACTIONS
                $saleBranchId = $sale->branch_id ?? 1;
                $receipts = ReceiptsVoucher::where(function ($q) use ($sale, $saleBranchId) {
                        $q->where('sale_id', $sale->id)
                          ->orWhere(function ($q2) use ($sale, $saleBranchId) {
                              $q2->where('reference_no', $sale->invoice_no)
                                 ->where(function ($q3) use ($sale) {
                                     $q3->whereNull('sale_id')->orWhere('sale_id', $sale->id);
                                 });
                              if ($saleBranchId) {
                                  $q2->where('branch_id', $saleBranchId);
                              }
                          });
                    })
                    ->get();

                $delRvIds = $receipts->pluck('id')->filter()->toArray();

                foreach ($receipts as $rv) {
                    $rv->delete();
                }

                AccountLedgerEntry::where(function ($q) use ($sale, $delRvIds, $saleBranchId) {
                    $q->where(function ($q2) use ($sale, $saleBranchId) {
                        $q2->where('voucher_no', $sale->invoice_no);
                        if ($saleBranchId) {
                            $q2->where('branch_id', $saleBranchId);
                        }
                    });
                    if (!empty($delRvIds)) {
                        $q->orWhereIn('voucher_id', $delRvIds);
                    }
                })->delete();

                BranchTransaction::where('reference_type', 'Sale')
                    ->where('reference_id', $sale->id)
                    ->delete();

                // 3. SETTLE CUSTOMER LEDGER (IF CUSTOMER EXISTS)
                if ($sale->customer_id) {
                    $customerId = $sale->customer_id;

                    // Delete ledger entries created for this sale
                    CustomerLedger::where('customer_id', $customerId)
                        ->where(function ($q) use ($sale) {
                            $q->where('reference_id', (string) $sale->id)
                              ->orWhere('reference_id', (string) $sale->invoice_no)
                              ->orWhere('description', 'like', '%' . $sale->invoice_no . '%');
                        })
                        ->delete();

                    // Recalculate customer running balance so balance automatically settles
                    self::recalculateCustomerLedger($customerId);
                }

                // 4. RESET LINKED PRODUCT BOOKING (IF CONVERTED FROM BOOKING)
                $booking = null;
                if ($sale->booking_id) {
                    $booking = ProductBooking::find($sale->booking_id);
                } elseif ($sale->invoice_no) {
                    $booking = ProductBooking::where('invoice_no', $sale->invoice_no)->first();
                }

                if ($booking) {
                    $booking->is_posted = 0;
                    $booking->is_finalized = 0;
                    $booking->status = 'draft';
                    $booking->save();
                }

                // 5. DELETE WAREHOUSE ORDERS / DELIVERY CHALLANS
                $whOrders = WarehouseOrder::where('sale_id', $sale->id)
                    ->get();

                foreach ($whOrders as $wo) {
                    WarehouseOrderItem::where('warehouse_order_id', $wo->id)->delete();
                    $wo->delete();
                }

                // 6. DELETE SALE ITEMS AND SALE RECORD
                $sale->saleItems()->delete();
                $sale->delete();

                if (request()->wantsJson() || request()->ajax()) {
                    return response()->json([
                        'ok' => true,
                        'message' => 'Sale deleted successfully. Stock, customer ledger, and account balances updated.'
                    ]);
                }

                return redirect()->route('sale.index')->with('success', 'Sale invoice #' . $sale->invoice_no . ' deleted successfully. Stock, customer ledger, and account balances updated.');
            });
        } catch (\Exception $e) {
            Log::error('Sale deletion failed', [
                'sale_id' => $id,
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'ok'    => false,
                    'error' => 'Deletion failed: ' . $e->getMessage()
                ], 422);
            }

            return redirect()->back()->with('error', 'Sale deletion failed: ' . $e->getMessage());
        }
    }

    /**
     * Recalculates the running balance chain in CustomerLedger for a specific customer.
     * Ensures previous_balance and closing_balance are mathematically accurate across all entries.
     */
    public static function recalculateCustomerLedger($customerId)
    {
        $customer = Customer::find($customerId);
        if (!$customer) {
            return;
        }

        $ledgers = CustomerLedger::where('customer_id', $customerId)
            ->orderBy('id', 'asc')
            ->get();

        if ($ledgers->isEmpty()) {
            return;
        }

        $runningBalance = (float)($customer->opening_balance ?? 0);

        foreach ($ledgers as $index => $ledger) {
            if ($index === 0 && floatval($ledger->total_debit) == 0 && floatval($ledger->total_credit) == 0 && (empty($ledger->transaction_type) || $ledger->transaction_type === 'Opening Balance')) {
                $runningBalance = (float)($customer->opening_balance ?? 0);
                $ledger->previous_balance = 0;
                $ledger->opening_balance  = $runningBalance;
                $ledger->closing_balance  = $runningBalance;
                $ledger->save();
                continue;
            }

            $prev = $runningBalance;
            $debit = (float)$ledger->total_debit;
            $credit = (float)$ledger->total_credit;
            $runningBalance = $prev + $debit - $credit;

            $ledger->previous_balance = $prev;
            $ledger->closing_balance  = $runningBalance;
            $ledger->save();
        }
    }

    /**
     * ✅ CHECK BOOKING POSTED STATE (for button restoration on page load)
     * Returns is_posted and is_finalized flags so frontend can restore button states
     */
    public function checkBookingPostedState($bookingId)
    {
        try {
            $booking = ProductBooking::findOrFail($bookingId);
            
            return response()->json([
                'ok' => true,
                'booking' => [
                    'id' => $booking->id,
                    'invoice_no' => $booking->invoice_no,
                    'is_posted' => (bool) $booking->is_posted,
                    'is_finalized' => (bool) $booking->is_finalized,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Booking not found'
            ], 404);
        }
    }

    /**
     * ✅ POST & FINALIZE WITH ACCOUNT TRANSFER + LEDGER + PENDING STOCK
     * یہ نیا button (btnPosted3) کے لیے ہے جو:
     * 1. Sale check کرے (پہلے سے posted نہ ہو)
     * 2. Receipt vouchers سے account میں amount transfer کرے
     * 3. Customer Ledger update کرے (اگر credit ہے)
     * 4. Sale items کو "ready for gate pass" mark کرے
     */
    public function finalizePosting(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                // Validate sale exists
                if (!$request->sale_id) {
                    abort(422, 'Sale ID required');
                }

                $sale = Sale::with(['saleItems', 'customer'])->lockForUpdate()->findOrFail($request->sale_id);

                // Check if already finalized
                if ($sale->finalized_at || $sale->is_posted) {
                    abort(422, 'Sale is already finalized or posted');
                }

                Log::info('====== FINALIZE POSTING STARTED ======', [
                    'sale_id' => $sale->id,
                    'invoice' => $sale->invoice_no,
                    'customer_id' => $sale->customer_id,
                    'total' => $sale->total_net
                ]);

                /* ========== STEP 1: PROCESS RECEIPT VOUCHERS & TRANSFER TO ACCOUNTS ========== */
                $finalBranchId = $sale->branch_id ?? 1;
                $receipts = ReceiptsVoucher::where(function ($q) use ($sale, $finalBranchId) {
                        $q->where('sale_id', $sale->id)
                          ->orWhere(function ($q2) use ($sale, $finalBranchId) {
                              $q2->where('reference_no', $sale->invoice_no)
                                 ->where(function ($q3) use ($sale) {
                                     $q3->whereNull('sale_id')->orWhere('sale_id', $sale->id);
                                 });
                              if ($finalBranchId) {
                                  $q2->where('branch_id', $finalBranchId);
                              }
                          });
                    })
                    ->where('type', 'SALE_RECEIPT')
                    ->lockForUpdate()
                    ->get();

                $totalReceiptsProcessed = 0;

                foreach ($receipts as $rv) {
                    if ($rv->processed) {
                        Log::info('Receipt already processed, skipping', ['rv_id' => $rv->id]);
                        continue;
                    }

                    $totalReceipt = floatval($rv->total_amount ?? 0);
                    if ($totalReceipt <= 0) continue;

                    // Get row account IDs and amounts
                    $rowAccountIds = !empty($rv->row_account_id) ? json_decode($rv->row_account_id, true) : [$rv->row_account_id];
                    $rowAmounts = !empty($rv->amount) ? json_decode($rv->amount, true) : [$rv->amount];

                    if (!is_array($rowAccountIds)) {
                        $rowAccountIds = [$rowAccountIds];
                    }
                    if (!is_array($rowAmounts)) {
                        $rowAmounts = [$rowAmounts];
                    }

                    // Process each account
                    foreach ($rowAccountIds as $i => $accId) {
                        $amt = floatval($rowAmounts[$i] ?? '$totalReceipt');
                        if ($amt <= 0 || !$accId) continue;

                        // Transfer to account ledger
                        $account = Account::lockForUpdate()->find($accId);
                        if ($account) {
                            \App\Services\VoucherService::postLedgerEntry(
                                (int)$accId,
                                'receipt',
                                $sale->invoice_no ?? $rv->rvid,
                                $rv->id,
                                Carbon::today()->toDateString(),
                                "Sale Receipt — Invoice #" . ($sale->invoice_no ?? ''),
                                $amt,
                                0,
                                auth()->id()
                            );

                            Log::info('Transferred to account ledger', [
                                'account_id' => $account->id,
                                'account_name' => $account->title,
                                'amount' => $amt,
                            ]);

                            $totalReceiptsProcessed += $amt;
                        }
                    }

                    // Mark receipt as processed
                    $rv->processed = true;
                    $rv->save();

                    Log::info('Receipt marked as processed', ['rv_id' => $rv->id, 'amount' => $totalReceipt]);
                }

                /* ========== STEP 2: UPDATE CUSTOMER LEDGER (IF CREDIT) ========== */
                if ($sale->party_type === 'credit' && $sale->customer_id) {
                    // Get previous balance
                    $lastLedger = CustomerLedger::where('customer_id', $sale->customer_id)
                        ->lockForUpdate()
                        ->latest('id')
                        ->first();

                    $previousBalance = $lastLedger ? $lastLedger->closing_balance : ($sale->customer->opening_balance ?? 0);

                    // Sale amount
                    $saleAmount = floatval($sale->total_net ?? 0);

                    // New closing balance = previous + sale - receipts
                    $newClosingBalance = $previousBalance + $saleAmount - $totalReceiptsProcessed;

                    CustomerLedger::create([
                        'customer_id'      => $sale->customer_id,
                        'admin_or_user_id' => auth()->id(),
                        'transaction_type' => 'Sale',
                        'reference_id'     => (string)$sale->id,
                        'description'      => 'Sale Invoice #' . $sale->invoice_no,
                        'opening_balance'  => $previousBalance,
                        'previous_balance' => $previousBalance,
                        'total_debit'      => $saleAmount,
                        'total_credit'     => $totalReceiptsProcessed,
                        'closing_balance'  => $newClosingBalance,
                    ]);

                    Log::info('Customer ledger created', [
                        'customer_id' => $sale->customer_id,
                        'previous' => $previousBalance,
                        'debit' => $saleAmount,
                        'credit' => $totalReceiptsProcessed,
                        'closing' => $newClosingBalance
                    ]);
                }

                /* ========== STEP 3: MARK SALE ITEMS AS READY FOR GATE PASS ========== */
                // Update sale items with finalization flag
                foreach ($sale->saleItems as $item) {
                    $item->update([
                        'ready_for_delivery' => true,  // Mark as ready for gate pass/DC
                    ]);
                }

                Log::info('Sale items marked ready for delivery', ['count' => $sale->saleItems->count()]);

                /* ========== STEP 4: FINALIZE SALE ========== */
                $sale->update([
                    'finalized_at' => now(),
                    'finalized_by' => auth()->id(),
                    'is_posted' => 1,
                    'posted_at' => now(),
                ]);

                Log::info('====== FINALIZE POSTING COMPLETED ======', [
                    'sale_id' => $sale->id,
                    'receipts_processed' => $totalReceiptsProcessed
                ]);

                return response()->json([
                    'ok' => true,
                    'message' => 'Sale finalized successfully! Accounts updated, ledger created, and items ready for gate pass.',
                    'sale_id' => $sale->id,
                    'invoice' => $sale->invoice_no,
                    'ledger_amount' => $totalReceiptsProcessed
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Finalize posting failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * ✅ POST FOR DELIVERY (btnPosted3)
     * 
     * Similar to ajaxPost (warehouse sale) but:
     * - Does NOT reduce warehouse_stocks or stock quantities
     * - ONLY marks items as ready_for_delivery
     * - Still creates customer ledger for credit customers
     * - Still processes receipts and updates accounts
     * - Still creates sale records
     * 
     * Workflow: User selects warehouse → receipt vouchers → accounts updated → ledger created
     * Items "ready for delivery" instead of stock being deducted
     */
    // public function ajaxPostForDelivery(Request $request)
    // {
    //     log::info('ajaxPostForDelivery called', [
    //         'request' => $request->all()
    //     ]);
    //     try {
    //         return DB::transaction(function () use ($request) {

    //             /* ================= VALIDATION & FETCH BOOKING ================= */
    //             if (!$request->booking_id) {
    //                 abort(422, 'Booking ID required');
    //             }

    //             $booking = Productbooking::with('items')
    //                 ->lockForUpdate()
    //                 ->findOrFail($request->booking_id);

    //             // Warehouse selection is optional for delivery posting (like draft mode)
    //             // Items will be saved to sale_postings and warehouse can be selected/changed later
    //             $warehouseMap = $request->warehouse_id ?? [];
                
    //             Log::info('Delivery Post Started', [
    //                 'booking_id' => $booking->id,
    //                 'invoice' => $booking->invoice_no,
    //                 'items_count' => $booking->items->count()
    //             ]);

    //             // Enforce receipts requirement
    //             $hasValidReceipt = false;
    //             if (!empty($request->receipt_account_id) && is_array($request->receipt_account_id)) {
    //                 foreach ($request->receipt_account_id as $i => $accId) {
    //                     $amt = (float) ($request->receipt_amount[$i] ?? 0);
    //                     if ($amt > 0 && !empty($accId) && is_numeric($accId)) {
    //                         $hasValidReceipt = true;
    //                         break;
    //                     }
    //                 }
    //             }

    //             // Check DB for existing unprocessed receipts for this booking
    //             $existsDbReceipts = ReceiptsVoucher::where(function ($q) use ($booking) {
    //                 $q->where('booking_id', $booking->id)
    //                     ->orWhere('reference_no', $booking->invoice_no)
    //                     ->orWhere('reference_no', 'like', '%"' . $booking->invoice_no . '"%')
    //                     ->orWhere('reference_no', 'like', '%' . $booking->invoice_no . '%');
    //             })
    //                 ->where('type', 'SALE_RECEIPT')
    //                 ->where(function ($q) {
    //                     $q->where('processed', false)->orWhereNull('processed');
    //                 })
    //                 ->exists();

    //             if (! $hasValidReceipt && ! $existsDbReceipts) {
    //                 Log::info('No receipt rows provided; proceeding to post for delivery as credit', ['booking' => $booking->id]);
    //             }

    //             if ($booking->is_posted) {
    //                 abort(422, 'Invoice already posted');
    //             }

    //             /* ================= UPDATE WAREHOUSE IDs ================= */
    //             foreach ($booking->items as $item) {
    //                 $wid = $request->warehouse_id[$item->product_id] ?? null;
    //                 $item->update(['warehouse_id' => $wid]);
                    
    //                 Log::info('Updated booking item with warehouse selection', [
    //                     'product_id' => $item->product_id,
    //                     'warehouse_id' => $wid ?? 'NULL (branch stock)',
    //                 ]);
    //             }

    //             $booking->load('items');

    //             /* ================= CREATE SALE ================= */
    //             $invoiceNo = null;
    //             try {
    //                 if ($booking->branch_id) {
    //                     $branch = Branch::lockForUpdate()->find($booking->branch_id);
    //                     if ($branch) {
    //                         $branch->invoice_counter = ((int) ($branch->invoice_counter ?? 0)) + 1;
    //                         $branch->save();
    //                         $invoiceNo = 'INV-' . str_pad($branch->invoice_counter, 4, '0', STR_PAD_LEFT);
    //                         Log::info('Generated sale invoice for branch', ['branch_id' => $booking->branch_id, 'invoice_no' => $invoiceNo]);
    //                     }
    //                 }
    //             } catch (\Exception $e) {
    //                 Log::error('Failed to generate branch invoice counter', ['branch_id' => $booking->branch_id, 'error' => $e->getMessage()]);
    //             }
                
    //             if (!$invoiceNo) {
    //                 $maxSaleId = Sale::where('branch_id', $booking->branch_id)->max('id') ?? 0;
    //                 $invoiceNo = 'INV-' . str_pad($maxSaleId + 1, 4, '0', STR_PAD_LEFT);
    //             }

    //             $sale = Sale::create([
    //                 'branch_id'        => $booking->branch_id,
    //                 'invoice_no'       => $invoiceNo,
    //                 'manual_invoice'   => $booking->manual_invoice,
    //                 'customer_id'      => $booking->customer_id,
    //                 'sub_customer'     => (($booking->party_type ?? '') === 'walking') ? ($booking->customer_name ?? null) : null,
    //                 'party_type'       => $booking->party_type,
    //                 'address'          => $booking->address,
    //                 'tel'              => $booking->tel,
    //                 'remarks'          => $booking->remarks,
    //                 'sub_total1'       => $booking->sub_total1,
    //                 'sub_total2'       => $booking->sub_total2,
    //                 'discount_percent' => $booking->discount_percent,
    //                 'discount_amount'  => $booking->discount_amount,
    //                 'additional_discount' => $booking->additional_discount ?? 0,
    //                 'extra_charges'    => $booking->extra_charges ?? 0,
    //                 'previous_balance' => $booking->previous_balance,
    //                 'total_balance'    => $booking->total_balance,
    //                 'total_net'        => $booking->sub_total2 ?? 0,
    //                 // ✅ Don't mark as is_posted yet - wait for gate pass/delivery confirmation
    //                 // is_posted will be set when gate pass is generated
    //             ]);

    //             Log::info('Sale record created for delivery posting', [
    //                 'sale_id' => $sale->id,
    //                 'invoice_no' => $sale->invoice_no,
    //                 'booking_id' => $booking->id
    //             ]);

    //             /* ================= CUSTOMER LEDGER (ONLY FOR CREDIT CUSTOMERS) ================= */
    //             if(($booking->party_type ?? '') === 'credit' && $booking->customer_id){
                    
    //                 $lastLedger = CustomerLedger::where('customer_id', $booking->customer_id)
    //                     ->latest('id')
    //                     ->lockForUpdate()
    //                     ->first();

    //                 $customer = Customer::find($booking->customer_id);
    //                 $customerOpeningBalance = $customer->opening_balance ?? 0;

    //                 $previousBalance = $lastLedger ? (float)$lastLedger->closing_balance : $customerOpeningBalance;
    //                 $closingBalance = (float)($booking->total_balance ?? 0);
    //                 $saleAmount = ($booking->sub_total2 ?? 0) - ($booking->additional_discount ?? 0) + ($booking->extra_charges ?? 0);
                    
    //                 $totalReceipts = 0;
    //                 if (!empty($request->receipt_amount) && is_array($request->receipt_amount)) {
    //                     foreach ($request->receipt_amount as $amt) {
    //                         $amt = (float) $amt;
    //                         if ($amt > 0) $totalReceipts += $amt;
    //                     }
    //                 }

    //                 Log::info('Creating customer ledger entry for delivery posting', [
    //                     'invoice' => $booking->invoice_no,
    //                     'customer_id' => $booking->customer_id,
    //                     'previous_balance' => $previousBalance,
    //                     'sale_amount' => $saleAmount,
    //                     'receipts' => $totalReceipts,
    //                 ]);

    //                 CustomerLedger::create([
    //                     'customer_id'        => $booking->customer_id,
    //                     'admin_or_user_id'   => auth()->id(),
    //                     'opening_balance'    => $customerOpeningBalance,
    //                     'previous_balance'   => $previousBalance,
    //                     'total_debit'        => $saleAmount,
    //                     'total_credit'       => $totalReceipts,
    //                     'closing_balance'    => $closingBalance,
    //                 ]);
    //             }

    //             /* ═══════════════════════════════════════════════════════════════ */
    //             /* 🎯 CRITICAL: SAVE TO sale_postings (NOT sale_items) ✅           */
    //             /* Items saved here as "pending" - NOT DEDUCTED FROM STOCK          */
    //             /* Later: Gate Pass processing will deduct from warehouse_stocks    */
    //             /* ═══════════════════════════════════════════════════════════════ */
    //             foreach ($booking->items as $item) {
    //                 $warehouseId = $item->warehouse_id;
    //                 $branchId = $booking->branch_id;
                    
    //                 // Determine source type and source_id
    //                 $sourceType = $warehouseId ? 'warehouse' : 'branch';
    //                 $sourceId = $warehouseId ?? $branchId;

    //                 // ✅ SAVE TO sale_postings with status='pending'
    //                 \App\Models\SalePosting::create([
    //                     'sale_id'      => $sale->id,
    //                     'product_id'   => $item->product_id,
    //                     'qty'          => $item->sales_qty,
    //                     'source_type'  => $sourceType,
    //                     'source_id'    => $sourceId,
    //                     'status'       => 'pending',
    //                 ]);

    //                 Log::info('Saved to sale_postings (delivery posting)', [
    //                     'sale_id' => $sale->id,
    //                     'product_id' => $item->product_id,
    //                     'qty' => $item->sales_qty,
    //                     'source_type' => $sourceType,
    //                     'source_id' => $sourceId
    //                 ]);
    //             }

    //             /* ================= ACCOUNT UPDATE ================= */
    //             $salesHead = AccountHead::where('name', 'like', '%Sales%')->first();
    //             if ($salesHead) {
    //                 $saleAccount = Account::lockForUpdate()->where('head_id', $salesHead->id)->first();
    //                 if ($saleAccount) {
    //                     $saleAccount->opening_balance += $sale->total_net;
    //                     $saleAccount->save();
    //                 }
    //             }

    //             /* ================= PROCESS PAYMENT RECEIPTS ================= */
    //             if (!empty($request->receipt_account_id) && is_array($request->receipt_account_id)) {
    //                 $rowAccountIds = [];
    //                 $rowAmounts = [];
    //                 foreach ($request->receipt_account_id as $i => $accId) {
    //                     $acc = $accId;
    //                     $amt = (float) ($request->receipt_amount[$i] ?? 0);
    //                     if ($amt > 0 && (empty($acc) || !is_numeric($acc))) {
    //                         abort(422, 'Invalid receipt row: amount provided but account missing or invalid at row ' . ($i + 1));
    //                     }
    //                     if (!$acc || $amt <= 0) continue;
    //                     $rowAccountIds[] = (int) $acc;
    //                     $rowAmounts[] = $amt;
    //                 }

    //                 if (!empty($rowAccountIds)) {
    //                     $existsProcessed = ReceiptsVoucher::where('reference_no', $booking->invoice_no)
    //                         ->where('type', 'SALE_RECEIPT')
    //                         ->where('processed', true)
    //                         ->exists();

    //                     if (!$existsProcessed) {
    //                         $receiptVoucher = ReceiptsVoucher::create([
    //                             'booking_id'       => $booking->id,
    //                             'sale_id'          => $sale->id,  // 🎯 ADD SALE_ID FOR INVOICE LINKING
    //                             'reference_no'     => $sale->invoice_no,  // Use sale invoice, not booking invoice
    //                             'type'             => 'SALE_RECEIPT',
    //                             'total_amount'     => array_sum($rowAmounts),
    //                             'row_account_id'   => json_encode($rowAccountIds),
    //                             'amount'           => json_encode($rowAmounts),
    //                             'created_by'       => auth()->id(),
    //                             'processed'        => true,  // Mark as processed since we update accounts immediately below
    //                         ]);

    //                         Log::info('Receipt voucher created and marked processed', [
    //                             'rv_id' => $receiptVoucher->id,
    //                             'total' => $receiptVoucher->total_amount,
    //                             'accounts_count' => count($rowAccountIds)
    //                         ]);

    //                         /* ================= UPDATE ACCOUNTS FOR EACH ROW ================= */
    //                         foreach ($rowAccountIds as $i => $acctId) {
    //                             $rowAmount = $rowAmounts[$i] ?? 0;
    //                             if ($rowAmount <= 0) continue;

    //                             $rowAccount = Account::lockForUpdate()->find($acctId);
    //                             if (!$rowAccount) {
    //                                 Log::error('Account not found for delivery receipt update', [
    //                                     'account_id' => $acctId,
    //                                     'rv_id' => $receiptVoucher->id
    //                                 ]);
    //                                 continue;
    //                             }

    //                             $balanceBefore = $rowAccount->opening_balance ?? 0;
    //                             $accountType = trim(strtolower($rowAccount->type));

    //                             Log::info('Updating account for delivery receipt', [
    //                                 'account_id' => $rowAccount->id,
    //                                 'account_title' => $rowAccount->title,
    //                                 'account_type' => $rowAccount->type,
    //                                 'balance_before' => $balanceBefore,
    //                                 'receipt_amount' => $rowAmount,
    //                             ]);

    //                             if ($accountType === 'debit') {
    //                                 $rowAccount->opening_balance = $balanceBefore + $rowAmount;
    //                                 Log::info('DEBIT account - adding amount', ['old' => $balanceBefore, 'new' => $rowAccount->opening_balance]);
    //                             } else {
    //                                 $rowAccount->opening_balance = $balanceBefore - $rowAmount;
    //                                 Log::info('CREDIT account - subtracting amount', ['old' => $balanceBefore, 'new' => $rowAccount->opening_balance]);
    //                             }

    //                             $rowAccount->save();

    //                             Log::info('Account updated for delivery receipt', [
    //                                 'account_id' => $rowAccount->id,
    //                                 'account_title' => $rowAccount->title,
    //                                 'balance_after' => $rowAccount->opening_balance,
    //                                 'rv_id' => $receiptVoucher->id,
    //                             ]);
    //                         }
    //                     }
    //                 }
    //             }

    //             /* ================= Mark booking as posted with draft status ================= */
    //             $booking->update([
    //                 'is_posted' => 1,
    //                 'posted_at' => now(),
    //                 'status'    => 'draft_posted',  // Mark as draft instead of 'sale'
    //             ]);

    //             Log::info('Booking marked as drafted (pending delivery)', [
    //                 'booking_id' => $booking->id,
    //                 'sale_id' => $sale->id,
    //                 'invoice' => $sale->invoice_no,
    //                 'postings_count' => $booking->items->count()
    //             ]);

    //             return response()->json([
    //                 'ok'              => true,
    //                 'sale_id'         => $sale->id,
    //                 'invoice_no'      => $sale->invoice_no,
    //                 'invoice_url'     => route('sale.invoice', $sale->id),
    //                 'msg'             => 'Posted successfully! Items saved to sale_postings. Ready for delivery gate pass.',
    //                 'mode'            => 'draft_posted',
    //                 'postings_count'  => $booking->items->count()
    //             ]);
    //         });
    //     } catch (\Exception $e) {
    //         Log::error('Post for delivery failed', [
    //             'message' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);
    //         return response()->json([
    //             'ok' => false,
    //             'msg' => $e->getMessage()
    //         ], 422);
    //     }
    // }

    /**
     * ═══════════════════════════════════════════════════════════════════════════════════════
     * 🎯 WAREHOUSE SELECTION METHODS FOR DRAFT_POSTED SALES
     * ═══════════════════════════════════════════════════════════════════════════════════════
     * 
     * Two new methods for handling warehouse selection flow:
     * 1. showWarehouseSelection() - Display warehouse selection form
     * 2. processWarehouseSelection() - Process warehouse selection and create DC + stock_hold
     */

    /**
     * ✅ SHOW WAREHOUSE SELECTION FORM
     * 
     * Display a form where user can select warehouses for each product in a draft_posted sale
     * Before a DC can be generated, user must select the warehouse for delivery
     */
    public function showWarehouseSelection($saleId)
    {
        
        try {
            $sale = Sale::with(['customer', 'saleItems.product'])
                ->findOrFail($saleId);

            // Only allow warehouse selection for draft_posted sales
            if ($sale->status !== 'draft_posted') {
                return back()->with('error', 'This sale is not in draft_posted status. Cannot select warehouse.');
            }

            // Check if WarehouseOrder already exists (means warehouse already selected)
            $existingOrders = \App\Models\WarehouseOrder::where('sale_id', $sale->id)->exists();
            if ($existingOrders) {
                return redirect()->route('sale.dc', $sale->id)
                    ->with('info', 'Warehouse already selected. Generating DC...');
            }

            // Fetch all warehouses that have products from this sale with available stock
            $saleProductIds = $sale->saleItems->pluck('product_id')->toArray();
            
            $isSuperAdmin = Auth::check() && Auth::user()->hasRole('super admin');
            $userBranchId = Auth::check() ? Auth::user()->branch_id : null;
            $selectedBranchId = $sale->branch_id ?: $userBranchId;

            // All warehouse stocks for products in this sale
            $warehouseStocksQuery = WarehouseStock::whereIn('product_id', $saleProductIds)
                ->whereNotNull('warehouse_id')
                ->where(function ($q) {
                    $q->whereExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('branch_warehouse')
                            ->whereColumn('branch_warehouse.branch_id', 'warehouse_stocks.branch_id')
                            ->whereColumn('branch_warehouse.warehouse_id', 'warehouse_stocks.warehouse_id');
                    });
                });

            $branchStocksQuery = WarehouseStock::whereIn('product_id', $saleProductIds);

            if (!$isSuperAdmin && $selectedBranchId) {
                $warehouseStocksQuery->where('branch_id', $selectedBranchId);
                $branchStocksQuery->where('branch_id', $selectedBranchId);
            }

            $warehouseStocks = $warehouseStocksQuery
                ->select('id', 'product_id', 'warehouse_id', 'branch_id', 'quantity', 'price')
                ->get();

            // All branch stocks for products in this sale
            $branchStocks = $branchStocksQuery
                ->select('id', 'product_id', 'warehouse_id', 'branch_id', 'quantity', 'price')
                ->get();

            // All branches & warehouses
            if ($isSuperAdmin) {
                $branches = Branch::all();
                $uniqueWarehouses = Warehouse::all();
            } else {
                $branches = Branch::where('id', $selectedBranchId)->get();
                $saleBranch = Branch::with('warehouses')->find($selectedBranchId);
                $uniqueWarehouses = $saleBranch ? $saleBranch->warehouses : collect();
            }

            Log::info('Showing warehouse selection form', [
                'sale_id'               => $sale->id,
                'invoice'               => $sale->invoice_no,
                'items_count'           => $sale->saleItems->count(),
                'branch_id'             => $sale->branch_id,
                'selected_branch_id'    => $selectedBranchId,
                'warehouses_with_stock' => $warehouseStocks->count(),
                'branches_count'        => $branches->count(),
                'unique_warehouses'     => $uniqueWarehouses->count()
            ]);

            return view('admin_panel.sale.warehouse_select', [
                'sale'             => $sale,
                'warehouseStocks'  => $warehouseStocks,
                'branchStocks'     => $branchStocks,
                'branchOwnStocks'  => $branchStocks,
                'branches'         => $branches,
                'uniqueWarehouses' => $uniqueWarehouses,
                'isSuperAdmin'     => $isSuperAdmin,
                'selectedBranchId' => $selectedBranchId,
            ]);
        } catch (\Exception $e) {
            Log::error('Error showing warehouse selection', [
                'error' => $e->getMessage(),
                'sale_id' => $saleId
            ]);
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * ✅ PROCESS WAREHOUSE SELECTION
     * 
     * Process the warehouse selection submission:
     * 1. Update sale_items with warehouse_id
     * 2. Create WarehouseOrder (DC)
     * 3. Create StockHold entries for audit trail
     * 4. Redirect to DC view/print
     */
    public function processWarehouseSelection(Request $request, $saleId)
    {
        try {
            return DB::transaction(function () use ($request, $saleId) {

                $sale = Sale::with(['customer', 'saleItems.product'])
                    ->lockForUpdate()
                    ->findOrFail($saleId);

                // Validate sale status
                if ($sale->status !== 'draft_posted') {
                    abort(422, 'Sale must be in draft_posted status');
                }

                // ✅ NEW: Get delivery method (warehouse or branch)
                $deliveryMethod = $request->input('delivery_method');
                $deliveryLocationId = $request->input('delivery_location_id');
                
                if (!$deliveryMethod || !$deliveryLocationId) {
                    abort(422, 'Please select a delivery method (Shop or Warehouse) and location');
                }

                // Validate delivery quantities provided
                $deliveryQtyMap = $request->input('delivery_qty');
                if (!$deliveryQtyMap || !is_array($deliveryQtyMap)) {
                    abort(422, 'Delivery quantity required for all products');
                }

                Log::info('Processing warehouse selection', [
                    'sale_id' => $sale->id,
                    'invoice' => $sale->invoice_no,
                    'delivery_method' => $deliveryMethod,
                    'delivery_location_id' => $deliveryLocationId,
                    'delivery_qty_count' => count($deliveryQtyMap)
                ]);

                /* ========== STEP 1: UPDATE SALE ITEMS WITH WAREHOUSE IDS ========== */
                foreach ($sale->saleItems as $item) {
                    // ✅ NEW: Get user-selected delivery quantity
                    $userDeliveryQty = (float)($deliveryQtyMap[$item->product_id] ?? 0);

                    if ($userDeliveryQty <= 0) {
                        abort(422, 'Delivery quantity must be greater than 0 for: ' . optional($item->product)->item_name);
                    }

                    // ✅ NEW: Store location info using proper columns
                    // For branch delivery: delivery_location_type = 'branch', warehouse_id = NULL
                    // For warehouse delivery: delivery_location_type = 'warehouse', warehouse_id = warehouse_id
                    $updateData = [
                        'delivery_location_type' => $deliveryMethod === 'branch' ? 'branch' : 'warehouse',
                    ];

                    if ($deliveryMethod === 'warehouse') {
                        // Warehouse delivery: store warehouse ID
                        $updateData['warehouse_id'] = (int)$deliveryLocationId;
                    } else {
                        // Branch delivery: clear warehouse_id, use branch_id instead
                        $updateData['warehouse_id'] = null;
                    }

                    // Update sale_item with selected warehouse/branch - SAME FOR ALL PRODUCTS
                    $item->update($updateData);

                    Log::info('Updated sale item delivery location', [
                        'sale_item_id' => $item->id,
                        'product_id' => $item->product_id,
                        'delivery_location_type' => $deliveryMethod,
                        'location_id' => $deliveryLocationId,
                        'warehouse_id' => $updateData['warehouse_id'] ?? null,
                        'branch_id' => $item->branch_id,
                        'qty' => $item->sales_qty,
                        'delivery_qty' => $userDeliveryQty
                    ]);
                }

                // Reload to get updated warehouse_ids
                $sale->load('saleItems.product');

                /* ========== STEP 2: CREATE WAREHOUSE ORDER (DC) ========== */
                // Group items by delivery location (warehouse_id for warehouses, branch_id for branches)
                $groupedByLocation = collect();
                
                foreach ($sale->saleItems as $item) {
                    $key = $item->delivery_location_type === 'warehouse' 
                        ? "warehouse-{$item->warehouse_id}"
                        : "branch-{$item->branch_id}";
                    
                    if (!$groupedByLocation->has($key)) {
                        $groupedByLocation->put($key, collect());
                    }
                    $groupedByLocation[$key]->push($item);
                }

                $dcNumbers = [];

                foreach ($groupedByLocation as $locationKey => $items) {
                    // Extract location type and ID from key
                    [$locationType, $locationId] = explode('-', $locationKey);
                    
                    // For warehouse orders, use warehouse ID; for branch, use NULL
                    $warehouseId = $locationType === 'warehouse' ? (int)$locationId : NULL;
                    
                    // Generate DC number using branch counter
                    $branch = Branch::lockForUpdate()->find($sale->branch_id ?? 1) ?? Branch::lockForUpdate()->first();
                    
                    $branch->dc_counter = ($branch->dc_counter ?? 0) + 1;
                    $branch->save();
                    
                    $dcNo = 'DC-' . str_pad($branch->dc_counter, 4, '0', STR_PAD_LEFT);

                    // Build items array for WarehouseOrder using USER-SELECTED delivery quantities
                    $itemsArray = $items->map(function($item) use ($deliveryQtyMap) {
                        // ✅ USE USER-SELECTED delivery qty, not auto-calculated
                        $userDeliveryQty = (float)($deliveryQtyMap[$item->product_id] ?? 0);
                        
                        // Recalculate line amount based on actual delivery qty
                        $lineAmount = $userDeliveryQty * ($item->retail_price ?? 0);

                        return [
                            'sale_item_id' => $item->id,
                            'product_id' => $item->product_id,
                            'product_name' => optional($item->product)->item_name,
                            'item_code' => optional($item->product)->item_code,
                            'qty' => $userDeliveryQty,  // ✅ USER-SELECTED delivery qty
                            'warehouse_id' => $item->warehouse_id,
                            'retail_price' => $item->retail_price,
                            'amount' => $lineAmount,
                        ];
                    })->values()->toArray();

                    // Create WarehouseOrder record
                    $warehouseOrder = \App\Models\WarehouseOrder::create([
                        'dc_no' => $dcNo,
                        'warehouse_id' => $warehouseId,  // NULL for branch deliveries
                        'delivery_location_type' => $locationType === 'warehouse' ? 'warehouse' : 'branch',
                        'branch_id' => $sale->branch_id,  // Always set for tracking
                        'customer_id' => $sale->customer_id,
                        'sale_id' => $sale->id,
                        'status' => 'pending',
                        'remarks' => $sale->remarks ?? null,
                        'prepared_by' => auth()->user()->name ?? null,
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                        'items' => $itemsArray,
                    ]);

                    Log::info('Created warehouse order (DC) with manual delivery quantities', [
                        'dc_no' => $dcNo,
                        'delivery_location_type' => $locationType,
                        'warehouse_id' => $warehouseId,
                        'branch_id' => $sale->branch_id,
                        'warehouse_order_id' => $warehouseOrder->id,
                        'items_count' => count($itemsArray)
                    ]);

                    // ✅ Notify assigned warehouse staff & branch incharge for new DC
                    try {
                        $whName = $warehouseId ? (\App\Models\Warehouse::find($warehouseId)?->warehouse_name ?? 'Warehouse') : 'Branch';
                        $custName = $sale->customer?->customer_name ?? ($sale->sub_customer ?? 'Customer');
                        \App\Models\Notification::create([
                            'branch_id'         => $sale->branch_id,
                            'warehouse_id'      => $warehouseId ?: null,
                            'sale_id'           => $sale->id,
                            'customer_id'       => $sale->customer_id,
                            'type'              => 'dc_created',
                            'title'             => 'New DC: ' . $dcNo,
                            'description'       => 'Delivery Challan ' . $dcNo . ' generated for ' . $custName . ' (' . $whName . ')',
                            'notification_date' => \Carbon\Carbon::today(),
                            'status'            => 'pending',
                            'is_read'           => false,
                            'created_by'        => auth()->id(),
                        ]);
                    } catch (\Throwable $e) {
                        \Log::warning('DC Notification creation failed: ' . $e->getMessage());
                    }

                    $dcNumbers[] = $dcNo;

                    /* ========== STEP 3: CREATE STOCK HOLD ENTRIES + PROCESS DELIVERY QTY ========== */
                    foreach ($items as $item) {
                        // 🔍 DEBUG: Check what warehouse_stocks records exist for this product
                        $allProductStocks = WarehouseStock::where('product_id', $item->product_id)->get();
                        Log::info('ALL warehouse_stocks for product', [
                            'product_id' => $item->product_id,
                            'product_name' => optional($item->product)->item_name,
                            'total_records' => $allProductStocks->count(),
                            'records' => $allProductStocks->map(function($s) {
                                return [
                                    'id' => $s->id,
                                    'warehouse_id' => $s->warehouse_id,
                                    'branch_id' => $s->branch_id,
                                    'quantity' => $s->quantity
                                ];
                            })->toArray()
                        ]);

                        // Query WarehouseStock based on delivery location type
                        $stockQuery = WarehouseStock::where('product_id', $item->product_id)
                            ->where('branch_id', $sale->branch_id);
                        
                        if ($locationType === 'warehouse') {
                            // For warehouse delivery
                            $stockQuery->where('warehouse_id', $warehouseId);
                        } else {
                            // For branch delivery - branch's own stock (where warehouse_id IS NULL)
                            $stockQuery->whereNull('warehouse_id');
                        }
                        
                        $warehouseStock = $stockQuery->first();
                        
                        // 🔍 DEBUG: Log exact query filters and results
                        Log::info('Stock validation - Query Details', [
                            'product_id' => $item->product_id,
                            'sale_branch_id' => $sale->branch_id,
                            'location_type' => $locationType,
                            'warehouse_id_filter' => $warehouseId,
                            'query_filters' => [
                                'product_id' => $item->product_id,
                                'branch_id' => $sale->branch_id,
                                'warehouse_id' => $warehouseId,
                                'location_for_query' => $locationType === 'warehouse' ? $warehouseId : 'NULL'
                            ],
                            'result_found' => $warehouseStock ? 'YES' : 'NO',
                            'result_quantity' => $warehouseStock ? $warehouseStock->quantity : 0
                        ]);

                        $availableQty = $warehouseStock ? (float)$warehouseStock->quantity : 0;
                        $requestedQty = (float)$item->sales_qty;
                        
                        // ✅ USE USER-SELECTED delivery quantity
                        $userDeliveryQty = (float)($deliveryQtyMap[$item->product_id] ?? 0);

                        // 🔍 DEBUG LOGGING
                        Log::info('Stock validation debug', [
                            'product_id' => $item->product_id,
                            'product_name' => optional($item->product)->item_name,
                            'location_type' => $locationType,
                            'warehouse_id' => $warehouseId,
                            'branch_id' => $sale->branch_id,
                            'warehouse_stock_query_result' => [
                                'found' => $warehouseStock ? true : false,
                                'stock_row_id' => $warehouseStock->id ?? null,
                                'stock_quantity' => $availableQty,
                                'stock_warehouse_id' => $warehouseStock->warehouse_id ?? 'NULL',
                                'stock_branch_id' => $warehouseStock->branch_id ?? null,
                            ],
                            'requested_qty' => $requestedQty,
                            'user_delivery_qty' => $userDeliveryQty,
                        ]);

                        // Validate user delivery qty doesn't exceed available
                        if ($userDeliveryQty > $availableQty) {
                            // Check if record exists without branch filter (diagnostic)
                            $diagnosticStock = NULL;
                            if ($locationType === 'warehouse') {
                                $diagnosticStock = WarehouseStock::where('product_id', $item->product_id)
                                    ->where('warehouse_id', $warehouseId)
                                    ->first();
                            }

                            // ✅ If force_sale=1 is passed, allow negative stock instead of aborting
                            $forceSale = (bool)($request->input('force_sale', 0));

                            if (!$forceSale) {
                                Log::error('Delivery quantity exceeds available stock', [
                                    'product' => optional($item->product)->item_name,
                                    'requested' => $userDeliveryQty,
                                    'available' => $availableQty,
                                ]);
                                abort(422, "Delivery quantity ({$userDeliveryQty}) exceeds available stock ({$availableQty}) for: " . optional($item->product)->item_name);
                            }

                            // Force sale allowed — log and continue (stock will go negative)
                            Log::warning('Force sale: allowing negative stock', [
                                'product' => optional($item->product)->item_name,
                                'requested' => $userDeliveryQty,
                                'available' => $availableQty,
                            ]);
                        }

                        // Calculate remainder: what's left after user's delivery choice
                        $remainingQty = max(0, $requestedQty - $userDeliveryQty);

                        // Create stock hold record
                        \App\Models\StockHold::create([
                            'sale_id' => $sale->id,
                            'warehouse_order_id' => $warehouseOrder->id,
                            'product_id' => $item->product_id,
                            'warehouse_id' => $warehouseId,
                            'customer_id' => $sale->customer_id,
                            'invoice_no' => $sale->invoice_no,
                            'dc_no' => $dcNo,
                            'available_qty' => $availableQty,
                            'deliver_qty' => $userDeliveryQty,  // ✅ User-selected qty
                            'remaining_qty' => $remainingQty,
                            'product_name' => optional($item->product)->item_name,
                            'product_code' => optional($item->product)->item_code,
                            'unit_price' => $item->retail_price ?? 0,
                            'remarks' => $sale->remarks ?? null,
                            'created_by' => auth()->id(),
                            'updated_by' => auth()->id(),
                        ]);

                        // ✅ IF REMAINDER EXISTS: Create customer_remaining entry
                        if ($remainingQty > 0) {
                            // Check if customer_remaining exists for this product
                            $existingRemaining = \App\Models\CustomerRemaining::where('sale_id', $sale->id)
                                ->where('product_id', $item->product_id)
                                ->first();

                            if ($existingRemaining) {
                                // Update existing record (nested partial delivery)
                                $existingRemaining->update([
                                    'remaining_qty' => $remainingQty,
                                    'status' => 'pending',
                                    'remarks' => "Partial delivery from {$dcNo}. Delivered: {$userDeliveryQty}, Remainder: {$remainingQty}",
                                    'updated_by' => auth()->id(),
                                ]);
                                Log::info('Updated existing customer_remaining', [
                                    'remaining_id' => $existingRemaining->id,
                                    'remaining_qty' => $remainingQty,
                                    'delivered_qty' => $userDeliveryQty,
                                ]);
                            } else {
                                // Create new customer_remaining entry
                                \App\Models\CustomerRemaining::create([
                                    'sale_id' => $sale->id,
                                    'customer_id' => $sale->customer_id,
                                    'product_id' => $item->product_id,
                                    'warehouse_id' => $warehouseId,
                                    'remaining_qty' => $remainingQty,
                                    'status' => 'pending',
                                    'remarks' => "Partial delivery from {$dcNo}. Delivered: {$userDeliveryQty}, Remainder: {$remainingQty}",
                                    'sub_customer_name' => $sale->sub_customer ?? null,
                                    'created_by' => auth()->id(),
                                    'updated_by' => auth()->id(),
                                ]);
                                Log::info('Created customer_remaining (manual partial delivery)', [
                                    'sale_id' => $sale->id,
                                    'product_id' => $item->product_id,
                                    'remaining_qty' => $remainingQty,
                                    'delivered_qty' => $userDeliveryQty,
                                    'dc_no' => $dcNo,
                                ]);
                            }
                        }

                        Log::info('Stock hold created with manual delivery qty', [
                            'product_id' => $item->product_id,
                            'invoice_no' => $sale->invoice_no,
                            'dc_no' => $dcNo,
                            'available' => $availableQty,
                            'requested' => $requestedQty,
                            'user_delivery' => $userDeliveryQty,
                            'remaining' => $remainingQty
                        ]);
                    }
                }

                /* ========== STEP 4: UPDATE SALE STATUS ========== */
                // Mark sale as having warehouse selected
                $sale->update([
                    'status' => 'warehouse_selected',
                    'updated_at' => now(),
                ]);

                Log::info('Warehouse selection completed with manual delivery quantities', [
                    'sale_id' => $sale->id,
                    'invoice' => $sale->invoice_no,
                    'dc_numbers' => $dcNumbers
                ]);

                /* ========== STEP 5: RESPOND ========== */
                return response()->json([
                    'ok' => true,
                    'message' => 'Delivery created successfully! DC will now be generated.',
                    'sale_id' => $sale->id,
                    'dc_data' => [
                        'dc_numbers' => $dcNumbers,
                        'redirect_url' => route('sale.dc', $sale->id)
                    ]
                ]);

            });
        } catch (\Exception $e) {
            Log::error('Warehouse selection processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'sale_id' => $saleId
            ]);

            $status = 422;
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                $status = $e->getStatusCode();
            }

            return response()->json([
                'ok' => false,
                'error' => $e->getMessage()
            ], $status);
        }
    }

    /**
     * Get detailed itemized movement and delivery history for a sale modal
     */
    public function getMovementDetails($id)
    {
        try {
            $sale = Sale::with([
                'customer', 
                'branch', 
                'saleItems.product.unit', 
                'saleItems.product.brand'
            ])->findOrFail($id);

            // 1. Get all warehouse orders (DCs) for this sale
            $warehouseOrders = DB::table('warehouse_orders')
                ->leftJoin('warehouses', 'warehouse_orders.warehouse_id', '=', 'warehouses.id')
                ->leftJoin('branches', 'warehouse_orders.branch_id', '=', 'branches.id')
                ->where('warehouse_orders.sale_id', $sale->id)
                ->select(
                    'warehouse_orders.*',
                    'warehouses.warehouse_name',
                    'branches.name as branch_name'
                )
                ->orderBy('warehouse_orders.created_at', 'asc')
                ->get();

            $orderIds = $warehouseOrders->pluck('id')->toArray();

            // 2. Get all outward gatepasses for these warehouse orders or invoice_no
            $gatepasses = DB::table('outward_gatepasses')
                ->leftJoin('warehouses', 'outward_gatepasses.warehouse_id', '=', 'warehouses.id')
                ->leftJoin('branches', 'outward_gatepasses.branch_id', '=', 'branches.id')
                ->where(function($q) use ($orderIds, $sale) {
                    if (!empty($orderIds)) {
                        $q->whereIn('outward_gatepasses.order_id', $orderIds);
                    }
                    if ($sale->invoice_no) {
                        $q->orWhere('outward_gatepasses.invoice_no', $sale->invoice_no);
                    }
                })
                ->select(
                    'outward_gatepasses.*',
                    'warehouses.warehouse_name',
                    'branches.name as branch_name'
                )
                ->orderBy('outward_gatepasses.created_at', 'asc')
                ->get();

            // Check if this sale is regular direct delivery
            $hasPendingRemaining = DB::table('customer_remaining')
                ->where('sale_id', $sale->id)
                ->whereIn('status', ['pending', 'partial'])
                ->exists();

            $isRegularDirectDelivery = ($sale->status !== 'draft_posted' && $gatepasses->isEmpty() && $warehouseOrders->isEmpty() && !$hasPendingRemaining);

            // 3. Build product line items breakdown
            $itemsBreakdown = [];
            foreach ($sale->saleItems as $item) {
                $productId = $item->product_id;
                $orderedQty = (float)$item->sales_qty;

                $deliveredQty = 0;

                if ($isRegularDirectDelivery) {
                    $deliveredQty = $orderedQty;
                } else {
                    foreach ($gatepasses as $gp) {
                        $gpItems = json_decode($gp->items, true) ?? [];
                        foreach ($gpItems as $gpi) {
                            if (isset($gpi['product_id']) && $gpi['product_id'] == $productId) {
                                $deliveredQty += (float)($gpi['qty'] ?? 0);
                            }
                        }
                    }
                }

                $remainingQty = max(0, $orderedQty - $deliveredQty);

                $itemsBreakdown[] = [
                    'product_id'   => $productId,
                    'product_name' => $item->product->item_name ?? 'N/A',
                    'item_code'    => $item->product->item_code ?? '',
                    'brand'        => $item->product->brand->name ?? '',
                    'unit'         => $item->product->unit->name ?? $item->unit ?? '',
                    'ordered_qty'  => $orderedQty,
                    'delivered_qty'=> $deliveredQty,
                    'remaining_qty'=> $remainingQty,
                ];
            }

            // 4. Build movement history log
            $movements = [];

            if ($isRegularDirectDelivery) {
                $movements[] = [
                    'type'         => 'Direct Sale Dispatch',
                    'doc_no'       => $sale->invoice_no,
                    'dc_no'        => 'N/A',
                    'date'         => \Carbon\Carbon::parse($sale->created_at)->format('d-m-Y h:i A'),
                    'location'     => $sale->branch->name ?? 'Main Branch',
                    'transporter'  => 'Immediate Counter Delivery',
                    'driver'       => 'N/A',
                    'vehicle'      => 'N/A',
                    'billty_no'    => 'N/A',
                    'items'        => collect($itemsBreakdown)->map(function($i) {
                        return [
                            'product_name' => $i['product_name'],
                            'qty'          => $i['ordered_qty'],
                            'unit'         => $i['unit'],
                        ];
                    })->toArray(),
                    'remarks'      => 'Direct dispatch on sale creation',
                    'status'       => 'Delivered',
                ];
            } else {
                // Collect from Gatepasses
                foreach ($gatepasses as $gp) {
                    $gpItems = json_decode($gp->items, true) ?? [];
                    $formattedItems = [];

                    foreach ($gpItems as $gpi) {
                        $pName = $gpi['product_name'] ?? null;
                        if (!$pName && isset($gpi['product_id'])) {
                            $prod = \App\Models\Product::find($gpi['product_id']);
                            $pName = $prod->item_name ?? 'Product #' . $gpi['product_id'];
                        }

                        $formattedItems[] = [
                            'product_name' => $pName ?? 'N/A',
                            'qty'          => (float)($gpi['qty'] ?? 0),
                            'unit'         => $gpi['unit'] ?? '',
                        ];
                    }

                    $locName = $gp->warehouse_name ? ($gp->warehouse_name . ' (Warehouse)') : ($gp->branch_name ? ($gp->branch_name . ' (Branch)') : 'N/A');

                    $movements[] = [
                        'type'         => 'Outward Gatepass',
                        'doc_no'       => $gp->gatepass_number ?? ('GP #' . $gp->id),
                        'dc_no'        => $gp->dc_no ?? 'N/A',
                        'date'         => \Carbon\Carbon::parse($gp->created_at)->format('d-m-Y h:i A'),
                        'location'     => $locName,
                        'transporter'  => $gp->transporter ?? 'N/A',
                        'driver'       => $gp->driver_name ?? 'N/A',
                        'vehicle'      => $gp->vehicle_number ?? 'N/A',
                        'billty_no'    => $gp->billty_no ?? 'N/A',
                        'items'        => $formattedItems,
                        'remarks'      => $gp->remarks ?? '',
                        'status'       => ucfirst($gp->status ?? 'Delivered'),
                    ];
                }

                // Also add pending DCs if any DC exists without a gatepass
                foreach ($warehouseOrders as $wo) {
                    $hasGP = $gatepasses->where('order_id', $wo->id)->isNotEmpty();
                    if (!$hasGP) {
                        $woItems = is_string($wo->items) ? json_decode($wo->items, true) : ($wo->items ?? []);
                        $formattedItems = [];
                        foreach ($woItems as $woi) {
                            $pName = $woi['product_name'] ?? null;
                            if (!$pName && isset($woi['product_id'])) {
                                $prod = \App\Models\Product::find($woi['product_id']);
                                $pName = $prod->item_name ?? 'Product #' . $woi['product_id'];
                            }
                            $formattedItems[] = [
                                'product_name' => $pName ?? 'N/A',
                                'qty'          => (float)($woi['qty'] ?? 0),
                                'unit'         => $woi['unit'] ?? '',
                            ];
                        }

                        $locName = $wo->warehouse_name ? ($wo->warehouse_name . ' (Warehouse)') : ($wo->branch_name ? ($wo->branch_name . ' (Branch)') : 'N/A');

                        $movements[] = [
                            'type'         => 'Delivery Challan (DC Created)',
                            'doc_no'       => $wo->dc_no ?? ('DC #' . $wo->id),
                            'dc_no'        => $wo->dc_no ?? 'N/A',
                            'date'         => \Carbon\Carbon::parse($wo->created_at)->format('d-m-Y h:i A'),
                            'location'     => $locName,
                            'transporter'  => 'Pending Gatepass',
                            'driver'       => 'Pending',
                            'vehicle'      => 'Pending',
                            'billty_no'    => 'N/A',
                            'items'        => $formattedItems,
                            'remarks'      => 'DC Created, waiting for Outward Gatepass dispatch',
                            'status'       => 'Pending Gatepass',
                        ];
                    }
                }
            }

            return response()->json([
                'success'   => true,
                'sale'      => [
                    'id'             => $sale->id,
                    'invoice_no'     => $sale->invoice_no,
                    'date'           => \Carbon\Carbon::parse($sale->created_at)->format('d-m-Y'),
                    'customer_name'  => optional($sale->customer)->customer_name ?? $sale->sub_customer ?? 'N/A',
                    'party_type'     => ucfirst($sale->party_type ?? 'N/A'),
                    'branch'         => $sale->branch->name ?? 'N/A',
                    'total_ordered'  => collect($itemsBreakdown)->sum('ordered_qty'),
                    'total_delivered'=> collect($itemsBreakdown)->sum('delivered_qty'),
                    'total_remaining'=> collect($itemsBreakdown)->sum('remaining_qty'),
                ],
                'items'     => $itemsBreakdown,
                'movements' => $movements,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching movement details: ' . $e->getMessage()
            ], 500);
        }
    }
}
