<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountHead;
use App\Models\AccountLedgerEntry;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\ExpenseVoucher;
use App\Models\Narration;
use App\Models\PaymentVoucher;
use App\Models\ReceiptsVoucher;
use App\Models\Vendor;
use App\Models\VendorLedger;
use App\Models\JournalVoucher;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{
    public function index($type)
    {
        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasRole('super admin');
        $query = Voucher::where('voucher_type', $type)->latest();
        if (!$isSuperAdmin && $user && $user->branch_id) {
            $query->where(function($q) use ($user) {
                $q->where('from_branch_id', $user->branch_id)
                  ->orWhere('to_branch_id', $user->branch_id);
            });
        }
        $vouchers = $query->get();
        $narration = Narration::where('expense_head', $type)->get();

        return view('admin_panel.accounts.expenses', [
            'vouchers' => $vouchers,
            'type' => $type,
            'narration' => $narration
        ]);
    }


    public function store(Request $request)
    {
        // Validate that arrays are present and match in length
        $request->validate([
            'date' => 'required',
            'type' => 'required',
            'person' => 'required',
            'narration' => 'required',
            'amount' => 'required',
        ]);

        // Loop through each row and create a voucher
        foreach ($request->date as $index => $date) {
            Voucher::create([
                'voucher_type' => $request->sub_head,
                'sales_officer' => auth()->user()->name,
                'date' => $date,
                'type' => $request->type[$index],
                'person' => $request->person[$index],
                'sub_head' => $request->sub_head[$index] ?? null,
                'narration' => $request->narration[$index],
                'amount' => $request->amount[$index],
                'status' => 'draft',
            ]);
        }

        return back()->with('success', 'Vouchers saved successfully!');
    }


    /**
     * Display the specified resource.
     */
    public function show(Voucher $voucher)
    {
        //
    }
    public function receipt($id)
    {
        $voucher = Voucher::findOrFail($id);

        $customerName = $voucher->person; // Default
        $customerAddress = '-';
        $closingBalance = 0;

        //yahan accounts bhi show karwayn all heads 
        // bank cash  
        if ($voucher->type === 'Main Customer' && $voucher->mainCustomer) {
            $customerName = $voucher->mainCustomer->customer_name;
            $customerAddress = $voucher->mainCustomer->address;
            $closingBalance = $voucher->mainCustomer->closing_balance;
        } elseif ($voucher->type === 'Sub Customer' && $voucher->subCustomer) {
            $customerName = $voucher->subCustomer->customer_name;
            $customerAddress = $voucher->subCustomer->address;
            $closingBalance = $voucher->subCustomer->closing_balance;
        }

        return view('admin_panel.accounts.receipt', compact('voucher', 'customerName', 'customerAddress', 'closingBalance'));
    }




    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Voucher $voucher)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Voucher $voucher)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Voucher $voucher)
    {
        //
    }

    protected function formatVoucherParties($voucher): array
    {
        $partyIds   = \App\Services\VoucherService::safeDecodeArray($voucher->party_id);
        $partyTypes = \App\Services\VoucherService::safeDecodeArray($voucher->type);

        if (empty($partyIds) && !empty($voucher->party_id)) {
            $partyIds   = [$voucher->party_id];
            $partyTypes = [$voucher->type];
        }

        if (count($partyIds) > 1) {
            $names = [];
            foreach ($partyIds as $idx => $pId) {
                $pType = $partyTypes[$idx] ?? ($partyTypes[0] ?? 'customer');
                if ($pType === 'customer' || $pType === 'walkin' || $pType === 'SALE_RECEIPT') {
                    $c = DB::table('customers')->where('id', $pId)->value('customer_name');
                    $names[] = $c ?: "Customer #$pId";
                } elseif ($pType === 'vendor') {
                    $v = DB::table('vendors')->where('id', $pId)->value('name');
                    $names[] = $v ?: "Vendor #$pId";
                } elseif (is_numeric($pType)) {
                    $a = DB::table('accounts')->where('id', $pId)->value('title');
                    $names[] = $a ?: "Account #$pId";
                }
            }
            return [
                'type_label' => count($partyIds) . ' Parties',
                'party_name' => implode(', ', $names),
            ];
        } elseif (count($partyIds) === 1) {
            $pId   = $partyIds[0];
            $pType = $partyTypes[0] ?? 'customer';
            $partyName = '-';
            $typeLabel = '-';

            if (is_numeric($pType)) {
                $accountHead = DB::table('account_heads')->where('id', $pType)->first();
                $account = DB::table('accounts')->where('id', $pId)->first();
                $typeLabel = $accountHead->name ?? 'Account';
                $partyName = $account->title ?? '-';
            } elseif ($pType === 'vendor') {
                $vendor = DB::table('vendors')->where('id', $pId)->first();
                $typeLabel = 'Vendor';
                $partyName = $vendor->name ?? '-';
            } elseif ($pType === 'customer') {
                $customer = DB::table('customers')->where('id', $pId)->first();
                $typeLabel = 'Customer';
                $partyName = $customer->customer_name ?? '-';
            } elseif ($pType === 'walkin') {
                $walkin = DB::table('customers')->where('id', $pId)->first();
                $typeLabel = 'Walk-in';
                $partyName = $walkin->customer_name ?? '-';
            } elseif ($pType === 'SALE_RECEIPT') {
                $customer = DB::table('customers')->where('id', $pId)->first();
                $typeLabel = 'Customer';
                $partyName = $customer->customer_name ?? '-';
            }

            return [
                'type_label' => $typeLabel,
                'party_name' => $partyName,
            ];
        }

        return ['type_label' => '-', 'party_name' => '-'];
    }

    public function all_recepit_vochers(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasRole('super admin');
        $selectedBranch = $request->get('branch_id', 'all');

        $query = \App\Models\ReceiptsVoucher::with('branch')->orderBy('id', 'DESC');

        if (!$isSuperAdmin) {
            // Branch user: strictly restrict to their branch
            $userBranchId = $user->branch_id ?? 0;
            $query->where('branch_id', $userBranchId);
        } else {
            // Super Admin: allow filtering by branch
            if ($request->filled('branch_id') && $request->branch_id !== 'all') {
                $query->where('branch_id', $request->branch_id);
            }
        }

        $receipts = $query->get();

        // Fetch active branches for the Super Admin filter
        $branches = $isSuperAdmin ? \App\Models\Branch::where('status', '!=', 'inactive')->orWhereNull('status')->orderBy('name')->get() : collect();

        foreach ($receipts as $voucher) {
            $formatted = $this->formatVoucherParties($voucher);
            $voucher->type_label = $formatted['type_label'];
            $voucher->party_name = $formatted['party_name'];
        }

        return view('admin_panel.vochers.all_recepit_vochers', compact(
            'receipts',
            'branches',
            'isSuperAdmin',
            'selectedBranch'
        ));
    }


    private function getPrintParties($voucher)
    {
        $partyTypes   = \App\Services\VoucherService::safeDecodeArray($voucher->type);
        $partyIds     = \App\Services\VoucherService::safeDecodeArray($voucher->party_id);
        $partyAmounts = \App\Services\VoucherService::safeDecodeArray($voucher->party_amount);
        $partyTels    = \App\Services\VoucherService::safeDecodeArray($voucher->tel);

        if (empty($partyIds) && !empty($voucher->party_id)) {
            $partyIds     = [$voucher->party_id];
            $partyTypes   = [$voucher->type];
            $partyAmounts = [(float)$voucher->total_amount];
            $partyTels    = [$voucher->tel];
        }

        $parties = [];
        foreach ($partyIds as $idx => $pId) {
            $pType = $partyTypes[$idx] ?? ($partyTypes[0] ?? 'customer');
            $pAmt  = isset($partyAmounts[$idx]) && is_numeric($partyAmounts[$idx])
                ? (float)$partyAmounts[$idx]
                : (count($partyIds) === 1 ? (float)$voucher->total_amount : 0);
            $pTel  = $partyTels[$idx] ?? '';
            $pName = '';
            $pHead = '';
            $pBal  = 0;

            if ($pType === 'customer' || $pType === 'walkin' || $pType === 'SALE_RECEIPT') {
                $custId = $pId;
                if (!$custId && isset($voucher->sale_id)) {
                    $sale = \App\Models\Sale::find($voucher->sale_id);
                    $custId = $sale?->customer_id;
                }
                $cust = $custId ? DB::table('customers')->where('id', $custId)->first() : null;
                $pName = $cust?->customer_name ?? '-';
                $pTel  = $pTel ?: ($cust?->mobile ?? '-');
                $pHead = $pType === 'walkin' ? 'Walk-in Customer' : 'Customer Accounts';
                $lastLedger = $custId ? DB::table('customer_ledgers')->where('customer_id', $custId)->orderByDesc('id')->first() : null;
                $pBal = $lastLedger ? (float)$lastLedger->closing_balance : (float)($cust?->opening_balance ?? 0);
            } elseif ($pType === 'vendor') {
                $ven = DB::table('vendors')->where('id', $pId)->first();
                $pName = $ven?->name ?? '-';
                $pTel  = $pTel ?: ($ven?->phone_number ?? '-');
                $pHead = 'Vendor Accounts';
                $lastLedger = DB::table('vendor_ledgers')->where('vendor_id', $pId)->orderByDesc('id')->first();
                $pBal = $lastLedger ? (float)$lastLedger->closing_balance : (float)($ven?->opening_balance ?? 0);
            } elseif (is_numeric($pType)) {
                $acc = DB::table('accounts')->where('id', $pId)->first();
                $accHead = DB::table('account_heads')->where('id', $pType)->first();
                $pName = $acc?->title ?? '-';
                $pTel  = $pTel ?: ($acc?->account_code ?? '-');
                $pHead = $accHead?->name ?? 'Account Head';
                $pBal  = (float)($acc?->opening_balance ?? 0);
            } else {
                $pName = '-';
                $pHead = ucfirst($pType);
            }

            $parties[] = (object)[
                'id'            => $pId,
                'name'          => $pName,
                'customer_name' => $pName,
                'phone'         => $pTel,
                'mobile'        => $pTel,
                'type'          => $pType,
                'head_name'     => $pHead,
                'balance'       => $pBal,
                'amount'        => $pAmt,
            ];
        }

        return $parties;
    }

    public function print($id)
    {
        $voucher = ReceiptsVoucher::findOrFail($id);

        // Decode JSON arrays safely
        $narrations = \App\Services\VoucherService::safeDecodeArray($voucher->narration_id);
        $references = \App\Services\VoucherService::safeDecodeArray($voucher->reference_no);
        $accountHeads = \App\Services\VoucherService::safeDecodeArray($voucher->row_account_head);
        $accounts = \App\Services\VoucherService::safeDecodeArray($voucher->row_account_id);
        $amounts = \App\Services\VoucherService::safeDecodeArray($voucher->amount);

        // Rows build
        $maxRows = max(count($accounts), count($amounts), count($accountHeads), count($references), count($narrations), 1);
        $rows = [];
        for ($index = 0; $index < $maxRows; $index++) {
            $accountId = $accounts[$index] ?? null;
            $narrId = $narrations[$index] ?? null;
            $narration = $narrId ? DB::table('narrations')->where('id', $narrId)->value('narration') : null;
            $ref = $references[$index] ?? null;
            $headVal = $accountHeads[$index] ?? null;
            $accountHead = null;
            if ($headVal) {
                $accountHead = is_numeric($headVal)
                    ? DB::table('account_heads')->where('id', $headVal)->value('name')
                    : $headVal;
            }
            $account = $accountId ? DB::table('accounts')->where('id', $accountId)->first() : null;
            $amount = isset($amounts[$index]) ? (float)$amounts[$index] : (float)($voucher->total_amount ?? 0);

            $rows[] = [
                'narration' => $narration,
                'reference' => $ref,
                'account_head' => $accountHead ?? ($account ? DB::table('account_heads')->where('id', $account->head_id)->value('name') : null),
                'account_name' => $account->title ?? null,
                'account_code' => $account->account_code ?? null,
                'amount' => $amount,
            ];
        }

        $parties = $this->getPrintParties($voucher);
        $party = $parties[0] ?? null;
        $previousBalance = $party ? $party->balance : 0;

        $branch = null;
        if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->branch_id) {
            $branch = DB::table('branches')->where('id', \Illuminate\Support\Facades\Auth::user()->branch_id)->first();
        }

        return view('admin_panel.vochers.print', compact('voucher', 'rows', 'party', 'previousBalance', 'branch', 'parties'));
    }

     public function getAccountsByHead(Request $request, $headId)
    {
        // ✅ BRANCH-AWARE ACCOUNTS: Simple users see only their branch's accounts
        $isSuper = Auth::check() && Auth::user()->hasRole('super admin');
        
        // If super admin and a specific branch is requested via AJAX
        if ($isSuper && $request->has('branch_id') && $request->branch_id != '') {
            $branchId = $request->branch_id;
        } else {
            $branchId = Auth::check() ? Auth::user()->branch_id : null;
        }
        
        $accounts = Account::where('head_id', $headId)
            ->where('status', 1)
            ->when(!$isSuper && $branchId, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            // If Super Admin selected a specific branch
            ->when($isSuper && $request->has('branch_id') && $request->branch_id != '', function ($q) use ($branchId) {
                 $q->where('branch_id', $branchId);
            })
            ->get();
        
        return response()->json($accounts);
    }
public function getOpeningBalance($type, $id)
{
    if ($type === 'customer' || $type === 'walkin') {
        $customer = Customer::find($id);
        echo "<pre>";
        print_r($customer);
        echo "<pre>";
        dd();
        return response()->json([
            'opening_balance' => $customer->opening_balance ?? 0
        ]);
    }

    // Account case
    $account = AccountHead::find($id);
    return response()->json([
        'opening_balance' => $account->opening_balance ?? 0
    ]);
}




    /**
     * ✅ BRANCH-AWARE: Fetch party list for Receipt Voucher based on type
     * Returns customers, walkin customers, or vendors with their current balances
     */
    public function getReceiptPartyList(Request $request)
    {
        $type = strtolower($request->query('type', ''));
        $isSuper = Auth::check() && Auth::user()->hasRole('super admin');
        
        if ($isSuper && $request->filled('branch_id')) {
            $branchId = $request->branch_id;
        } else {
            $branchId = Auth::check() ? Auth::user()->branch_id : null;
        }

        if ($type === 'customer') {
            $query = Customer::with(['ledgers' => fn($q) => $q->latest('id')]);

            // Branch filter (allow matching branch or unassigned customers)
            if ($branchId && $branchId !== 'all') {
                $query->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            }

            $customers = $query->get()->map(function ($c) {
                $ledger = $c->ledgers->first();
                $closing = $ledger ? (float)$ledger->closing_balance : (float)($c->opening_balance ?? 0);
                return [
                    'id'              => $c->id,
                    'text'            => $c->customer_name,
                    'mobile'          => $c->mobile ?? '',
                    'closing_balance' => $closing,
                ];
            });
            return response()->json($customers);

        } elseif ($type === 'walkin') {
            $query = Customer::whereIn('customer_type', ['walking', 'Walking Customer']);

            if ($branchId && $branchId !== 'all') {
                $query->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            }

            $walkins = $query->get()->map(function ($c) {
                $closing = (float)($c->opening_balance ?? 0);
                return [
                    'id'              => $c->id,
                    'text'            => $c->customer_name,
                    'mobile'          => $c->mobile ?? '',
                    'closing_balance' => $closing,
                ];
            });
            return response()->json($walkins);

        } elseif ($type === 'vendor') {
            $query = \App\Models\Vendor::query();
            if (!$isSuper && $branchId) {
                $query->where('branch_id', $branchId);
            }
            if ($isSuper && $request->has('branch_id') && $request->branch_id != '') {
                $query->where('branch_id', $branchId);
            }

            $vendors = $query->get()->map(function ($v) use ($branchId) {
                $lastLedger = \App\Models\VendorLedger::where('vendor_id', $v->id)
                    ->where('branch_id', $branchId ?? $v->branch_id)
                    ->orderByDesc('id')
                    ->first();
                $closing = $lastLedger ? (float)$lastLedger->closing_balance : (float)($v->opening_balance ?? 0);
                return [
                    'id'              => $v->id,
                    'text'            => $v->name,
                    'mobile'          => $v->phone ?? $v->contact ?? '',
                    'closing_balance' => $closing,
                ];
            });
            return response()->json($vendors);
        } elseif (is_numeric($type)) {
            $query = Account::where('head_id', $type)->where('status', 1);

            if (!$isSuper && $branchId) {
                $query->where('branch_id', $branchId);
            }
            if ($isSuper && $request->has('branch_id') && $request->branch_id != '') {
                $query->where('branch_id', $branchId);
            }

            $accounts = $query->get()->map(function ($acc) {
                return [
                    'id'              => $acc->id,
                    'text'            => $acc->title . ' (' . ($acc->account_code ?? '') . ')',
                    'mobile'          => $acc->account_code ?? '',
                    'closing_balance' => (float)($acc->opening_balance ?? 0),
                ];
            });
            return response()->json($accounts);
        }

        return response()->json([]);
    }


    public function recepit_vochers()
    {
        $narrations = \App\Models\Narration::where('expense_head', 'Receipts Voucher')
            ->pluck('narration', 'id');
        $AccountHeads = AccountHead::get();

        // Check if user is super admin
        $isSuperAdmin = Auth::user()->hasRole('super admin');
        $currentBranch = Auth::user()->branch_id;
        $Branch = \App\Models\Branch::all();

        // Last RVID nikalna
        $lastVoucher = \App\Models\ReceiptsVoucher::latest('id')->first();

        // Next ID generate karna
        $nextId = $lastVoucher ? $lastVoucher->id + 1 : 1;
        $nextRvid = 'RVID-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        return view('admin_panel.vochers.reciepts_vouchers', compact('narrations', 'AccountHeads', 'nextRvid', 'isSuperAdmin', 'currentBranch', 'Branch'));
    }


    public function store_rec_vochers(Request $request)
    {
        DB::beginTransaction();
        try {
            // 1. Generate RVID
            $rvid = $request->rvid ?: \App\Models\ReceiptsVoucher::generateRVID(auth()->id());
            
            // 2. Process Narrations (Handle 'Add New' case)
            $narrationIds = [];
            if ($request->narration_id) {
                foreach ($request->narration_id as $index => $narrId) {
                    $manualText = $request->narration_text[$index] ?? null;
                    if (empty($narrId) && !empty($manualText)) {
                        $newNar = \App\Models\Narration::create([
                            'expense_head' => 'Receipts Voucher',
                            'narration'    => $manualText,
                        ]);
                        $narrationIds[] = (string)$newNar->id;
                    } else {
                        $narrationIds[] = (string)$narrId;
                    }
                }
            }

            // Get proper branch_id from request or auth
            if (Auth::user()->hasRole('super admin') && $request->has('branch_id') && $request->branch_id != '') {
                $branchId = (int) $request->branch_id;
            } else {
                $branchId = (int) (auth()->user()->branch_id ?? 1);
            }

            // Extract party rows (supports array & legacy scalar)
            $partyTypes   = $request->party_type ?? ($request->vendor_type ? (is_array($request->vendor_type) ? $request->vendor_type : [$request->vendor_type]) : []);
            $partyIds     = $request->party_id ?? ($request->vendor_id ? (is_array($request->vendor_id) ? $request->vendor_id : [$request->vendor_id]) : []);
            $partyAmounts = $request->party_amount ?? [];
            $partyTels    = $request->party_tel ?? ($request->tel ? (is_array($request->tel) ? $request->tel : [$request->tel]) : []);

            if (empty($partyAmounts) && count($partyIds) === 1) {
                $partyAmounts = [(float)$request->total_amount];
            }

            $voucherData = [
                'branch_id'        => $branchId,
                'rvid'             => $rvid,
                'receipt_date'     => $request->receipt_date ?? now()->toDateString(),
                'entry_date'       => $request->entry_date ?? now()->toDateString(),
                'type'             => json_encode($partyTypes),
                'party_id'         => json_encode($partyIds),
                'party_amount'     => json_encode($partyAmounts),
                'tel'              => json_encode($partyTels),
                'remarks'          => $request->remarks,
                'narration_id'     => json_encode($narrationIds),
                'reference_no'     => json_encode($request->reference_no),
                'row_account_head' => json_encode($request->row_account_head),
                'row_account_id'   => json_encode($request->row_account_id),
                'discount_value'   => json_encode($request->discount_value),
                'rate'             => json_encode($request->rate),
                'amount'           => json_encode($request->amount),
                'total_amount'     => (float)$request->total_amount,
                'processed'        => true,
                'status'           => 'active',
            ];

            $savedVoucher = ReceiptsVoucher::create($voucherData);

            // Apply ledger effects via VoucherService
            \App\Services\VoucherService::applyReceiptVoucher($savedVoucher, array_merge($request->all(), [
                'party_type'   => $partyTypes,
                'party_id'     => $partyIds,
                'party_amount' => $partyAmounts,
                'party_tel'    => $partyTels,
                'branch_id'    => $branchId,
            ]), auth()->id());

            DB::commit();
            return redirect()->route('all-recepit-vochers')->with('success', "Receipt Voucher #$rvid saved successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Receipt Voucher Store Error: " . $e->getMessage());
            return back()->with('error', "Error saving voucher: " . $e->getMessage());
        }
    }

    public function destroy_receipt($id)
    {
        $voucher = ReceiptsVoucher::findOrFail($id);
        
        if ($voucher->status === 'voided') {
            return back()->with('error', 'Voucher is already voided!');
        }
        
        $success = \App\Services\VoucherService::reverseReceiptVoucher($voucher, auth()->id());
        
        if ($success) {
            return back()->with('success', 'Receipt Voucher voided successfully! Ledgers reversed.');
        } else {
            $err = \App\Services\VoucherService::$lastError;
            return back()->with('error', 'Failed to void Receipt Voucher.' . ($err ? " Error: {$err}" : ''));
        }
    }

    public function edit_receipt($id)
    {
        $voucher = ReceiptsVoucher::findOrFail($id);
        $narrations = \App\Models\Narration::where('expense_head', 'Receipts Voucher')
            ->pluck('narration', 'id');
        $AccountHeads = AccountHead::get();

        $isSuperAdmin = Auth::user()->hasRole('super admin');
        $currentBranch = $voucher->branch_id ?? (Auth::user()->branch_id ?? 1);
        $Branch = \App\Models\Branch::all();

        // Decode rows safely
        $narrationsArray  = \App\Services\VoucherService::safeDecodeArray($voucher->narration_id);
        $referenceArray   = \App\Services\VoucherService::safeDecodeArray($voucher->reference_no);
        $accountHeadArray = \App\Services\VoucherService::safeDecodeArray($voucher->row_account_head);
        $accountIdArray   = \App\Services\VoucherService::safeDecodeArray($voucher->row_account_id);
        $discountArray    = \App\Services\VoucherService::safeDecodeArray($voucher->discount_value);
        $rateArray        = \App\Services\VoucherService::safeDecodeArray($voucher->rate);
        $amountArray      = \App\Services\VoucherService::safeDecodeArray($voucher->amount);

        $maxRows = max(
            count($accountIdArray),
            count($accountHeadArray),
            count($amountArray),
            count($referenceArray),
            count($narrationsArray),
            1
        );

        $rows = [];
        for ($index = 0; $index < $maxRows; $index++) {
            $accId = $accountIdArray[$index] ?? null;
            $headId = $accountHeadArray[$index] ?? null;

            $resolvedHeadId = null;
            if ($headId && is_numeric($headId)) {
                $resolvedHeadId = (int)$headId;
            } elseif ($headId) {
                $head = AccountHead::where('name', $headId)->orWhere('name', 'LIKE', "%{$headId}%")->first();
                $resolvedHeadId = $head?->id;
            }

            if (!$resolvedHeadId && $accId && is_numeric($accId)) {
                $acc = Account::find($accId);
                $resolvedHeadId = $acc?->head_id;
            }

            $headId = $resolvedHeadId;
            $subAccounts = $headId ? Account::where('head_id', $headId)->where('status', 1)->get() : collect();

            $rows[] = [
                'narration_id'     => $narrationsArray[$index] ?? null,
                'reference_no'     => $referenceArray[$index] ?? '',
                'row_account_head' => $headId,
                'row_account_id'   => $accId,
                'discount_value'   => $discountArray[$index] ?? 0,
                'rate'             => $rateArray[$index] ?? 0,
                'amount'           => $amountArray[$index] ?? ($voucher->total_amount ?? 0),
                'sub_accounts'     => $subAccounts,
            ];
        }

        // Decode party rows safely
        $partyTypes   = \App\Services\VoucherService::safeDecodeArray($voucher->type);
        $partyIds     = \App\Services\VoucherService::safeDecodeArray($voucher->party_id);
        $partyAmounts = \App\Services\VoucherService::safeDecodeArray($voucher->party_amount);
        $partyTels    = \App\Services\VoucherService::safeDecodeArray($voucher->tel);

        if (empty($partyIds) && !empty($voucher->party_id)) {
            $partyIds     = [$voucher->party_id];
            $partyTypes   = [$voucher->type];
            $partyAmounts = [(float)$voucher->total_amount];
            $partyTels    = [$voucher->tel];
        }

        $partyRows = [];
        foreach ($partyIds as $idx => $pId) {
            $pType = $partyTypes[$idx] ?? ($partyTypes[0] ?? 'customer');
            $pAmt  = isset($partyAmounts[$idx]) && is_numeric($partyAmounts[$idx]) 
                ? (float)$partyAmounts[$idx] 
                : (count($partyIds) === 1 ? (float)$voucher->total_amount : 0);
            $pTel  = $partyTels[$idx] ?? '';
            $pName = '';
            $pBal  = 0;

            if ($pType === 'customer' || $pType === 'walkin' || $pType === 'SALE_RECEIPT') {
                $cust = Customer::find($pId);
                $pName = $cust?->customer_name ?? '';
                $pTel  = $pTel ?: ($cust?->mobile ?? '');
                $lastLedger = CustomerLedger::where('customer_id', $pId)->latest('id')->first();
                $pBal = $lastLedger ? (float)$lastLedger->closing_balance : (float)($cust?->opening_balance ?? 0);
            } elseif ($pType === 'vendor') {
                $ven = Vendor::find($pId);
                $pName = $ven?->name ?? '';
                $pTel  = $pTel ?: ($ven?->phone_number ?? '');
                $lastLedger = VendorLedger::where('vendor_id', $pId)->latest('id')->first();
                $pBal = $lastLedger ? (float)$lastLedger->closing_balance : (float)($ven?->opening_balance ?? 0);
            } elseif (is_numeric($pType)) {
                $acc = Account::find($pId);
                $pName = $acc?->title ?? '';
                $pTel  = $pTel ?: ($acc?->account_code ?? '');
                $pBal  = (float)($acc?->opening_balance ?? 0);
            }

            $partyRows[] = [
                'type'    => $pType,
                'id'      => $pId,
                'name'    => $pName,
                'tel'     => $pTel,
                'balance' => $pBal,
                'amount'  => $pAmt,
            ];
        }

        $partyName    = $partyRows[0]['name'] ?? '';
        $partyMobile  = $partyRows[0]['tel'] ?? '';
        $partyBalance = $partyRows[0]['balance'] ?? 0;

        return view('admin_panel.vochers.edit', compact(
            'voucher',
            'rows',
            'partyRows',
            'narrations',
            'AccountHeads',
            'Branch',
            'isSuperAdmin',
            'currentBranch',
            'partyName',
            'partyMobile',
            'partyBalance'
        ));
    }

    public function update_receipt(Request $request, $id)
    {
        $voucher = ReceiptsVoucher::findOrFail($id);

        $request->validate([
            'receipt_date' => 'required|date',
            'amount'       => 'required|array',
            'total_amount' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            // Process Narrations
            $narrationIds = [];
            if ($request->narration_id) {
                foreach ($request->narration_id as $index => $narrId) {
                    $manualText = $request->narration_text[$index] ?? null;
                    if (empty($narrId) && !empty($manualText)) {
                        $newNar = \App\Models\Narration::create([
                            'expense_head' => 'Receipts Voucher',
                            'narration'    => $manualText,
                        ]);
                        $narrationIds[] = (string)$newNar->id;
                    } else {
                        $narrationIds[] = (string)$narrId;
                    }
                }
            }

            if (Auth::user()->hasRole('super admin') && $request->filled('branch_id')) {
                $branchId = (int) $request->branch_id;
            } else {
                $branchId = (int) ($voucher->branch_id ?? auth()->user()->branch_id ?? 1);
            }

            $partyTypes   = $request->party_type ?? ($request->vendor_type ? (is_array($request->vendor_type) ? $request->vendor_type : [$request->vendor_type]) : []);
            $partyIds     = $request->party_id ?? ($request->vendor_id ? (is_array($request->vendor_id) ? $request->vendor_id : [$request->vendor_id]) : []);
            $partyAmounts = $request->party_amount ?? [];
            $partyTels    = $request->party_tel ?? ($request->tel ? (is_array($request->tel) ? $request->tel : [$request->tel]) : []);

            if (empty($partyAmounts) && count($partyIds) === 1) {
                $partyAmounts = [(float)$request->total_amount];
            }

            $updateData = array_merge($request->all(), [
                'party_type'   => $partyTypes,
                'party_id'     => $partyIds,
                'party_amount' => $partyAmounts,
                'party_tel'    => $partyTels,
                'narration_id' => $narrationIds,
                'branch_id'    => $branchId,
            ]);

            \App\Services\VoucherService::updateReceiptVoucher($voucher, $updateData, auth()->id());

            DB::commit();
            return redirect()->route('all-recepit-vochers')->with('success', "Receipt Voucher #{$voucher->rvid} updated successfully! Ledgers synchronized.");
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Receipt Voucher Update Error: " . $e->getMessage());
            return back()->with('error', "Error updating voucher: " . $e->getMessage());
        }
    }

    public function Payment_vochers()
    {
        $narrations = \App\Models\Narration::where('expense_head', 'Payment voucher')
            ->pluck('narration', 'id');
        $AccountHeads = AccountHead::get();
        // echo"<pre>";
        // print_r($AccountHeads);
        // echo"</pre>";
        // dd();

        // Last RVID nikalna
        $lastVoucher = \App\Models\PaymentVoucher::latest('id')->first();

        // Next ID generate karna
        $nextId = $lastVoucher ? $lastVoucher->id + 1 : 1;
        $nextPVID = 'PVID-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        return view('admin_panel.vochers.payment_vochers.payment_vouchers', compact('narrations', 'AccountHeads', 'nextPVID'));
    }

    public function store_Pay_vochers(Request $request)
    {
            // echo "<pre>";
            // print_r($request->all());
            // dd();
        DB::beginTransaction();
        try {
            $pvid = PaymentVoucher::generateInvoiceNo();
            $narrationIds = [];

            foreach ($request->narration_id as $index => $narrId) {
                $manualText = $request->narration_text[$index] ?? null;
                $manualType = $request->narration_type_text[$index] ?? 'Manual';

                if (empty($narrId) && !empty($manualText)) {
                    // Auto expense_head set based on voucher type
                    $expenseHead = 'Payment voucher';
                    if (stripos($manualType, 'Receipt') !== false || $request->voucher_type == 'receipt') {
                        $expenseHead = 'Payment voucher';
                    }

                    $new = \App\Models\Narration::create([
                        'expense_head' => $expenseHead,
                        'narration'    => $manualText,
                    ]);

                    $narrationIds[] = (string)$new->id; // store as string → ["7"]
                } else {
                    $narrationIds[] = (string)$narrId; // force string format
                }
            }
            // Branch handling
            if (Auth::user()->hasRole('super admin') && $request->filled('branch_id')) {
                $branchId = (int) $request->branch_id;
            } else {
                $branchId = (int) (auth()->user()->branch_id ?? 1);
            }

            // Party arrays (support multiple parties)
            $partyTypes   = $request->party_type ?? (isset($request->vendor_type) ? (is_array($request->vendor_type) ? $request->vendor_type : [$request->vendor_type]) : ['vendor']);
            $partyIds     = $request->party_id ?? (isset($request->vendor_id) ? (is_array($request->vendor_id) ? $request->vendor_id : [$request->vendor_id]) : []);
            $partyAmounts = $request->party_amount ?? [$request->total_amount];
            $partyTels    = $request->party_tel ?? (isset($request->tel) ? (is_array($request->tel) ? $request->tel : [$request->tel]) : []);

            $voucherData = [
                'branch_id'        => $branchId,
                'pvid'             => $pvid,
                'receipt_date'     => $request->receipt_date,
                'entry_date'       => $request->entry_date ?? now()->toDateString(),
                'type'             => json_encode($partyTypes),
                'party_id'         => json_encode($partyIds),
                'party_amount'     => json_encode($partyAmounts),
                'tel'              => json_encode($partyTels),
                'remarks'          => $request->remarks,
                'narration_id'     => json_encode($narrationIds),
                'reference_no'     => json_encode($request->reference_no),
                'row_account_head' => json_encode($request->row_account_head),
                'row_account_id'   => json_encode($request->row_account_id),
                'discount_value'   => json_encode($request->discount_value),
                'amount'           => json_encode($request->amount),
                'total_amount'     => $request->total_amount,
                'status'           => 'active',
                'processed'        => true,
            ];

            $voucher = PaymentVoucher::create($voucherData);

            \App\Services\VoucherService::applyPaymentVoucher($voucher, array_merge($request->all(), [
                'party_type'   => $partyTypes,
                'party_id'     => $partyIds,
                'party_amount' => $partyAmounts,
                'party_tel'    => $partyTels,
            ]), auth()->id());

            DB::commit();
            return back()->with('success', 'Payment Voucher saved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy_payment($id)
    {
        $voucher = PaymentVoucher::findOrFail($id);
        
        if ($voucher->status === 'voided') {
            return back()->with('error', 'Voucher is already voided!');
        }
        
        $success = \App\Services\VoucherService::reversePaymentVoucher($voucher, auth()->id());
        
        if ($success) {
            return back()->with('success', 'Payment Voucher voided successfully! Ledgers reversed.');
        } else {
            $err = \App\Services\VoucherService::$lastError;
            return back()->with('error', 'Failed to void Payment Voucher.' . ($err ? " Error: {$err}" : ''));
        }
    }

    public function edit_payment($id)
    {
        $voucher = PaymentVoucher::findOrFail($id);
        $narrations = \App\Models\Narration::where('expense_head', 'Payment voucher')
            ->pluck('narration', 'id');
        $AccountHeads = AccountHead::get();

        $isSuperAdmin = Auth::user()->hasRole('super admin');
        $currentBranch = $voucher->branch_id ?? (Auth::user()->branch_id ?? 1);
        $Branch = \App\Models\Branch::all();

        // Decode rows safely
        $narrationsArray  = \App\Services\VoucherService::safeDecodeArray($voucher->narration_id);
        $referenceArray   = \App\Services\VoucherService::safeDecodeArray($voucher->reference_no);
        $accountHeadArray = \App\Services\VoucherService::safeDecodeArray($voucher->row_account_head);
        $accountIdArray   = \App\Services\VoucherService::safeDecodeArray($voucher->row_account_id);
        $amountArray      = \App\Services\VoucherService::safeDecodeArray($voucher->amount);

        $maxRows = max(
            count($accountIdArray),
            count($accountHeadArray),
            count($amountArray),
            count($referenceArray),
            count($narrationsArray),
            1
        );

        $rows = [];
        for ($index = 0; $index < $maxRows; $index++) {
            $accId = $accountIdArray[$index] ?? null;
            $headId = $accountHeadArray[$index] ?? null;

            $resolvedHeadId = null;
            if ($headId && is_numeric($headId)) {
                $resolvedHeadId = (int)$headId;
            } elseif ($headId) {
                $head = AccountHead::where('name', $headId)->orWhere('name', 'LIKE', "%{$headId}%")->first();
                $resolvedHeadId = $head?->id;
            }

            if (!$resolvedHeadId && $accId && is_numeric($accId)) {
                $acc = Account::find($accId);
                $resolvedHeadId = $acc?->head_id;
            }

            $headId = $resolvedHeadId;
            $subAccounts = $headId ? Account::where('head_id', $headId)->where('status', 1)->get() : collect();

            $rows[] = [
                'narration_id'     => $narrationsArray[$index] ?? null,
                'reference_no'     => $referenceArray[$index] ?? '',
                'row_account_head' => $headId,
                'row_account_id'   => $accId,
                'amount'           => $amountArray[$index] ?? ($voucher->total_amount ?? 0),
                'sub_accounts'     => $subAccounts,
            ];
        }

        // Party info (supports multiple parties)
        $partyTypeArray   = \App\Services\VoucherService::safeDecodeArray($voucher->type);
        $partyIdArray     = \App\Services\VoucherService::safeDecodeArray($voucher->party_id);
        $partyAmountArray = \App\Services\VoucherService::safeDecodeArray($voucher->party_amount);
        $partyTelArray    = \App\Services\VoucherService::safeDecodeArray($voucher->tel);

        $maxPartyRows = max(count($partyIdArray), count($partyTypeArray), 1);
        $partyRows = [];

        for ($p = 0; $p < $maxPartyRows; $p++) {
            $pType = $partyTypeArray[$p] ?? ($partyTypeArray[0] ?? 'vendor');
            $pId   = $partyIdArray[$p] ?? ($partyIdArray[0] ?? null);
            $pAmt  = $partyAmountArray[$p] ?? ($voucher->total_amount ?? 0);
            $pTel  = $partyTelArray[$p] ?? '';

            $pName = '';
            $pBal  = 0;

            if ($pType === 'vendor') {
                $ven = Vendor::find($pId);
                $pName = $ven?->name ?? '';
                $pTel  = $pTel ?: ($ven?->phone_number ?? '');
                $lastLedger = VendorLedger::where('vendor_id', $pId)->latest('id')->first();
                $pBal = $lastLedger ? (float)$lastLedger->closing_balance : (float)($ven?->opening_balance ?? 0);
            } elseif ($pType === 'customer' || $pType === 'walkin') {
                $cust = Customer::find($pId);
                $pName = $cust?->customer_name ?? '';
                $pTel  = $pTel ?: ($cust?->mobile ?? '');
                $lastLedger = CustomerLedger::where('customer_id', $pId)->latest('id')->first();
                $pBal = $lastLedger ? (float)$lastLedger->closing_balance : (float)($cust?->opening_balance ?? 0);
            } elseif (is_numeric($pType)) {
                $acc = Account::find($pId);
                $pName = $acc?->title ?? '';
                $pTel  = $pTel ?: ($acc?->account_code ?? '');
                $pBal  = (float)($acc?->opening_balance ?? 0);
            }

            $partyRows[] = [
                'type'    => $pType,
                'id'      => $pId,
                'name'    => $pName,
                'tel'     => $pTel,
                'balance' => $pBal,
                'amount'  => $pAmt,
            ];
        }

        $partyName    = $partyRows[0]['name'] ?? '';
        $partyMobile  = $partyRows[0]['tel'] ?? '';
        $partyBalance = $partyRows[0]['balance'] ?? 0;

        return view('admin_panel.vochers.payment_vochers.edit', compact(
            'voucher',
            'rows',
            'partyRows',
            'narrations',
            'AccountHeads',
            'Branch',
            'isSuperAdmin',
            'currentBranch',
            'partyName',
            'partyMobile',
            'partyBalance'
        ));
    }

    public function update_payment(Request $request, $id)
    {
        $voucher = PaymentVoucher::findOrFail($id);

        $request->validate([
            'receipt_date' => 'required|date',
            'amount'       => 'required|array',
            'total_amount' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            // Process Narrations
            $narrationIds = [];
            if ($request->narration_id) {
                foreach ($request->narration_id as $index => $narrId) {
                    $manualText = $request->narration_text[$index] ?? null;
                    if (empty($narrId) && !empty($manualText)) {
                        $newNar = \App\Models\Narration::create([
                            'expense_head' => 'Payment voucher',
                            'narration'    => $manualText,
                        ]);
                        $narrationIds[] = (string)$newNar->id;
                    } else {
                        $narrationIds[] = (string)$narrId;
                    }
                }
            }

            if (Auth::user()->hasRole('super admin') && $request->filled('branch_id')) {
                $branchId = (int) $request->branch_id;
            } else {
                $branchId = (int) ($voucher->branch_id ?? auth()->user()->branch_id ?? 1);
            }

            $partyTypes   = $request->party_type ?? ($request->vendor_type ? (is_array($request->vendor_type) ? $request->vendor_type : [$request->vendor_type]) : []);
            $partyIds     = $request->party_id ?? ($request->vendor_id ? (is_array($request->vendor_id) ? $request->vendor_id : [$request->vendor_id]) : []);
            $partyAmounts = $request->party_amount ?? [];
            $partyTels    = $request->party_tel ?? ($request->tel ? (is_array($request->tel) ? $request->tel : [$request->tel]) : []);

            $updateData = array_merge($request->all(), [
                'party_type'   => $partyTypes,
                'party_id'     => $partyIds,
                'party_amount' => $partyAmounts,
                'party_tel'    => $partyTels,
                'narration_id' => $narrationIds,
                'branch_id'    => $branchId,
            ]);

            \App\Services\VoucherService::updatePaymentVoucher($voucher, $updateData, auth()->id());

            DB::commit();
            return redirect()->route('all-Payment-vochers')->with('success', "Payment Voucher #{$voucher->pvid} updated successfully! Ledgers synchronized.");
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Payment Voucher Update Error: " . $e->getMessage());
            return back()->with('error', "Error updating voucher: " . $e->getMessage());
        }
    }
    
     public function all_Payment_vochers(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasRole('super admin');
        $userBranchId = $user ? $user->branch_id : 0;
        $branches = \App\Models\Branch::where(function ($q) {
            $q->whereNull('status')->orWhere('status', '!=', 'inactive');
        })->orderBy('name')->get();
        $selectedBranch = $request->get('branch_id', 'all');

        $query = \App\Models\PaymentVoucher::with('branch');

        if (!$isSuperAdmin) {
            $query->where('branch_id', $userBranchId);
        } else {
            if ($request->filled('branch_id') && $request->branch_id !== 'all') {
                $query->where('branch_id', $request->branch_id);
            }
        }

        $receipts = $query->orderBy('id', 'DESC')->get();

        foreach ($receipts as $voucher) {
            $formatted = $this->formatVoucherParties($voucher);
            $voucher->type_label = $formatted['type_label'];
            $voucher->party_name = $formatted['party_name'];
        }

        return view('admin_panel.vochers.payment_vochers.all_payment_vochers', compact('receipts', 'branches', 'isSuperAdmin', 'selectedBranch'));
    }

    public function uploadProof(Request $request, $id)
    {
        $request->validate([
            'receiving_proof' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $voucher = \App\Models\PaymentVoucher::findOrFail($id);

        if ($request->hasFile('receiving_proof')) {
            if ($voucher->receiving_proof) {
                $oldPath = public_path('uploads/receipts/' . $voucher->receiving_proof);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $file = $request->file('receiving_proof');
            $filename = 'proof_' . $voucher->pvid . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Ensure destination directory exists
            $destinationPath = public_path('uploads/receipts');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);

            $voucher->receiving_proof = $filename;
            $voucher->save();

            return back()->with('success', 'Receiving proof uploaded successfully.');
        }

        return back()->with('error', 'Failed to upload receiving proof.');
    }

    public function Paymentprint($id)
    {
        $voucher = \App\Models\PaymentVoucher::findOrFail($id);

        // Decode JSON arrays safely
        $narrations = \App\Services\VoucherService::safeDecodeArray($voucher->narration_id);
        $references = \App\Services\VoucherService::safeDecodeArray($voucher->reference_no);
        $accountHeads = \App\Services\VoucherService::safeDecodeArray($voucher->row_account_head);
        $accounts = \App\Services\VoucherService::safeDecodeArray($voucher->row_account_id);
        $amounts = \App\Services\VoucherService::safeDecodeArray($voucher->amount);

        // 🧾 Build detailed rows
        $maxRows = max(count($accounts), count($amounts), count($accountHeads), count($references), count($narrations), 1);
        $rows = [];
        for ($index = 0; $index < $maxRows; $index++) {
            $accountId = $accounts[$index] ?? null;
            $narrId = $narrations[$index] ?? null;
            $narration = $narrId ? DB::table('narrations')->where('id', $narrId)->value('narration') : null;
            $ref = $references[$index] ?? null;
            $headVal = $accountHeads[$index] ?? null;
            $accountHead = null;
            if ($headVal) {
                $accountHead = is_numeric($headVal)
                    ? DB::table('account_heads')->where('id', $headVal)->value('name')
                    : $headVal;
            }
            $account = $accountId ? DB::table('accounts')->where('id', $accountId)->first() : null;
            $amount = isset($amounts[$index]) ? (float)$amounts[$index] : (float)($voucher->total_amount ?? 0);

            $rows[] = [
                'narration' => $narration,
                'reference' => $ref,
                'account_head' => $accountHead ?? ($account ? DB::table('account_heads')->where('id', $account->head_id)->value('name') : null),
                'account_name' => $account->title ?? null,
                'account_code' => $account->account_code ?? null,
                'amount' => $amount,
            ];
        }

        $parties = $this->getPrintParties($voucher);
        $party = $parties[0] ?? null;
        $previousBalance = $party ? $party->balance : 0;

        $branch = null;
        if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->branch_id) {
            $branch = DB::table('branches')->where('id', \Illuminate\Support\Facades\Auth::user()->branch_id)->first();
        }

        return view('admin_panel.vochers.payment_vochers.print', compact('voucher', 'rows', 'party', 'previousBalance', 'branch', 'parties'));
    }


    public function expense_vochers()
    {
        $narrations = \App\Models\Narration::where('expense_head', 'Expense voucher')
            ->pluck('narration', 'id');
        $AccountHeads = AccountHead::get();

        // Branch info
        $isSuperAdmin = Auth::user()->hasRole('super admin');
        $currentBranch = Auth::user()->branch_id;
        $Branch = \App\Models\Branch::all();

        // Last RVID nikalna
        $lastVoucher = \App\Models\ExpenseVoucher::latest('id')->first();

        // Next ID generate karna
        $nextId = $lastVoucher ? $lastVoucher->id + 1 : 1;
        $nextRvid = 'EVID-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        return view('admin_panel.vochers.expense_vochers.expense_vouchers', compact('narrations', 'AccountHeads', 'nextRvid', 'isSuperAdmin', 'currentBranch', 'Branch'));
    }

    public function store_expense_vochers(Request $request)
    {
        DB::beginTransaction();
        try {
            // Branch resolution
            if (Auth::user()->hasRole('super admin') && $request->has('branch_id') && $request->branch_id != '') {
                $branchId = (int) $request->branch_id;
            } else {
                $branchId = (int) (auth()->user()->branch_id ?? 1);
            }

            $evid = ExpenseVoucher::generateInvoiceNo();
            $narrationIds = [];

            foreach ($request->narration_id as $index => $narrId) {
                $manualText = $request->narration_text[$index] ?? null;
                $manualType = $request->narration_type_text[$index] ?? 'Manual';

                if (empty($narrId) && !empty($manualText)) {
                    // Auto expense_head set based on voucher type
                    $expenseHead = 'Expense voucher';
                    if (stripos($manualType, 'Receipt') !== false || $request->voucher_type == 'receipt') {
                        $expenseHead = 'Expense voucher';
                    }

                    $new = \App\Models\Narration::create([
                        'expense_head' => $expenseHead,
                        'narration'    => $manualText,
                    ]);

                    $narrationIds[] = (string)$new->id; // store as string → ["7"]
                } else {
                    $narrationIds[] = (string)$narrId; // force string format
                }
            }
            // Party arrays (support multiple parties)
            $partyTypes   = $request->party_type ?? (isset($request->vendor_type) ? (is_array($request->vendor_type) ? $request->vendor_type : [$request->vendor_type]) : ['vendor']);
            $partyIds     = $request->party_id ?? (isset($request->vendor_id) ? (is_array($request->vendor_id) ? $request->vendor_id : [$request->vendor_id]) : []);
            $partyAmounts = $request->party_amount ?? [$request->total_amount];
            $partyTels    = $request->party_tel ?? (isset($request->tel) ? (is_array($request->tel) ? $request->tel : [$request->tel]) : []);

            $voucherData = [
                'evid'             => $evid,
                'branch_id'        => $branchId,
                'entry_date'       => $request->entry_date,
                'type'             => json_encode($partyTypes),
                'party_id'         => json_encode($partyIds),
                'party_amount'     => json_encode($partyAmounts),
                'tel'              => json_encode($partyTels),
                'remarks'          => $request->remarks,
                'narration_id'     => json_encode($narrationIds),
                'row_account_head' => json_encode($request->row_account_head),
                'row_account_id'   => json_encode($request->row_account_id),
                'amount'           => json_encode($request->amount),
                'total_amount'     => $request->total_amount,
                'status'           => 'active',
                'processed'        => true,
            ];

            $voucher = ExpenseVoucher::create($voucherData);

            \App\Services\VoucherService::applyExpenseVoucher($voucher, array_merge($request->all(), [
                'party_type'   => $partyTypes,
                'party_id'     => $partyIds,
                'party_amount' => $partyAmounts,
                'party_tel'    => $partyTels,
            ]), auth()->id());

            DB::commit();
            return back()->with('success', 'Expense Voucher saved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function all_expense_vochers(Request $request)
    {
        $isSuperAdmin = Auth::user()->hasRole('super admin');
        $userBranchId = Auth::user()->branch_id;
        $branches = \App\Models\Branch::all();
        $selectedBranch = $request->get('branch_id', 'all');

        $query = \App\Models\ExpenseVoucher::with('branch');

        if (!$isSuperAdmin) {
            $query->where('branch_id', $userBranchId);
        } else {
            if ($request->filled('branch_id') && $request->branch_id !== 'all') {
                $query->where('branch_id', $request->branch_id);
            }
        }

        $receipts = $query->orderBy('id', 'DESC')->get();

        foreach ($receipts as $voucher) {
            $formatted = $this->formatVoucherParties($voucher);
            $voucher->type_label = $formatted['type_label'];
            $voucher->party_name = $formatted['party_name'];
            $voucher->branch_name = $voucher->branch ? $voucher->branch->name : '-';
        }

        return view('admin_panel.vochers.expense_vochers.all_expense_vochers', compact('receipts', 'branches', 'selectedBranch', 'isSuperAdmin'));
    }

    public function destroy_expense($id)
    {
        $voucher = ExpenseVoucher::findOrFail($id);

        if ($voucher->status === 'voided') {
            return back()->with('error', 'Expense Voucher is already voided!');
        }

        $success = \App\Services\VoucherService::reverseExpenseVoucher($voucher, auth()->id());

        if ($success) {
            return back()->with('success', 'Expense Voucher voided successfully! Ledgers reversed.');
        } else {
            $err = \App\Services\VoucherService::$lastError;
            return back()->with('error', 'Failed to void Expense Voucher.' . ($err ? " Error: {$err}" : ''));
        }
    }

    public function edit_expense($id)
    {
        $voucher = ExpenseVoucher::findOrFail($id);
        $narrations = \App\Models\Narration::where('expense_head', 'Expense voucher')
            ->pluck('narration', 'id');
        $AccountHeads = AccountHead::get();

        $isSuperAdmin = Auth::user()->hasRole('super admin');
        $currentBranch = $voucher->branch_id ?? (Auth::user()->branch_id ?? 1);
        $Branch = \App\Models\Branch::all();

        // Decode rows safely
        $narrationsArray  = \App\Services\VoucherService::safeDecodeArray($voucher->narration_id);
        $accountHeadArray = \App\Services\VoucherService::safeDecodeArray($voucher->row_account_head);
        $accountIdArray   = \App\Services\VoucherService::safeDecodeArray($voucher->row_account_id);
        $amountArray      = \App\Services\VoucherService::safeDecodeArray($voucher->amount);

        $maxRows = max(
            count($accountIdArray),
            count($accountHeadArray),
            count($amountArray),
            count($narrationsArray),
            1
        );

        $rows = [];
        for ($index = 0; $index < $maxRows; $index++) {
            $accId = $accountIdArray[$index] ?? null;
            $headId = $accountHeadArray[$index] ?? null;

            $resolvedHeadId = null;
            if ($headId && is_numeric($headId)) {
                $resolvedHeadId = (int)$headId;
            } elseif ($headId) {
                $head = AccountHead::where('name', $headId)->orWhere('name', 'LIKE', "%{$headId}%")->first();
                $resolvedHeadId = $head?->id;
            }

            if (!$resolvedHeadId && $accId && is_numeric($accId)) {
                $acc = Account::find($accId);
                $resolvedHeadId = $acc?->head_id;
            }

            $headId = $resolvedHeadId;
            $subAccounts = $headId ? Account::where('head_id', $headId)->where('status', 1)->get() : collect();

            $rows[] = [
                'narration_id'     => $narrationsArray[$index] ?? null,
                'row_account_head' => $headId,
                'row_account_id'   => $accId,
                'amount'           => $amountArray[$index] ?? ($voucher->total_amount ?? 0),
                'sub_accounts'     => $subAccounts,
            ];
        }

        // Party / Source Info (supports multiple parties)
        $partyTypeArray   = \App\Services\VoucherService::safeDecodeArray($voucher->type);
        $partyIdArray     = \App\Services\VoucherService::safeDecodeArray($voucher->party_id);
        $partyAmountArray = \App\Services\VoucherService::safeDecodeArray($voucher->party_amount);
        $partyTelArray    = \App\Services\VoucherService::safeDecodeArray($voucher->tel);

        $maxPartyRows = max(count($partyIdArray), count($partyTypeArray), 1);
        $partyRows = [];

        for ($p = 0; $p < $maxPartyRows; $p++) {
            $pType = $partyTypeArray[$p] ?? ($partyTypeArray[0] ?? 'vendor');
            $pId   = $partyIdArray[$p] ?? ($partyIdArray[0] ?? null);
            $pAmt  = $partyAmountArray[$p] ?? ($voucher->total_amount ?? 0);
            $pTel  = $partyTelArray[$p] ?? '';

            $pName = '';
            $pBal  = 0;

            if ($pType === 'vendor') {
                $ven = Vendor::find($pId);
                $pName = $ven?->name ?? '';
                $pTel  = $pTel ?: ($ven?->phone_number ?? '');
                $lastLedger = VendorLedger::where('vendor_id', $pId)->latest('id')->first();
                $pBal = $lastLedger ? (float)$lastLedger->closing_balance : (float)($ven?->opening_balance ?? 0);
            } elseif ($pType === 'customer' || $pType === 'walkin') {
                $cust = Customer::find($pId);
                $pName = $cust?->customer_name ?? '';
                $pTel  = $pTel ?: ($cust?->mobile ?? '');
                $lastLedger = CustomerLedger::where('customer_id', $pId)->latest('id')->first();
                $pBal = $lastLedger ? (float)$lastLedger->closing_balance : (float)($cust?->opening_balance ?? 0);
            } elseif (is_numeric($pType) || is_numeric($pId)) {
                $accId = is_numeric($pId) ? $pId : $pType;
                $acc = Account::find($accId);
                $pName = $acc?->title ?? '';
                $pTel  = $pTel ?: ($acc?->account_code ?? '');
                $pBal  = (float)($acc?->opening_balance ?? 0);
            }

            $partyRows[] = [
                'type'    => $pType,
                'id'      => $pId,
                'name'    => $pName,
                'tel'     => $pTel,
                'balance' => $pBal,
                'amount'  => $pAmt,
            ];
        }

        $partyName    = $partyRows[0]['name'] ?? '';
        $partyMobile  = $partyRows[0]['tel'] ?? '';
        $partyBalance = $partyRows[0]['balance'] ?? 0;

        return view('admin_panel.vochers.expense_vochers.edit', compact(
            'voucher',
            'rows',
            'partyRows',
            'narrations',
            'AccountHeads',
            'Branch',
            'isSuperAdmin',
            'currentBranch',
            'partyName',
            'partyMobile',
            'partyBalance'
        ));
    }

    public function update_expense(Request $request, $id)
    {
        $voucher = ExpenseVoucher::findOrFail($id);

        $request->validate([
            'entry_date'   => 'required|date',
            'amount'       => 'required|array',
            'total_amount' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            // Process Narrations
            $narrationIds = [];
            if ($request->narration_id) {
                foreach ($request->narration_id as $index => $narrId) {
                    $manualText = $request->narration_text[$index] ?? null;
                    if (empty($narrId) && !empty($manualText)) {
                        $newNar = \App\Models\Narration::create([
                            'expense_head' => 'Expense voucher',
                            'narration'    => $manualText,
                        ]);
                        $narrationIds[] = (string)$newNar->id;
                    } else {
                        $narrationIds[] = (string)$narrId;
                    }
                }
            }

            if (Auth::user()->hasRole('super admin') && $request->filled('branch_id')) {
                $branchId = (int) $request->branch_id;
            } else {
                $branchId = (int) ($voucher->branch_id ?? auth()->user()->branch_id ?? 1);
            }

            $partyTypes   = $request->party_type ?? ($request->vendor_type ? (is_array($request->vendor_type) ? $request->vendor_type : [$request->vendor_type]) : []);
            $partyIds     = $request->party_id ?? ($request->vendor_id ? (is_array($request->vendor_id) ? $request->vendor_id : [$request->vendor_id]) : []);
            $partyAmounts = $request->party_amount ?? [];
            $partyTels    = $request->party_tel ?? ($request->tel ? (is_array($request->tel) ? $request->tel : [$request->tel]) : []);

            $updateData = array_merge($request->all(), [
                'party_type'   => $partyTypes,
                'party_id'     => $partyIds,
                'party_amount' => $partyAmounts,
                'party_tel'    => $partyTels,
                'narration_id' => $narrationIds,
                'branch_id'    => $branchId,
            ]);

            \App\Services\VoucherService::updateExpenseVoucher($voucher, $updateData, auth()->id());

            DB::commit();
            return redirect()->route('all-expense-vochers')->with('success', "Expense Voucher #{$voucher->evid} updated successfully! Ledgers synchronized.");
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Expense Voucher Update Error: " . $e->getMessage());
            return back()->with('error', "Error updating voucher: " . $e->getMessage());
        }
    }



    public function expenseprint($id)
    {
        $voucher = \App\Models\ExpenseVoucher::findOrFail($id);

        // Decode JSON arrays safely
        $narrations = \App\Services\VoucherService::safeDecodeArray($voucher->narration_id);
        $references = \App\Services\VoucherService::safeDecodeArray($voucher->reference_no);
        $accountHeads = \App\Services\VoucherService::safeDecodeArray($voucher->row_account_head);
        $accounts = \App\Services\VoucherService::safeDecodeArray($voucher->row_account_id);
        $amounts = \App\Services\VoucherService::safeDecodeArray($voucher->amount);

        // 🧾 Prepare detailed rows
        $maxRows = max(count($accounts), count($amounts), count($accountHeads), count($references), count($narrations), 1);
        $rows = [];
        for ($index = 0; $index < $maxRows; $index++) {
            $accountId = $accounts[$index] ?? null;
            $narrId = $narrations[$index] ?? null;
            $narration = $narrId ? DB::table('narrations')->where('id', $narrId)->value('narration') : null;
            $ref = $references[$index] ?? null;
            $headVal = $accountHeads[$index] ?? null;
            $accountHead = null;
            if ($headVal) {
                $accountHead = is_numeric($headVal)
                    ? DB::table('account_heads')->where('id', $headVal)->value('name')
                    : $headVal;
            }
            $account = $accountId ? DB::table('accounts')->where('id', $accountId)->first() : null;
            $amount = isset($amounts[$index]) ? (float)$amounts[$index] : (float)($voucher->total_amount ?? 0);

            $rows[] = [
                'narration' => $narration,
                'reference' => $ref,
                'account_head' => $accountHead ?? ($account ? DB::table('account_heads')->where('id', $account->head_id)->value('name') : null),
                'account_name' => $account->title ?? null,
                'account_code' => $account->account_code ?? null,
                'amount' => $amount,
            ];
        }

        $parties = $this->getPrintParties($voucher);
        $party = $parties[0] ?? null;
        $previousBalance = $party ? $party->balance : 0;

        $branch = null;
        if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->branch_id) {
            $branch = DB::table('branches')->where('id', \Illuminate\Support\Facades\Auth::user()->branch_id)->first();
        }

        return view('admin_panel.vochers.expense_vochers.print', compact('voucher', 'rows', 'party', 'previousBalance', 'branch', 'parties'));
    }

    // =========================================================================
    // ✅ ERP STANDARD: Account Ledger Entry Posting Helper
    // Called after every voucher save to log transactions into account_ledger_entries
    // =========================================================================
    private function postLedgerEntry(
        int    $accountId,
        string $voucherType,
        string $voucherNo,
        ?int   $voucherId,
        string $date,
        ?string $description,
        float  $debit,
        float  $credit
    ): void {
        $account = \App\Models\Account::find($accountId);
        if (!$account) return;

        // Get last running balance for this account
        $lastEntry = \App\Models\AccountLedgerEntry::where('account_id', $accountId)
            ->orderByDesc('id')
            ->first();

        if ($lastEntry) {
            $previousBalance = (float)$lastEntry->running_balance;
        } else {
            // First ever entry — use account's opening_balance as base
            $openingBalance = (float)($account->opening_balance ?? 0);

            if ($openingBalance != 0) {
                // Post the opening balance as OB entry first
                $obEntryNo = \App\Models\AccountLedgerEntry::generateEntryNo($accountId, 'opening_balance');
                \App\Models\AccountLedgerEntry::create([
                    'account_id'        => $accountId,
                    'branch_id'         => $account->branch_id,
                    'voucher_type'      => 'opening_balance',
                    'voucher_no'        => null,
                    'voucher_id'        => null,
                    'entry_no'          => $obEntryNo,
                    'transaction_date'  => now()->toDateString(),
                    'description'       => 'Opening Balance',
                    'debit'             => $openingBalance >= 0 ? $openingBalance : 0,
                    'credit'            => $openingBalance < 0 ? abs($openingBalance) : 0,
                    'running_balance'   => $openingBalance,
                    'created_by'        => auth()->id(),
                ]);
            }

            $previousBalance = $openingBalance;
        }

        // Calculate new running balance
        $newBalance = $previousBalance + $debit - $credit;

        // Generate sequential entry number (BR-1, CR-1, JV-1 etc.)
        $entryNo = \App\Models\AccountLedgerEntry::generateEntryNo($accountId, $voucherType);

        \App\Models\AccountLedgerEntry::create([
            'account_id'        => $accountId,
            'branch_id'         => $account->branch_id,
            'voucher_type'      => $voucherType,
            'voucher_no'        => $voucherNo,
            'voucher_id'        => $voucherId,
            'entry_no'          => $entryNo,
            'transaction_date'  => $date,
            'description'       => $description ?? ucfirst($voucherType) . ' Voucher #' . $voucherNo,
            'debit'             => $debit,
            'credit'            => $credit,
            'running_balance'   => $newBalance,
            'created_by'        => auth()->id(),
        ]);

        \App\Models\AccountLedgerEntry::recalculateRunningBalance($accountId);
    }

    public function storeNarrationAjax(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'narration' => 'required|string|max:255'
        ]);

        $narration = \App\Models\Narration::create([
            'expense_head' => 'Receipts Voucher',
            'narration'    => $request->narration,
        ]);

        return response()->json([
            'success' => true,
            'id' => $narration->id,
            'text' => $narration->narration
        ]);
    }

    /* =====================================================================
     * ██╗ ██╗ ██╗ ██╗ ██╗██╗    ██╗   ██╗ ██████╗ ██╗   ██╗ ██████╗██╗  ██╗███████╗██████╗
     * ██║██╔╝██╔╝██╔╝██╔╝██║    ██║   ██║██╔═══██╗██║   ██║██╔════╝██║  ██║██╔════╝██╔══██╗
     * JOURNAL VOUCHER (JV) — Double Entry: Customer Credit → Vendor Debit
     * ===================================================================== */

    /**
     * Show Journal Voucher creation form.
     */
    public function journal_voucher_create()
    {
        $narrations    = \App\Models\Narration::where('expense_head', 'Journal Voucher')->pluck('narration', 'id');
        $AccountHeads  = AccountHead::get();
        $isSuperAdmin  = Auth::user()->hasRole('super admin');
        $currentBranch = Auth::user()->branch_id;
        $Branch        = \App\Models\Branch::all();

        $lastId    = (int) JournalVoucher::withTrashed()->max('id');
        $nextJVID  = 'JVID-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);

        return view('admin_panel.vochers.journal_vouchers.create', compact(
            'narrations', 'AccountHeads', 'nextJVID', 'isSuperAdmin', 'currentBranch', 'Branch'
        ));
    }

    /**
     * Store Journal Voucher — posts double-entry to Customer & Vendor ledgers.
     */
    public function journal_voucher_store(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'voucher_date'       => 'required|date',
            'debit_party_type'   => 'required|string',
            'debit_party_id'     => 'required',
            'credit_party_type'  => 'required|string',
            'credit_party_id'    => 'required',
            'amount'             => 'required|numeric|min:0.01',
            'remarks'            => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $jvid   = JournalVoucher::generateJVID();
            $amount = (float) $request->amount;

            // Determine branch
            $branchId = (Auth::user()->hasRole('super admin') && $request->filled('branch_id'))
                ? $request->branch_id
                : (Auth::user()->branch_id ?? 0);

            // Handle narration
            $narrationIds = [];
            if ($request->has('narration_id')) {
                foreach ($request->narration_id as $idx => $narrId) {
                    $manualText = $request->narration_text[$idx] ?? null;
                    if (empty($narrId) && !empty($manualText)) {
                        $new = \App\Models\Narration::create([
                            'expense_head' => 'Journal Voucher',
                            'narration'    => $manualText,
                        ]);
                        $narrationIds[] = (string) $new->id;
                    } else {
                        $narrationIds[] = (string) $narrId;
                    }
                }
            }

            // Save Journal Voucher header
            $jv = JournalVoucher::create([
                'jvid'              => $jvid,
                'voucher_date'      => $request->voucher_date,
                'entry_date'        => now()->toDateString(),
                'remarks'           => $request->remarks,
                'branch_id'         => $branchId,
                'created_by'        => Auth::id(),
                'debit_party_type'  => $request->debit_party_type,
                'debit_party_id'    => $request->debit_party_id,
                'credit_party_type' => $request->credit_party_type,
                'credit_party_id'   => $request->credit_party_id,
                'amount'            => $amount,
                'narration_id'      => json_encode($narrationIds),
                'reference_no'      => json_encode($request->reference_no ?? []),
                'status'            => 'posted',
            ]);

            // ─────────────────────────────────────────────────────────────
            // DEBIT SIDE — typically the VENDOR (we are paying/reducing payable)
            // ─────────────────────────────────────────────────────────────
            if ($request->debit_party_type === 'vendor') {
                $lastLedger = VendorLedger::where('vendor_id', $request->debit_party_id)
                    ->where('branch_id', $branchId)
                    ->orderBy('id', 'desc')
                    ->first();

                $vendor          = \App\Models\Vendor::find($request->debit_party_id);
                $previousBalance = $lastLedger
                    ? (float) $lastLedger->closing_balance
                    : (float) ($vendor->opening_balance ?? 0);

                VendorLedger::create([
                    'vendor_id'        => $request->debit_party_id,
                    'branch_id'        => $branchId,
                    'admin_or_user_id' => Auth::id(),
                    'transaction_date' => $request->voucher_date,
                    'description'      => "Journal Voucher #$jvid — Debit",
                    'opening_balance'  => $vendor->opening_balance ?? 0,
                    'previous_balance' => $previousBalance,
                    'debit_amount'     => $amount,   // Debit = Vendor payable reduced
                    'credit_amount'    => 0,
                    'closing_balance'  => $previousBalance - $amount,
                ]);

            } elseif ($request->debit_party_type === 'customer') {
                $lastLedger = CustomerLedger::where('customer_id', $request->debit_party_id)
                    ->orderBy('id', 'desc')
                    ->first();

                $customer        = \App\Models\Customer::find($request->debit_party_id);
                $previousBalance = $lastLedger
                    ? (float) $lastLedger->closing_balance
                    : (float) ($customer->opening_balance ?? 0);

                CustomerLedger::create([
                    'customer_id'      => $request->debit_party_id,
                    'admin_or_user_id' => Auth::id(),
                    'previous_balance' => $previousBalance,
                    'opening_balance'  => $customer->opening_balance ?? 0,
                    'total_debit'      => $amount,
                    'total_credit'     => 0,
                    'closing_balance'  => $previousBalance + $amount, // Debit increases what customer owes
                ]);
            }

            // ─────────────────────────────────────────────────────────────
            // CREDIT SIDE — typically the CUSTOMER (reducing their receivable)
            // ─────────────────────────────────────────────────────────────
            if ($request->credit_party_type === 'customer') {
                $lastLedger = CustomerLedger::where('customer_id', $request->credit_party_id)
                    ->orderBy('id', 'desc')
                    ->first();

                $customer        = \App\Models\Customer::find($request->credit_party_id);
                $previousBalance = $lastLedger
                    ? (float) $lastLedger->closing_balance
                    : (float) ($customer->opening_balance ?? 0);

                CustomerLedger::create([
                    'customer_id'      => $request->credit_party_id,
                    'admin_or_user_id' => Auth::id(),
                    'previous_balance' => $previousBalance,
                    'opening_balance'  => $customer->opening_balance ?? 0,
                    'total_debit'      => 0,
                    'total_credit'     => $amount,
                    'closing_balance'  => $previousBalance - $amount, // Credit reduces receivable
                ]);

            } elseif ($request->credit_party_type === 'vendor') {
                $lastLedger = VendorLedger::where('vendor_id', $request->credit_party_id)
                    ->where('branch_id', $branchId)
                    ->orderBy('id', 'desc')
                    ->first();

                $vendor          = \App\Models\Vendor::find($request->credit_party_id);
                $previousBalance = $lastLedger
                    ? (float) $lastLedger->closing_balance
                    : (float) ($vendor->opening_balance ?? 0);

                VendorLedger::create([
                    'vendor_id'        => $request->credit_party_id,
                    'branch_id'        => $branchId,
                    'admin_or_user_id' => Auth::id(),
                    'transaction_date' => $request->voucher_date,
                    'description'      => "Journal Voucher #$jvid — Credit",
                    'opening_balance'  => $vendor->opening_balance ?? 0,
                    'previous_balance' => $previousBalance,
                    'debit_amount'     => 0,
                    'credit_amount'    => $amount,
                    'closing_balance'  => $previousBalance + $amount,
                ]);
            }

            DB::commit();
            return redirect()->route('journal.vouchers.index')
                ->with('success', "Journal Voucher #$jvid saved successfully! Both ledgers updated.");

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("JV Store Error: " . $e->getMessage());
            return back()->with('error', "Error saving Journal Voucher: " . $e->getMessage());
        }
    }

    /**
     * List all Journal Vouchers.
     */
    public function journal_vouchers_index()
    {
        $isSuperAdmin = Auth::user()->hasRole('super admin');
        $branchId     = Auth::user()->branch_id;

        $query = JournalVoucher::latest();

        if (!$isSuperAdmin && $branchId) {
            $query->where('branch_id', $branchId);
        }

        $vouchers = $query->get()->map(function ($jv) {
            $jv->debit_party_name  = JournalVoucher::resolvePartyName($jv->debit_party_type,  $jv->debit_party_id);
            $jv->credit_party_name = JournalVoucher::resolvePartyName($jv->credit_party_type, $jv->credit_party_id);
            return $jv;
        });

        return view('admin_panel.vochers.journal_vouchers.index', compact('vouchers'));
    }

    /**
     * Print a single Journal Voucher.
     */
    public function journal_voucher_print(int $id)
    {
        $jv = JournalVoucher::findOrFail($id);

        $debitPartyName  = JournalVoucher::resolvePartyName($jv->debit_party_type,  $jv->debit_party_id);
        $creditPartyName = JournalVoucher::resolvePartyName($jv->credit_party_type, $jv->credit_party_id);

        // Fetch phone / code for parties
        $debitPartyPhone = match ($jv->debit_party_type) {
            'vendor'   => \App\Models\Vendor::find($jv->debit_party_id)?->phone ?? '—',
            'customer' => \App\Models\Customer::find($jv->debit_party_id)?->mobile ?? '—',
            'account'  => \App\Models\Account::find($jv->debit_party_id)?->account_code ?? '—',
            default    => '—',
        };

        $creditPartyPhone = match ($jv->credit_party_type) {
            'vendor'   => \App\Models\Vendor::find($jv->credit_party_id)?->phone ?? '—',
            'customer' => \App\Models\Customer::find($jv->credit_party_id)?->mobile ?? '—',
            'account'  => \App\Models\Account::find($jv->credit_party_id)?->account_code ?? '—',
            default    => '—',
        };

        $branch = null;
        $branchIdForPrint = $jv->branch_id ?? (Auth::check() ? Auth::user()->branch_id : null);
        if ($branchIdForPrint) {
            $branch = DB::table('branches')->where('id', $branchIdForPrint)->first();
        }

        return view('admin_panel.vochers.journal_vouchers.print', compact(
            'jv', 'debitPartyName', 'creditPartyName', 'debitPartyPhone', 'creditPartyPhone', 'branch'
        ));
    }

    /**
     * Delete (soft-delete) a Journal Voucher with full ledger reversal.
     */
    public function journal_voucher_destroy(int $id)
    {
        $jv = JournalVoucher::findOrFail($id);

        if ($jv->status === 'voided') {
            return back()->with('error', 'Journal Voucher is already voided!');
        }

        $success = \App\Services\VoucherService::reverseJournalVoucher($jv, auth()->id(), true);

        if ($success) {
            $jv->delete();
            return back()->with('success', "Journal Voucher #{$jv->jvid} voided and ledgers reversed successfully.");
        } else {
            $err = \App\Services\VoucherService::$lastError;
            return back()->with('error', "Failed to void Journal Voucher." . ($err ? " Error: {$err}" : ''));
        }
    }

    /**
     * Show Journal Voucher edit form.
     */
    public function journal_voucher_edit(int $id)
    {
        $jv = JournalVoucher::findOrFail($id);
        $narrations    = \App\Models\Narration::where('expense_head', 'Journal Voucher')->pluck('narration', 'id');
        $AccountHeads  = AccountHead::get();
        $isSuperAdmin  = Auth::user()->hasRole('super admin');
        $currentBranch = $jv->branch_id ?? Auth::user()->branch_id;
        $Branch        = \App\Models\Branch::all();

        $narrationsArray = \App\Services\VoucherService::safeDecodeArray($jv->narration_id);
        $selectedNarration = $narrationsArray[0] ?? null;

        $debitPartyName  = JournalVoucher::resolvePartyName($jv->debit_party_type,  $jv->debit_party_id);
        $creditPartyName = JournalVoucher::resolvePartyName($jv->credit_party_type, $jv->credit_party_id);

        return view('admin_panel.vochers.journal_vouchers.edit', compact(
            'jv', 'narrations', 'AccountHeads', 'isSuperAdmin', 'currentBranch', 'Branch',
            'selectedNarration', 'debitPartyName', 'creditPartyName'
        ));
    }

    /**
     * Update Journal Voucher with ledger reversal and re-application.
     */
    public function journal_voucher_update(Request $request, int $id)
    {
        $jv = JournalVoucher::findOrFail($id);

        $request->validate([
            'voucher_date'       => 'required|date',
            'debit_party_type'   => 'required|string',
            'debit_party_id'     => 'required',
            'credit_party_type'  => 'required|string',
            'credit_party_id'    => 'required',
            'amount'             => 'required|numeric|min:0.01',
            'remarks'            => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            // 1. Reverse previous effects if not voided
            if ($jv->status !== 'voided') {
                \App\Services\VoucherService::reverseJournalVoucher($jv, auth()->id(), false);
            }

            // 2. Process Narrations
            $narrationIds = [];
            if ($request->has('narration_id')) {
                foreach ($request->narration_id as $idx => $narrId) {
                    $manualText = $request->narration_text[$idx] ?? null;
                    if (empty($narrId) && !empty($manualText)) {
                        $new = \App\Models\Narration::create([
                            'expense_head' => 'Journal Voucher',
                            'narration'    => $manualText,
                        ]);
                        $narrationIds[] = (string) $new->id;
                    } else {
                        $narrationIds[] = (string) $narrId;
                    }
                }
            }

            // 3. Branch
            $branchId = (Auth::user()->hasRole('super admin') && $request->filled('branch_id'))
                ? (int)$request->branch_id
                : (int)($jv->branch_id ?? Auth::user()->branch_id ?? 0);

            $jv->update([
                'voucher_date'      => $request->voucher_date,
                'remarks'           => $request->remarks,
                'branch_id'         => $branchId,
                'debit_party_type'  => $request->debit_party_type,
                'debit_party_id'    => $request->debit_party_id,
                'credit_party_type' => $request->credit_party_type,
                'credit_party_id'   => $request->credit_party_id,
                'amount'            => (float)$request->amount,
                'narration_id'      => json_encode($narrationIds),
                'reference_no'      => json_encode($request->reference_no ?? []),
                'status'            => 'posted',
            ]);

            // 4. Apply new ledger effects
            \App\Services\VoucherService::applyJournalVoucher($jv, $request->all(), auth()->id());

            DB::commit();
            return redirect()->route('journal.vouchers.index')
                ->with('success', "Journal Voucher #{$jv->jvid} updated successfully! Ledgers synchronized.");
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("JV Update Error: " . $e->getMessage());
            return back()->with('error', "Error updating Journal Voucher: " . $e->getMessage());
        }
    }

    /**
     * AJAX — Get party list for Journal Voucher (customers + vendors + accounts)
     */
    public function getJournalPartyList(\Illuminate\Http\Request $request)
    {
        $type     = strtolower($request->query('type', ''));
        $isSuper  = Auth::check() && Auth::user()->hasRole('super admin');
        $branchId = ($isSuper && $request->filled('branch_id'))
            ? $request->branch_id
            : (Auth::check() ? Auth::user()->branch_id : null);

        if ($type === 'customer') {
            $query = \App\Models\Customer::with(['ledgers' => fn($q) => $q->latest()]);

            if ($branchId && $branchId !== 'all') {
                $query->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            }

            return response()->json($query->get()->map(function ($c) {
                $ledger  = $c->ledgers->first();
                $closing = $ledger ? (float) $ledger->closing_balance : (float) ($c->opening_balance ?? 0);
                return ['id' => $c->id, 'text' => $c->customer_name, 'mobile' => $c->mobile ?? '', 'closing_balance' => $closing];
            }));

        } elseif ($type === 'vendor') {
            $query = \App\Models\Vendor::query();
            if (!$isSuper && $branchId) $query->where('branch_id', $branchId);
            if ($isSuper && $request->filled('branch_id')) $query->where('branch_id', $branchId);

            return response()->json($query->get()->map(function ($v) use ($branchId) {
                $lastLedger = VendorLedger::where('vendor_id', $v->id)
                    ->where('branch_id', $branchId ?? $v->branch_id)
                    ->orderByDesc('id')->first();
                $closing = $lastLedger ? (float) $lastLedger->closing_balance : (float) ($v->opening_balance ?? 0);
                return ['id' => $v->id, 'text' => $v->name, 'mobile' => $v->phone ?? $v->contact ?? '', 'closing_balance' => $closing];
            }));
        }

        return response()->json([]);
    }
}

