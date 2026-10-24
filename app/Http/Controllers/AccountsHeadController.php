<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountHead;
use App\Models\AccountLedgerEntry;
use App\Models\AccountEditHistory;
use App\Models\Branch;
use App\Models\PurchaseAccountAllocaations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountsHeadController extends Controller
{
    /**
     * Display Chart of Accounts with branch-aware filtering
     * 
     * ✅ For branch users: Shows only their branch's accounts
     * ✅ For super admin: Shows all branches overview
     */
    public function index()
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super admin');

        // ✅ For Super Admin: Show all branches overview
        if ($isSuperAdmin) {
            return $this->showBranchesOverview();
        }

        // ✅ For Branch User: Show their branch accounts
        return $this->showBranchAccounts($user->branch_id);
    }

    /**
     * ✅ Display all branches with their account balances (Super Admin Only)
     */
    private function showBranchesOverview()
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super admin');

        // Get all branches with their account information
        $branches = Branch::where(function ($q) {
            $q->whereNull('status')
              ->orWhere('status', '!=', 'inactive');
        })->with('accounts.head')->orderBy('name')->get();

        // Preload ledger totals for all branches in one bulk query
        $allAccountIds = $branches->flatMap->accounts->pluck('id')->toArray();
        $ledgerTotals = DB::table('account_ledger_entries')
            ->whereIn('account_id', $allAccountIds)
            ->where('voucher_type', '!=', 'opening_balance')
            ->groupBy('account_id')
            ->selectRaw('account_id, COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(credit), 0) as total_credit')
            ->get()
            ->keyBy('account_id');

        // Calculate totals for each branch
        $branchesWithTotals = $branches->map(function ($branch) use ($ledgerTotals) {
            $accounts = $branch->accounts;
            $openingSum = (float)$accounts->sum(function ($acc) {
                return (float)($acc->opening_balance ?? 0);
            });
            $currentSum = (float)$accounts->sum(function ($acc) use ($ledgerTotals) {
                $ob = (float)($acc->opening_balance ?? 0);
                $isCredit = trim(strtolower((string)($acc->type ?? 'debit'))) === 'credit';
                $totals = $ledgerTotals->get($acc->id);
                $totDebit = (float)($totals->total_debit ?? 0);
                $totCredit = (float)($totals->total_credit ?? 0);
                return $isCredit ? ($ob + $totCredit - $totDebit) : ($ob + $totDebit - $totCredit);
            });

            return [
                'id'              => $branch->id,
                'name'            => $branch->name,
                'address'         => $branch->address,
                'number'          => $branch->number,
                'status'          => $branch->status ?? 'active',
                'accounts_count'  => $accounts->count(),
                'opening_balance' => $openingSum,
                'total_balance'   => $currentSum,
                'current_balance' => $currentSum,
            ];
        });

        $totalOrgBranches = $branchesWithTotals->count();
        $totalOrgAccounts = $branchesWithTotals->sum('accounts_count');
        $totalOrgOpening  = $branchesWithTotals->sum('opening_balance');
        $totalOrgBalance  = $branchesWithTotals->sum('current_balance');

        $heads = AccountHead::orderBy('name')->get();

        return view('admin_panel.chart_of_accounts.branches_overview', compact(
            'branchesWithTotals',
            'branches',
            'heads',
            'totalOrgBranches',
            'totalOrgAccounts',
            'totalOrgOpening',
            'totalOrgBalance',
            'isSuperAdmin',
            'user'
        ));
    }

    /**
     * ✅ Display accounts for a specific branch
     */
    public function showBranchAccounts($branchId)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super admin');

        // ✅ Authorization: Branch users can only view their own branch
        if (!$isSuperAdmin && $user->branch_id != $branchId) {
            return redirect()->back()
                ->with('error', 'Unauthorized. You can only view your own branch accounts.');
        }

        // Get branch details
        $branch = Branch::with(['accounts.head'])
            ->findOrFail($branchId);

        // ✅ Preload real-time ledger totals in a single optimized query
        $accountIds = $branch->accounts->pluck('id')->toArray();
        $ledgerTotals = DB::table('account_ledger_entries')
            ->whereIn('account_id', $accountIds)
            ->where('voucher_type', '!=', 'opening_balance')
            ->groupBy('account_id')
            ->selectRaw('account_id, COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(credit), 0) as total_credit')
            ->get()
            ->keyBy('account_id');

        // Real-time exact ledger balance for all branch accounts (Opening + Debit - Credit)
        $allAccounts = $branch->accounts()->with('head')->get()->map(function ($acc) use ($ledgerTotals) {
            $acc->opening_balance = (float)($acc->opening_balance ?? 0);
            $isCredit = trim(strtolower((string)($acc->type ?? 'debit'))) === 'credit';
            
            $totals = $ledgerTotals->get($acc->id);
            $totDebit = (float)($totals->total_debit ?? 0);
            $totCredit = (float)($totals->total_credit ?? 0);

            $acc->current_balance = $isCredit
                ? ($acc->opening_balance + $totCredit - $totDebit)
                : ($acc->opening_balance + $totDebit - $totCredit);

            return $acc;
        });

        // ✅ Get ALL account heads
        $heads = AccountHead::orderBy('name')->get();
        
        // Build accountsByHead structure
        $accountsByHead = collect();
        foreach ($heads as $head) {
            $accounts = $allAccounts->where('head_id', $head->id)->values();
            if ($accounts->count() > 0) {
                $accountsByHead[$head->name] = $accounts;
            }
        }

        // Calculate totals
        $totalOpeningBalance = (float)$allAccounts->sum('opening_balance');
        $totalCurrentBalance = (float)$allAccounts->sum('current_balance');
        $totalBalance = $totalCurrentBalance;

        $branches = Branch::where(function ($q) {
            $q->whereNull('status')
              ->orWhere('status', '!=', 'inactive');
        })->orderBy('name')->get();

        return view('admin_panel.chart_of_accounts.branch_details', compact(
            'branch',
            'allAccounts',
            'accountsByHead',
            'heads',
            'totalOpeningBalance',
            'totalCurrentBalance',
            'totalBalance',
            'branches',
            'isSuperAdmin',
            'user'
        ));
    }

    /**
     * Store new Account Head (shared across branches - ERP Standard)
     * ✅ Users with chart.of.accounts.create permission can create heads
     */
    public function storeHead(Request $request)
    {
        // ✅ Authorization: Check permission (route middleware already validates)
        if (!Auth::user()->hasPermissionTo('chart.of.accounts.create')) {
            return redirect()->back()
                ->with('error', 'Unauthorized. You do not have permission to create account heads.');
        }

        $request->validate(['name' => 'required|string|max:100']);
        
        AccountHead::create(['name' => $request->name]);
        
        return redirect()->back()->with('success', 'Account head added successfully.');
    }

    /**
     * Store new Account (branch-specific - ERP Standard)
     * 
     * ✅ Branch users: Auto-binds account to their branch
     * ✅ Super admin: Can create account for any selected branch
     * ✅ Account codes are auto-generated sequentially per branch per head
     */
    public function storeAccount(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super admin');

        // ✅ Validation rules (account_code is auto-generated, so not required from user)
        $rules = [
            'head_id'         => 'required|exists:account_heads,id',
            'title'           => 'required|string|max:150',
            'type'            => 'required|in:Debit,Credit',
            'opening_balance' => 'nullable|numeric',
            'status'          => 'nullable|in:on',
        ];

        // ✅ Branch selection: Required for super admin, not for branch users
        if ($isSuperAdmin) {
            $rules['branch_id'] = 'required|exists:branches,id';
        }

        $request->validate($rules);

        // ✅ Determine branch_id
        $branchId = $isSuperAdmin ? $request->branch_id : $user->branch_id;

        // ✅ Get the head and branch
        $head = AccountHead::findOrFail($request->head_id);
        $branch = Branch::findOrFail($branchId);

        // ✅ Use AccountCodeService to auto-generate sequential code
        $service = new \App\Services\AccountCodeService();
        $accountCode = $service->generateAccountCode($branch, $head);

        // ✅ Set status (1 = active, 0 = inactive)
        $status = $request->status === 'on' ? 1 : 0;

        $openingBal = abs((float)($request->opening_balance ?? 0));
        $openingDate = $request->opening_date ?: now()->toDateString();

        // ✅ Create account with auto-generated code
        $account = Account::create([
            'branch_id'       => $branchId,
            'head_id'         => $request->head_id,
            'account_code'    => $accountCode,
            'title'           => $request->title,
            'type'            => $request->type,
            'opening_balance' => $openingBal,
            'opening_date'    => $openingDate,
            'status'          => $status,
        ]);

        if ($openingBal != 0) {
            $obEntryNo = AccountLedgerEntry::generateEntryNo($account->id, 'opening_balance');
            AccountLedgerEntry::create([
                'account_id'       => $account->id,
                'branch_id'        => $branchId,
                'voucher_type'     => 'opening_balance',
                'voucher_no'       => null,
                'voucher_id'       => null,
                'entry_no'         => $obEntryNo,
                'transaction_date' => $openingDate,
                'description'      => 'Opening Balance',
                'debit'            => $openingBal >= 0 ? $openingBal : 0,
                'credit'           => $openingBal < 0 ? abs($openingBal) : 0,
                'running_balance'  => $openingBal,
                'created_by'       => $user->id,
            ]);
        }

        return redirect()->back()
            ->with('success', "Account '{$accountCode}' created successfully for {$branch->name}.");
    }

    /**
     * Update existing Account
     * ✅ If head is changed, account code is automatically regenerated
     */
    public function updateAccount(Request $request, $id)
    {
        $account = Account::findOrFail($id);
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super admin');

        // ✅ Authorization
        if (!$isSuperAdmin && $account->branch_id != $user->branch_id) {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $rules = [
            'head_id'         => 'required|exists:account_heads,id',
            'title'           => 'required|string|max:150',
            'type'            => 'required|in:Debit,Credit',
            'opening_balance' => 'nullable|numeric',
            'opening_date'    => 'nullable|date',
            'status'          => 'nullable',
        ];

        $request->validate($rules);

        // Store old values for audit log
        $oldTitle       = $account->title;
        $oldOpening     = (float)($account->opening_balance ?? 0);
        $oldOpeningDate = $account->opening_date ? $account->opening_date->format('Y-m-d') : null;
        $oldHeadId      = $account->head_id;
        $oldType        = $account->type;
        $oldStatus      = (int)($account->status == 1 || $account->status == 'active');

        $newTitle       = $request->title;
        $newOpening     = abs((float)($request->opening_balance ?? 0));
        $newOpeningDate = $request->opening_date ?: ($oldOpeningDate ?: now()->toDateString());
        $newHeadId      = (int)$request->head_id;
        $newType        = $request->type;
        $newStatus      = ($request->status === 'on' || $request->status == '1' || $request->status === true || $request->status === 'active') ? 1 : 0;

        $data = [
            'title'           => $newTitle,
            'type'            => $newType,
            'opening_balance' => $newOpening,
            'opening_date'    => $newOpeningDate,
            'status'          => $newStatus,
        ];

        // ✅ If head changed, generate new sequential code
        if ($account->head_id != $request->head_id) {
            $head = AccountHead::findOrFail($request->head_id);
            $branch = Branch::findOrFail($account->branch_id);
            
            $service = new \App\Services\AccountCodeService();
            $data['head_id'] = $request->head_id;
            $data['account_code'] = $service->generateAccountCode($branch, $head);
        }

        // Calculate differences and build change notes
        $diff = $newOpening - $oldOpening;
        $currentBal = AccountLedgerEntry::getClosingBalance($account->id);
        $resultingCurrentBal = $currentBal + $diff;

        $changes = [];
        if (abs($diff) > 0.0001) {
            $changes[] = "Opening Balance: PKR " . number_format($oldOpening, 2) . " → PKR " . number_format($newOpening, 2);
        }
        if ($oldOpeningDate !== $newOpeningDate) {
            $changes[] = "Opening Date: " . ($oldOpeningDate ?: 'None') . " → {$newOpeningDate}";
        }
        if ($oldTitle !== $newTitle) {
            $changes[] = "Title: '{$oldTitle}' → '{$newTitle}'";
        }
        if ($oldType !== $newType) {
            $changes[] = "Nature: {$oldType} → {$newType}";
        }
        if ($oldHeadId !== $newHeadId) {
            $changes[] = "Head updated";
        }
        if ($oldStatus !== $newStatus) {
            $statusText = $newStatus === 1 ? 'Active' : 'Inactive';
            $changes[] = "Status: {$statusText}";
        }

        if (!empty($changes)) {
            AccountEditHistory::create([
                'account_id'                => $account->id,
                'branch_id'                 => $account->branch_id,
                'user_id'                   => Auth::id(),
                'old_opening_balance'       => $oldOpening,
                'new_opening_balance'       => $newOpening,
                'resulting_current_balance' => $resultingCurrentBal,
                'old_title'                 => $oldTitle,
                'new_title'                 => $newTitle,
                'changes_summary'           => implode('; ', $changes),
            ]);
        }

        $obEntry = AccountLedgerEntry::where('account_id', $account->id)
            ->where('voucher_type', 'opening_balance')
            ->first();

        if ($obEntry) {
            $obEntry->update([
                'transaction_date' => $newOpeningDate,
                'debit'            => $newOpening >= 0 ? $newOpening : 0,
                'credit'           => $newOpening < 0 ? abs($newOpening) : 0,
                'running_balance'  => $newOpening,
            ]);
        } elseif ($newOpening != 0) {
            $obEntryNo = AccountLedgerEntry::generateEntryNo($account->id, 'opening_balance');
            AccountLedgerEntry::create([
                'account_id'       => $account->id,
                'branch_id'        => $account->branch_id,
                'voucher_type'     => 'opening_balance',
                'voucher_no'       => null,
                'voucher_id'       => null,
                'entry_no'         => $obEntryNo,
                'transaction_date' => $newOpeningDate,
                'description'      => 'Opening Balance',
                'debit'            => $newOpening >= 0 ? $newOpening : 0,
                'credit'           => $newOpening < 0 ? abs($newOpening) : 0,
                'running_balance'  => $newOpening,
                'created_by'       => Auth::id() ?? 1,
            ]);
        }

        $account->update($data);
        AccountLedgerEntry::recalculateRunningBalance($account->id);

        return redirect()->back()->with('success', 'Account updated successfully.');
    }

    public function getAccountHistory($id)
    {
        $account = Account::with(['head', 'branch'])->findOrFail($id);
        $currentBal = AccountLedgerEntry::getClosingBalance($id);

        $histories = AccountEditHistory::with('user:id,name')
            ->where('account_id', $id)
            ->latest()
            ->get()
            ->map(function ($h) {
                return [
                    'id'                        => $h->id,
                    'user_name'                 => $h->user ? $h->user->name : 'System User',
                    'date'                      => $h->created_at ? $h->created_at->format('d M Y, h:i A') : 'N/A',
                    'old_opening_balance'       => (float) $h->old_opening_balance,
                    'new_opening_balance'       => (float) $h->new_opening_balance,
                    'resulting_current_balance' => (float) $h->resulting_current_balance,
                    'diff'                      => (float) $h->new_opening_balance - (float) $h->old_opening_balance,
                    'old_title'                 => $h->old_title,
                    'new_title'                 => $h->new_title,
                    'changes_summary'           => $h->changes_summary ?? 'Account details updated',
                ];
            });

        return response()->json([
            'success'               => true,
            'account_title'         => $account->title,
            'account_code'          => $account->account_code,
            'branch_name'           => $account->branch->name ?? '',
            'current_balance'       => $currentBal,
            'histories'             => $histories
        ]);
    }

    /**
     * Delete Account
     * ✅ ERP Standard: Check if account has transactions before deleting (optional/future)
     */
    public function destroyAccount($id)
    {
        $account = Account::findOrFail($id);
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super admin');

        // ✅ Authorization
        if (!$isSuperAdmin && $account->branch_id != $user->branch_id) {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        // TODO: Check for existing transactions before deletion
        
        $account->delete();

        return redirect()->back()->with('success', 'Account deleted successfully.');
    }

    /**
     * ✅ ERP STANDARD: Per-Account Ledger
     * Shows complete transaction history for a single account.
     * Accessible from the branch accounts list.
     */
    public function accountLedger($accountId, Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super admin');

        $account = \App\Models\Account::with(['head', 'branch'])->findOrFail($accountId);

        // ✅ Authorization: Branch users can only view their own branch accounts
        if (!$isSuperAdmin && $account->branch_id != $user->branch_id) {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        // ✅ Purge any stale ledger entries from voided vouchers for this account
        $voidedEvids = \App\Models\ExpenseVoucher::where('status', 'voided')->pluck('evid')->filter()->toArray();
        $voidedPvids = \App\Models\PaymentVoucher::where('status', 'voided')->pluck('pvid')->filter()->toArray();
        $voidedRvids = \App\Models\ReceiptsVoucher::where('status', 'voided')->pluck('rvid')->filter()->toArray();
        $allVoidedNos = array_unique(array_filter(array_merge($voidedEvids, $voidedPvids, $voidedRvids)));
        if (!empty($allVoidedNos)) {
            \App\Models\AccountLedgerEntry::where('account_id', $accountId)
                ->whereIn('voucher_no', $allVoidedNos)
                ->delete();
        }

        // ✅ Automatically sync any unrecorded receipts or vouchers for this account
        self::syncAccountLedger($account);
        $account->refresh();

        // ✅ Ensure balances and sequence numbers are cleanly maintained in date order
        \App\Models\AccountLedgerEntry::recalculateRunningBalance($accountId);

        // Date filters
        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');

        // Fetch ledger entries ordered chronologically by transaction_date
        $query = \App\Models\AccountLedgerEntry::where('account_id', $accountId)
            ->orderByRaw("CASE WHEN voucher_type = 'opening_balance' THEN 0 ELSE 1 END ASC")
            ->orderBy('transaction_date', 'asc')
            ->orderByRaw("CASE WHEN debit > 0 THEN 0 ELSE 1 END ASC")
            ->orderByRaw("CASE WHEN voucher_no IS NOT NULL AND voucher_no != '' THEN 0 ELSE 1 END ASC")
            ->orderBy('voucher_no', 'asc')
            ->orderBy('id', 'asc');

        if ($dateFrom) {
            $query->where('transaction_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('transaction_date', '<=', $dateTo);
        }

        $entries = $query->get();

        // Get initial OB entry if exists
        $obEntry = \App\Models\AccountLedgerEntry::where('account_id', $accountId)
            ->where('voucher_type', 'opening_balance')
            ->first();
        $initialOpening = (float)($account->opening_balance ?? 0);

        if ($dateFrom) {
            $prevEntry = \App\Models\AccountLedgerEntry::where('account_id', $accountId)
                ->where('transaction_date', '<', $dateFrom)
                ->where('voucher_type', '!=', 'opening_balance')
                ->orderBy('transaction_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            $openingBalance = $prevEntry ? (float)$prevEntry->running_balance : $initialOpening;
        } else {
            $openingBalance = $initialOpening;
        }

        // Exclude OB entry from Total In to avoid double counting
        $nonObEntries = $entries->where('voucher_type', '!=', 'opening_balance');
        $totalDebit   = $nonObEntries->sum('debit');
        $totalCredit  = $nonObEntries->sum('credit');

        // Closing balance = last entry running balance (or opening balance if no entries)
        $closingBalance = $entries->last()?->running_balance ?? $openingBalance;

        return view('admin_panel.chart_of_accounts.account_ledger', compact(
            'account',
            'entries',
            'obEntry',
            'nonObEntries',
            'totalDebit',
            'totalCredit',
            'openingBalance',
            'closingBalance',
            'dateFrom',
            'dateTo',
            'isSuperAdmin'
        ));
    }

    /**
     * ✅ Auto-sync any unrecorded receipts or vouchers into account_ledger_entries.
     */
    public static function syncAccountLedger($account)
    {
        $accountId = $account->id;
        $accountType = trim(strtolower($account->type ?? 'debit'));
        $isReceivable = (int)($account->head_id ?? 0) === 8 || str_contains(strtolower($account->head?->name ?? ''), 'receivable');

        // ✅ Auto-heal: Ensure Debit/Cash accounts never have negative opening balance
        if ($accountType === 'debit' && (float)$account->opening_balance < 0) {
            $account->opening_balance = 0.00;
            $account->save();

            $obEntry = \App\Models\AccountLedgerEntry::where('account_id', $accountId)
                ->where('voucher_type', 'opening_balance')
                ->first();
            if ($obEntry) {
                $obEntry->debit = 0.00;
                $obEntry->credit = 0.00;
                $obEntry->running_balance = 0.00;
                $obEntry->save();
            }
        }

        // ✅ 0. Clean up any improperly synced party entries that belong to vendors or customers
        $erroneousEntries = \App\Models\AccountLedgerEntry::where('account_id', $accountId)
            ->whereIn('voucher_type', ['payment', 'receipt'])
            ->where('description', 'like', '%Party Side%')
            ->get();

        $deletedCount = 0;
        foreach ($erroneousEntries as $entry) {
            if ($entry->voucher_type === 'payment') {
                $pv = \App\Models\PaymentVoucher::where('pvid', $entry->voucher_no)
                    ->orWhere('id', $entry->voucher_id)
                    ->first();
                if ($pv) {
                    $types = \App\Services\VoucherService::safeDecodeArray($pv->type);
                    if (empty($types) && !empty($pv->type)) {
                        $types = [$pv->type];
                    }
                    $isAccountType = false;
                    foreach ($types as $t) {
                        $tStr = strtolower(trim((string)$t));
                        if (is_numeric($t) || in_array($tStr, ['account', 'ledger'])) {
                            $isAccountType = true;
                            break;
                        }
                    }
                    if (!$isAccountType) {
                        $entry->delete();
                        $deletedCount++;
                    }
                }
            } elseif ($entry->voucher_type === 'receipt') {
                $rv = \App\Models\ReceiptsVoucher::where('rvid', $entry->voucher_no)
                    ->orWhere('id', $entry->voucher_id)
                    ->first();
                if ($rv) {
                    $types = \App\Services\VoucherService::safeDecodeArray($rv->type);
                    if (empty($types) && !empty($rv->type)) {
                        $types = [$rv->type];
                    }
                    $isAccountType = false;
                    foreach ($types as $t) {
                        $tStr = strtolower(trim((string)$t));
                        if (is_numeric($t) || in_array($tStr, ['account', 'ledger'])) {
                            $isAccountType = true;
                            break;
                        }
                    }
                    if (!$isAccountType) {
                        $entry->delete();
                        $deletedCount++;
                    }
                }
            }
        }
        if ($deletedCount > 0) {
            \App\Models\AccountLedgerEntry::recalculateRunningBalance($accountId);
        }

        // Helper to extract account-specific amount from voucher fields (Bank / Cash side)
        $getAccountAmount = function($rowAccountsRaw, $amountsRaw, $totalAmountRaw, $targetAccId) {
            $accIds = is_array($rowAccountsRaw) ? $rowAccountsRaw : json_decode($rowAccountsRaw, true);
            $amts = is_array($amountsRaw) ? $amountsRaw : json_decode($amountsRaw, true);

            if (is_array($accIds)) {
                $sum = 0;
                foreach ($accIds as $idx => $id) {
                    if ((int)$id === (int)$targetAccId) {
                        $sum += is_array($amts) ? (float)($amts[$idx] ?? 0) : (float)$totalAmountRaw;
                    }
                }
                return $sum;
            } else {
                if ((int)$rowAccountsRaw === (int)$targetAccId) {
                    return (float)($totalAmountRaw ?: $amountsRaw);
                }
            }
            return 0;
        };

        // Helper to extract party amount for an Account (strictly verifies party is an Account, NOT a Vendor or Customer)
        $getPartyAccountAmount = function($voucher, $targetAccId) {
            $vObj = is_array($voucher) ? (object)$voucher : $voucher;
            $partyTypes   = \App\Services\VoucherService::safeDecodeArray($vObj->type ?? null);
            $partyIds     = \App\Services\VoucherService::safeDecodeArray($vObj->party_id ?? null);
            $partyAmounts = \App\Services\VoucherService::safeDecodeArray($vObj->party_amount ?? null);

            // Legacy scalar voucher
            if (empty($partyIds) && !empty($vObj->party_id)) {
                if ((string)$vObj->party_id === (string)$targetAccId) {
                    $pType = strtolower(trim((string)($vObj->type ?? '')));
                    if (in_array($pType, ['vendor', 'customer', 'walkin', 'sale_receipt'])) {
                        return 0.0;
                    }
                    if (is_numeric($pType) || empty($pType)) {
                        return (float)($vObj->total_amount ?? $vObj->debit_amount ?? $vObj->credit_amount ?? 0);
                    }
                }
                return 0.0;
            }

            $total = 0.0;
            foreach ($partyIds as $idx => $pId) {
                if ((string)$pId === (string)$targetAccId) {
                    $pType = strtolower(trim((string)($partyTypes[$idx] ?? ($partyTypes[0] ?? ''))));
                    if (in_array($pType, ['vendor', 'customer', 'walkin', 'sale_receipt'])) {
                        continue;
                    }
                    if (is_numeric($pType) || empty($pType)) {
                        $amt = isset($partyAmounts[$idx]) && is_numeric($partyAmounts[$idx])
                            ? (float)$partyAmounts[$idx]
                            : (count($partyIds) === 1 ? (float)($vObj->total_amount ?? 0) : 0.0);
                        $total += $amt;
                    }
                }
            }
            return (float)$total;
        };

        $otherAffectedAccounts = [];

        // ✅ 0.5. Branch Cash Account Auto-Healing: Fix cross-branch misdirected receipts & auto-sync walk-in/cash sales
        $isBranchCash = ($account->head_id == 1 || str_contains(strtolower($account->title ?? ''), 'cash in hand')) && !empty($account->branch_id);
        if ($isBranchCash) {
            // A. Move any ReceiptsVouchers & ALEs for this branch that were mistakenly saved to another branch account
            $mismatchedRvs = \App\Models\ReceiptsVoucher::where('branch_id', $account->branch_id)
                ->where('row_account_id', '!=', $accountId)
                ->whereNotNull('sale_id')
                ->get();

            foreach ($mismatchedRvs as $mRv) {
                $oldAccId = (int)$mRv->row_account_id;
                $mRv->row_account_id = $accountId;
                $mRv->save();

                $affectedAles = \App\Models\AccountLedgerEntry::where('voucher_id', $mRv->id)
                    ->orWhere(function($q) use ($mRv) {
                        $q->where('voucher_no', $mRv->reference_no)
                          ->where('branch_id', $mRv->branch_id)
                          ->where('voucher_type', 'receipt');
                    })
                    ->get();

                foreach ($affectedAles as $aAle) {
                    if ($aAle->account_id != $accountId) {
                        $otherAffectedAccounts[] = $aAle->account_id;
                        $aAle->account_id = $accountId;
                        $aAle->branch_id = $account->branch_id;
                        $aAle->save();
                    }
                }
                if ($oldAccId && $oldAccId != $accountId) {
                    $otherAffectedAccounts[] = $oldAccId;
                }
            }

            // B. Also find any ALEs that were posted with branch_id = $account->branch_id and description like '%Sale Receipt%' but wrong account_id
            $orphanAles = \App\Models\AccountLedgerEntry::where('branch_id', $account->branch_id)
                ->where('account_id', '!=', $accountId)
                ->where('voucher_type', 'receipt')
                ->where('description', 'like', '%Sale Receipt%')
                ->get();

            foreach ($orphanAles as $oAle) {
                $otherAffectedAccounts[] = $oAle->account_id;
                $oAle->account_id = $accountId;
                $oAle->branch_id = $account->branch_id;
                $oAle->save();
            }

            // C. Auto-sync any walk-in / cash sales for this branch that lack ledger entries
            $branchSales = \App\Models\Sale::where('branch_id', $account->branch_id)
                ->where(function($q) {
                    $q->whereIn('party_type', ['walking', 'cash'])
                      ->orWhere('receipt1', '>', 0);
                })
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            foreach ($branchSales as $bSale) {
                $hasAle = \App\Models\AccountLedgerEntry::where('account_id', $accountId)
                    ->where('voucher_no', $bSale->invoice_no)
                    ->where('voucher_type', 'receipt')
                    ->exists();

                if (!$hasAle) {
                    $netAmt = (float)($bSale->total_net ?? 0);
                    if ($netAmt <= 0) continue;

                    // Find or create ReceiptsVoucher
                    $rv = \App\Models\ReceiptsVoucher::where('sale_id', $bSale->id)->first();
                    if (!$rv) {
                        $rv = \App\Models\ReceiptsVoucher::where('reference_no', $bSale->invoice_no)
                            ->where('branch_id', $account->branch_id)
                            ->first();
                    }

                    if ($rv) {
                        $rv->row_account_id = $accountId;
                        $rv->branch_id = $account->branch_id;
                        $rv->save();
                    } else {
                        $dateStr = $bSale->created_at ? $bSale->created_at->toDateString() : now()->toDateString();
                        $entryDt = $bSale->created_at ?: now();
                        $rv = \App\Models\ReceiptsVoucher::create([
                            'branch_id'        => $account->branch_id,
                            'rvid'             => \App\Models\ReceiptsVoucher::generateRVID(auth()->id() ?? 1),
                            'receipt_date'     => $dateStr,
                            'entry_date'       => $entryDt,
                            'type'             => 'SALE_RECEIPT',
                            'party_id'         => $bSale->customer_id,
                            'sale_id'          => $bSale->id,
                            'reference_no'     => $bSale->invoice_no,
                            'row_account_id'   => $accountId,
                            'row_account_head' => 'Cash/Bank',
                            'amount'           => $netAmt,
                            'total_amount'     => $netAmt,
                            'processed'        => true,
                            'created_at'       => $entryDt,
                            'updated_at'       => $entryDt,
                        ]);
                    }

                    $sDate = $bSale->created_at ? $bSale->created_at->toDateString() : now()->toDateString();
                    \App\Models\AccountLedgerEntry::create([
                        'account_id'        => $accountId,
                        'branch_id'         => $account->branch_id,
                        'voucher_type'      => 'receipt',
                        'voucher_no'        => $bSale->invoice_no,
                        'voucher_id'        => $rv->id,
                        'entry_no'          => \App\Models\AccountLedgerEntry::generateEntryNo($accountId, 'receipt'),
                        'transaction_date'  => $sDate,
                        'description'       => "Sale Receipt — Invoice #" . $bSale->invoice_no . " (Walk-in Customer)",
                        'debit'             => $netAmt,
                        'credit'            => 0,
                        'running_balance'   => 0,
                        'created_by'        => $bSale->salesman_id ?? 1,
                        'created_at'        => $bSale->created_at ?: now(),
                        'updated_at'        => $bSale->created_at ?: now(),
                    ]);
                }
            }
        }

        // 1. Gather all active receipts vouchers that reference this account (as row_account_id or party_id)
        $rvQuery = \App\Models\ReceiptsVoucher::where(function($q) {
            $q->whereNull('status')->orWhere('status', '!=', 'voided');
        })->where(function($q) use ($accountId) {
            $q->where(function($sub) use ($accountId) {
                $sub->where('row_account_id', $accountId)
                    ->orWhere('row_account_id', (string)$accountId)
                    ->orWhere('row_account_id', 'like', '%"' . $accountId . '"%')
                    ->orWhere('row_account_id', 'like', '%[' . $accountId . ',%')
                    ->orWhere('row_account_id', 'like', '%,' . $accountId . ']%')
                    ->orWhere('row_account_id', 'like', '%[' . $accountId . ']%')
                    ->orWhere('row_account_id', 'like', '%,' . $accountId . ',%');
            })->orWhere(function($sub) use ($accountId) {
                $sub->where(function($pSub) use ($accountId) {
                    $pSub->where('party_id', $accountId)
                         ->orWhere('party_id', (string)$accountId)
                         ->orWhere('party_id', 'like', '%"' . $accountId . '"%')
                         ->orWhere('party_id', 'like', '%[' . $accountId . ',%')
                         ->orWhere('party_id', 'like', '%,' . $accountId . ']%')
                         ->orWhere('party_id', 'like', '%[' . $accountId . ']%')
                         ->orWhere('party_id', 'like', '%,' . $accountId . ',%');
                })->where(function($tSub) {
                    $tSub->whereNull('type')
                         ->orWhere(function($typeNotParty) {
                             $typeNotParty->where('type', 'not like', '%vendor%')
                                          ->where('type', 'not like', '%customer%')
                                          ->where('type', 'not like', '%walkin%')
                                          ->where('type', 'not like', '%SALE_RECEIPT%');
                         });
                });
            });
        });
        $rvs = $rvQuery->orderBy('id', 'asc')->get();

        $unrecordedReceipts = [];
        foreach ($rvs as $rv) {
            $rowAmt = $getAccountAmount($rv->row_account_id, $rv->amount, $rv->total_amount, $accountId);
            if ($rowAmt > 0) {
                $alreadyRecordedRow = \App\Models\AccountLedgerEntry::where('account_id', $accountId)
                    ->where(function($q) use ($rv) {
                        $q->where('voucher_id', $rv->id);
                        if (!empty($rv->rvid)) {
                            $q->orWhere('voucher_no', $rv->rvid);
                        }
                        $cleanRefs = \App\Services\VoucherService::safeDecodeArray($rv->reference_no);
                        foreach ($cleanRefs as $ref) {
                            $refStr = trim((string)$ref);
                            if (!empty($refStr) && !in_array($refStr, ['[""]', '""', 'null', '[null]'])) {
                                $q->orWhere('voucher_no', $refStr);
                            }
                        }
                    })
                    ->where('debit', '>', 0)
                    ->exists();

                if (!$alreadyRecordedRow) {
                    $unrecordedReceipts[] = [
                        'rv'     => $rv,
                        'amount' => $rowAmt,
                        'side'   => 'row',
                    ];
                }
            }

            $partyAmt = $getPartyAccountAmount($rv, $accountId);
            if ($partyAmt > 0) {
                $alreadyRecordedParty = \App\Models\AccountLedgerEntry::where('account_id', $accountId)
                    ->where(function($q) use ($rv) {
                        $q->where('voucher_id', $rv->id);
                        if (!empty($rv->rvid)) {
                            $q->orWhere('voucher_no', $rv->rvid);
                        }
                        $cleanRefs = \App\Services\VoucherService::safeDecodeArray($rv->reference_no);
                        foreach ($cleanRefs as $ref) {
                            $refStr = trim((string)$ref);
                            if (!empty($refStr) && !in_array($refStr, ['[""]', '""', 'null', '[null]'])) {
                                $q->orWhere('voucher_no', $refStr);
                            }
                        }
                    })
                    ->where('credit', '>', 0)
                    ->exists();

                if (!$alreadyRecordedParty) {
                    $unrecordedReceipts[] = [
                        'rv'     => $rv,
                        'amount' => $partyAmt,
                        'side'   => 'party',
                    ];
                }
            }
        }

        // 2. Gather all active payment vouchers that reference this account (as row_account_id or party_id)
        $pvQuery = \App\Models\PaymentVoucher::where(function($q) {
            $q->whereNull('status')->orWhere('status', '!=', 'voided');
        })->where(function($q) use ($accountId) {
            $q->where(function($sub) use ($accountId) {
                $sub->where('row_account_id', $accountId)
                    ->orWhere('row_account_id', (string)$accountId)
                    ->orWhere('row_account_id', 'like', '%"' . $accountId . '"%')
                    ->orWhere('row_account_id', 'like', '%[' . $accountId . ',%')
                    ->orWhere('row_account_id', 'like', '%,' . $accountId . ']%')
                    ->orWhere('row_account_id', 'like', '%[' . $accountId . ']%')
                    ->orWhere('row_account_id', 'like', '%,' . $accountId . ',%');
            })->orWhere(function($sub) use ($accountId) {
                $sub->where(function($pSub) use ($accountId) {
                    $pSub->where('party_id', $accountId)
                         ->orWhere('party_id', (string)$accountId)
                         ->orWhere('party_id', 'like', '%"' . $accountId . '"%')
                         ->orWhere('party_id', 'like', '%[' . $accountId . ',%')
                         ->orWhere('party_id', 'like', '%,' . $accountId . ']%')
                         ->orWhere('party_id', 'like', '%[' . $accountId . ']%')
                         ->orWhere('party_id', 'like', '%,' . $accountId . ',%');
                })->where(function($tSub) {
                    $tSub->whereNull('type')
                         ->orWhere(function($typeNotParty) {
                             $typeNotParty->where('type', 'not like', '%vendor%')
                                          ->where('type', 'not like', '%customer%')
                                          ->where('type', 'not like', '%walkin%');
                         });
                });
            });
        });
        $pvs = $pvQuery->orderBy('id', 'asc')->get();

        // ✅ Auto-heal legacy bug: If opening_balance was inflated by party vouchers, reset it to prevent double counting
        $partyPvSum = 0.0;
        foreach ($pvs as $pv) {
            $partyPvSum += $getPartyAccountAmount($pv, $accountId);
        }

        if ($partyPvSum > 0 && abs((float)$account->opening_balance - $partyPvSum) < 0.01) {
            $account->opening_balance = 0.00;
            $account->save();

            $obEntry = \App\Models\AccountLedgerEntry::where('account_id', $accountId)
                ->where('voucher_type', 'opening_balance')
                ->first();
            if ($obEntry) {
                $obEntry->debit = 0.00;
                $obEntry->credit = 0.00;
                $obEntry->running_balance = 0.00;
                $obEntry->save();
            }
        }

        $unrecordedPayments = [];
        foreach ($pvs as $pv) {
            $rowAmt = $getAccountAmount($pv->row_account_id, $pv->amount, $pv->total_amount, $accountId);
            if ($rowAmt > 0) {
                $alreadyRecordedRow = \App\Models\AccountLedgerEntry::where('account_id', $accountId)
                    ->where(function($q) use ($pv) {
                        $q->where('voucher_id', $pv->id);
                        if (!empty($pv->pvid)) {
                            $q->orWhere('voucher_no', $pv->pvid);
                        }
                        $cleanRefs = \App\Services\VoucherService::safeDecodeArray($pv->reference_no);
                        foreach ($cleanRefs as $ref) {
                            $refStr = trim((string)$ref);
                            if (!empty($refStr) && !in_array($refStr, ['[""]', '""', 'null', '[null]'])) {
                                $q->orWhere('voucher_no', $refStr);
                            }
                        }
                    })
                    ->where('credit', '>', 0)
                    ->exists();

                if (!$alreadyRecordedRow) {
                    $unrecordedPayments[] = [
                        'pv'     => $pv,
                        'amount' => $rowAmt,
                        'side'   => 'row',
                    ];
                }
            }

            $partyAmt = $getPartyAccountAmount($pv, $accountId);
            if ($partyAmt > 0) {
                $alreadyRecordedParty = \App\Models\AccountLedgerEntry::where('account_id', $accountId)
                    ->where(function($q) use ($pv) {
                        $q->where('voucher_id', $pv->id);
                        if (!empty($pv->pvid)) {
                            $q->orWhere('voucher_no', $pv->pvid);
                        }
                        $cleanRefs = \App\Services\VoucherService::safeDecodeArray($pv->reference_no);
                        foreach ($cleanRefs as $ref) {
                            $refStr = trim((string)$ref);
                            if (!empty($refStr) && !in_array($refStr, ['[""]', '""', 'null', '[null]'])) {
                                $q->orWhere('voucher_no', $refStr);
                            }
                        }
                    })
                    ->where('debit', '>', 0)
                    ->exists();

                if (!$alreadyRecordedParty) {
                    $unrecordedPayments[] = [
                        'pv'     => $pv,
                        'amount' => $partyAmt,
                        'side'   => 'party',
                    ];
                }
            }
        }

        // 3. Gather all active expense vouchers that reference this account (as row_account_id or party_id)
        $evQuery = \App\Models\ExpenseVoucher::where(function($q) {
            $q->whereNull('status')->orWhere('status', '!=', 'voided');
        })->where(function($q) use ($accountId) {
            $q->where(function($sub) use ($accountId) {
                $sub->where('row_account_id', $accountId)
                    ->orWhere('row_account_id', (string)$accountId)
                    ->orWhere('row_account_id', 'like', '%"' . $accountId . '"%')
                    ->orWhere('row_account_id', 'like', '%[' . $accountId . ',%')
                    ->orWhere('row_account_id', 'like', '%,' . $accountId . ']%')
                    ->orWhere('row_account_id', 'like', '%[' . $accountId . ']%')
                    ->orWhere('row_account_id', 'like', '%,' . $accountId . ',%');
            })->orWhere(function($sub) use ($accountId) {
                $sub->where(function($pSub) use ($accountId) {
                    $pSub->where('party_id', $accountId)
                         ->orWhere('party_id', (string)$accountId)
                         ->orWhere('party_id', 'like', '%"' . $accountId . '"%')
                         ->orWhere('party_id', 'like', '%[' . $accountId . ',%')
                         ->orWhere('party_id', 'like', '%,' . $accountId . ']%')
                         ->orWhere('party_id', 'like', '%[' . $accountId . ']%')
                         ->orWhere('party_id', 'like', '%,' . $accountId . ',%');
                })->where(function($tSub) {
                    $tSub->whereNull('type')
                         ->orWhere(function($typeNotParty) {
                             $typeNotParty->where('type', 'not like', '%vendor%')
                                          ->where('type', 'not like', '%customer%')
                                          ->where('type', 'not like', '%walkin%');
                         });
                });
            });
        });
        $evs = $evQuery->orderBy('id', 'asc')->get();

        $unrecordedExpenses = [];
        foreach ($evs as $ev) {
            $rowAmt = $getAccountAmount($ev->row_account_id, $ev->amount, $ev->total_amount, $accountId);
            if ($rowAmt > 0) {
                // ✅ Fixed: Check for ANY existing entry with this voucher to prevent duplicates
                $alreadyRecordedRow = \App\Models\AccountLedgerEntry::where('account_id', $accountId)
                    ->where(function($q) use ($ev) {
                        $q->where('voucher_id', $ev->id)
                          ->orWhere('voucher_no', $ev->evid);
                    })
                    ->exists();

                if (!$alreadyRecordedRow) {
                    $unrecordedExpenses[] = [
                        'ev'     => $ev,
                        'amount' => $rowAmt,
                        'side'   => 'row',
                    ];
                }
            }

            $partyAmt = $getPartyAccountAmount($ev, $accountId);
            if ($partyAmt > 0) {
                // ✅ Fixed: Check for ANY existing entry with this voucher (not just "Party Side" description)
                $alreadyRecordedParty = \App\Models\AccountLedgerEntry::where('account_id', $accountId)
                    ->where(function($q) use ($ev) {
                        $q->where('voucher_id', $ev->id)
                          ->orWhere('voucher_no', $ev->evid);
                    })
                    ->exists();

                if (!$alreadyRecordedParty) {
                    $unrecordedExpenses[] = [
                        'ev'     => $ev,
                        'amount' => $partyAmt,
                        'side'   => 'party',
                    ];
                }
            }
        }

        // If nothing unrecorded and an OB entry exists, no action needed
        $hasAnyEntry = \App\Models\AccountLedgerEntry::where('account_id', $accountId)->exists();
        if ($hasAnyEntry && empty($unrecordedReceipts) && empty($unrecordedPayments) && empty($unrecordedExpenses)) {
            return;
        }

        // If no entries at all in AccountLedgerEntry, determine base initial opening balance
        if (!$hasAnyEntry) {
            $baseOpening = (float)($account->opening_balance ?? 0);
            $openingDate = $account->opening_date ? $account->opening_date->toDateString() : ($account->created_at ? $account->created_at->toDateString() : now()->toDateString());

            // Create initial OB entry
            $obEntryNo = \App\Models\AccountLedgerEntry::generateEntryNo($accountId, 'opening_balance');
            \App\Models\AccountLedgerEntry::create([
                'account_id'        => $accountId,
                'branch_id'         => $account->branch_id ?? 0,
                'voucher_type'      => 'opening_balance',
                'voucher_no'        => null,
                'voucher_id'        => null,
                'entry_no'          => $obEntryNo,
                'transaction_date'  => $openingDate,
                'description'       => 'Opening Balance',
                'debit'             => $baseOpening >= 0 ? $baseOpening : 0,
                'credit'            => $baseOpening < 0 ? abs($baseOpening) : 0,
                'running_balance'   => $baseOpening,
                'created_by'        => auth()->id() ?? 1,
            ]);
        }

        // Post unrecorded receipts
        foreach ($unrecordedReceipts as $item) {
            $rv = $item['rv'];
            $amt = $item['amount'];
            $side = $item['side'];

            $date = $rv->receipt_date ?: ($rv->entry_date ?: ($rv->created_at ? $rv->created_at->toDateString() : now()->toDateString()));
            $date = is_string($date) ? explode(' ', $date)[0] : now()->toDateString();

            if ($side === 'party') {
                $desc = "Receipt Voucher Party Side: " . ($rv->remarks ?: ($rv->reference_no ? "Invoice #" . $rv->reference_no : ($rv->rvid ?? 'RV')));
                $debit = 0;
                $credit = $amt;
            } else {
                $desc = "Sale Receipt — Voucher #" . ($rv->rvid ?? 'RV') . ($rv->reference_no ? " — Invoice #" . $rv->reference_no : '');
                $debit = $amt;
                $credit = 0;
            }

            \App\Models\AccountLedgerEntry::create([
                'account_id'        => $accountId,
                'branch_id'         => $rv->branch_id ?? ($account->branch_id ?? 0),
                'voucher_type'      => 'receipt',
                'voucher_no'        => $rv->reference_no ?: $rv->rvid,
                'voucher_id'        => $rv->id,
                'entry_no'          => \App\Models\AccountLedgerEntry::generateEntryNo($accountId, 'receipt'),
                'transaction_date'  => $date,
                'description'       => $desc,
                'debit'             => $debit,
                'credit'            => $credit,
                'running_balance'   => 0, // Recalculated immediately below
                'created_by'        => auth()->id() ?? 1,
            ]);
        }

        // Post unrecorded payments
        foreach ($unrecordedPayments as $item) {
            $pv = $item['pv'];
            $amt = $item['amount'];
            $side = $item['side'];

            $date = $pv->receipt_date ?: ($pv->entry_date ?: ($pv->created_at ? $pv->created_at->toDateString() : now()->toDateString()));
            $date = is_string($date) ? explode(' ', $date)[0] : now()->toDateString();

            if ($side === 'party') {
                $desc = "Payment Voucher Party Side: " . ($pv->remarks ?: ($pv->pvid ?? 'PV'));
                $debit = $amt;
                $credit = 0;
            } else {
                $desc = "Payment Voucher #" . ($pv->pvid ?? 'PV') . ($pv->remarks ? " — " . $pv->remarks : '');
                $debit = 0;
                $credit = $amt;
            }

            \App\Models\AccountLedgerEntry::create([
                'account_id'        => $accountId,
                'branch_id'         => $pv->branch_id ?? ($account->branch_id ?? 0),
                'voucher_type'      => 'payment',
                'voucher_no'        => $pv->pvid ?: $pv->reference_no,
                'voucher_id'        => $pv->id,
                'entry_no'          => \App\Models\AccountLedgerEntry::generateEntryNo($accountId, 'payment'),
                'transaction_date'  => $date,
                'description'       => $desc,
                'debit'             => $debit,
                'credit'            => $credit,
                'running_balance'   => 0, // Recalculated immediately below
                'created_by'        => auth()->id() ?? 1,
            ]);
        }

        // Post unrecorded expenses
        foreach ($unrecordedExpenses as $item) {
            $ev = $item['ev'];
            $amt = $item['amount'];
            $side = $item['side'];

            $date = $ev->entry_date ?: ($ev->created_at ? $ev->created_at->toDateString() : now()->toDateString());
            $date = is_string($date) ? explode(' ', $date)[0] : now()->toDateString();

            if ($side === 'party') {
                $desc = "Expense Voucher #" . ($ev->evid ?? 'EV') . ($ev->remarks ? " — " . $ev->remarks : '');
                $debit = 0;
                $credit = $amt;
            } else {
                $desc = "Expense Voucher #" . ($ev->evid ?? 'EV') . ($ev->remarks ? " — " . $ev->remarks : '');
                $debit = $amt;
                $credit = 0;
            }

            $entryNo = \App\Models\AccountLedgerEntry::generateEntryNo($accountId, 'expense');

            \App\Models\AccountLedgerEntry::create([
                'account_id'        => $accountId,
                'branch_id'         => $ev->branch_id ?? ($account->branch_id ?? 0),
                'voucher_type'      => 'expense',
                'voucher_no'        => $ev->evid,
                'voucher_id'        => $ev->id,
                'entry_no'          => $entryNo,
                'transaction_date'  => $date,
                'description'       => $desc,
                'debit'             => $debit,
                'credit'            => $credit,
                'running_balance'   => 0, // Recalculated immediately below
                'created_by'        => auth()->id() ?? 1,
            ]);
        }

        \App\Models\AccountLedgerEntry::recalculateRunningBalance($accountId);

        foreach (array_unique($otherAffectedAccounts ?? []) as $otherAccId) {
            if ($otherAccId && $otherAccId != $accountId) {
                \App\Models\AccountLedgerEntry::recalculateRunningBalance($otherAccId);
            }
        }
    }
}
