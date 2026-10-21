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

        // Calculate totals for each branch
        $branchesWithTotals = $branches->map(function ($branch) {
            $accounts = $branch->accounts;
            $openingSum = (float)$accounts->sum(function ($acc) {
                return (float)($acc->opening_balance ?? 0);
            });
            $currentSum = (float)$accounts->sum(function ($acc) {
                return AccountLedgerEntry::getClosingBalance($acc->id);
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

        // ✅ Get accounts with current running balances from ledger
        $allAccounts = $branch->accounts()->with('head')->get()->map(function ($acc) {
            $acc->opening_balance = (float)($acc->opening_balance ?? 0);

            $lastEntry = AccountLedgerEntry::getClosingEntry($acc->id);
            $acc->current_balance = $lastEntry ? (float)$lastEntry->running_balance : (float)$acc->opening_balance;
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
        $this->syncAccountLedger($account);

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
    private function syncAccountLedger($account)
    {
        $accountId = $account->id;
        $accountType = trim(strtolower($account->type ?? 'debit'));
        $isReceivable = (int)($account->head_id ?? 0) === 8 || str_contains(strtolower($account->head?->name ?? ''), 'receivable');

        // ✅ 0. Clean up any improperly synced party entries that belong to vendors or customers
        if ($isReceivable) {
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
                        $hasNumeric = false;
                        foreach ($types as $t) {
                            if (is_numeric($t)) {
                                $hasNumeric = true;
                                break;
                            }
                        }
                        if (!$hasNumeric) {
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
                        $hasNumeric = false;
                        foreach ($types as $t) {
                            if (is_numeric($t)) {
                                $hasNumeric = true;
                                break;
                            }
                        }
                        if (!$hasNumeric) {
                            $entry->delete();
                            $deletedCount++;
                        }
                    }
                }
            }
            if ($deletedCount > 0) {
                \App\Models\AccountLedgerEntry::recalculateRunningBalance($accountId);
            }
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

        // 1. Gather all active receipts vouchers that reference this account
        $rvQuery = \App\Models\ReceiptsVoucher::where(function($q) {
            $q->whereNull('status')->orWhere('status', '!=', 'voided');
        });

        if ($isReceivable) {
            $rvQuery->where(function($q) use ($accountId) {
                $q->where('party_id', $accountId)
                  ->orWhere('party_id', (string)$accountId)
                  ->orWhere('party_id', 'like', '%"' . $accountId . '"%')
                  ->orWhere('party_id', 'like', '%[' . $accountId . ',%')
                  ->orWhere('party_id', 'like', '%,' . $accountId . ']%')
                  ->orWhere('party_id', 'like', '%[' . $accountId . ']%')
                  ->orWhere('party_id', 'like', '%,' . $accountId . ',%');
            })->where(function($q) {
                $q->whereNull('type')
                  ->orWhere(function($sub) {
                      $sub->where('type', 'not like', '%vendor%')
                          ->where('type', 'not like', '%customer%')
                          ->where('type', 'not like', '%walkin%')
                          ->where('type', 'not like', '%SALE_RECEIPT%');
                  });
            });
        } else {
            $rvQuery->where(function($q) use ($accountId) {
                $q->where('row_account_id', $accountId)
                  ->orWhere('row_account_id', (string)$accountId)
                  ->orWhere('row_account_id', 'like', '%[' . $accountId . ',%')
                  ->orWhere('row_account_id', 'like', '%,' . $accountId . ']%')
                  ->orWhere('row_account_id', 'like', '%[' . $accountId . ']%')
                  ->orWhere('row_account_id', 'like', '%,' . $accountId . ',%');
            });
        }
        $rvs = $rvQuery->orderBy('id', 'asc')->get();

        $unrecordedReceipts = [];
        foreach ($rvs as $rv) {
            $amt = $isReceivable
                ? $getPartyAccountAmount($rv, $accountId)
                : $getAccountAmount($rv->row_account_id, $rv->amount, $rv->total_amount, $accountId);

            if ($amt > 0) {
                $alreadyRecorded = \App\Models\AccountLedgerEntry::where('account_id', $accountId)
                    ->where(function($q) use ($rv) {
                        $q->where('voucher_id', $rv->id)
                          ->orWhere('voucher_no', $rv->rvid)
                          ->orWhere('voucher_no', $rv->reference_no);
                    })->exists();

                if (!$alreadyRecorded) {
                    $unrecordedReceipts[] = [
                        'rv' => $rv,
                        'amount' => $amt,
                    ];
                }
            }
        }

        // 2. Gather all active payment vouchers that reference this account
        $pvQuery = \App\Models\PaymentVoucher::where(function($q) {
            $q->whereNull('status')->orWhere('status', '!=', 'voided');
        });

        if ($isReceivable) {
            $pvQuery->where(function($q) use ($accountId) {
                $q->where('party_id', $accountId)
                  ->orWhere('party_id', (string)$accountId)
                  ->orWhere('party_id', 'like', '%"' . $accountId . '"%')
                  ->orWhere('party_id', 'like', '%[' . $accountId . ',%')
                  ->orWhere('party_id', 'like', '%,' . $accountId . ']%')
                  ->orWhere('party_id', 'like', '%[' . $accountId . ']%')
                  ->orWhere('party_id', 'like', '%,' . $accountId . ',%');
            })->where(function($q) {
                $q->whereNull('type')
                  ->orWhere(function($sub) {
                      $sub->where('type', 'not like', '%vendor%')
                          ->where('type', 'not like', '%customer%')
                          ->where('type', 'not like', '%walkin%');
                  });
            });
        } else {
            $pvQuery->where(function($q) use ($accountId) {
                $q->where('row_account_id', $accountId)
                  ->orWhere('row_account_id', (string)$accountId)
                  ->orWhere('row_account_id', 'like', '%[' . $accountId . ',%')
                  ->orWhere('row_account_id', 'like', '%,' . $accountId . ']%')
                  ->orWhere('row_account_id', 'like', '%[' . $accountId . ']%')
                  ->orWhere('row_account_id', 'like', '%,' . $accountId . ',%');
            });
        }
        $pvs = $pvQuery->orderBy('id', 'asc')->get();

        $unrecordedPayments = [];
        foreach ($pvs as $pv) {
            $amt = $isReceivable
                ? $getPartyAccountAmount($pv, $accountId)
                : $getAccountAmount($pv->row_account_id, $pv->amount, $pv->total_amount, $accountId);

            if ($amt > 0) {
                $alreadyRecorded = \App\Models\AccountLedgerEntry::where('account_id', $accountId)
                    ->where(function($q) use ($pv) {
                        $q->where('voucher_id', $pv->id)
                          ->orWhere('voucher_no', $pv->pvid)
                          ->orWhere('voucher_no', $pv->reference_no);
                    })->exists();

                if (!$alreadyRecorded) {
                    $unrecordedPayments[] = [
                        'pv' => $pv,
                        'amount' => $amt,
                    ];
                }
            }
        }

        // 3. Gather all active expense vouchers that reference this account
        $evQuery = \App\Models\ExpenseVoucher::where(function($q) {
            $q->whereNull('status')->orWhere('status', '!=', 'voided');
        });

        $evQuery->where(function($q) use ($accountId) {
            $q->where('row_account_id', $accountId)
              ->orWhere('row_account_id', (string)$accountId)
              ->orWhere('row_account_id', 'like', '%"' . $accountId . '"%')
              ->orWhere('row_account_id', 'like', '%[' . $accountId . ',%')
              ->orWhere('row_account_id', 'like', '%,' . $accountId . ']%')
              ->orWhere('row_account_id', 'like', '%[' . $accountId . ']%')
              ->orWhere('row_account_id', 'like', '%,' . $accountId . ',%');
        });
        $evs = $evQuery->orderBy('id', 'asc')->get();

        $unrecordedExpenses = [];
        foreach ($evs as $ev) {
            $amt = $getAccountAmount($ev->row_account_id, $ev->amount, $ev->total_amount, $accountId);
            if ($amt > 0) {
                $alreadyRecorded = \App\Models\AccountLedgerEntry::where('account_id', $accountId)
                    ->where(function($q) use ($ev) {
                        $q->where('voucher_id', $ev->id)
                          ->orWhere('voucher_no', $ev->evid);
                    })->exists();

                if (!$alreadyRecorded) {
                    $unrecordedExpenses[] = [
                        'ev' => $ev,
                        'amount' => $amt,
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

            $date = $rv->receipt_date ?: ($rv->entry_date ?: ($rv->created_at ? $rv->created_at->toDateString() : now()->toDateString()));
            $date = is_string($date) ? explode(' ', $date)[0] : now()->toDateString();

            $desc = $isReceivable
                ? "Receipt Voucher Party Side: " . ($rv->remarks ?: ($rv->reference_no ? "Invoice #" . $rv->reference_no : ($rv->rvid ?? 'RV')))
                : ("Sale Receipt — Voucher #" . ($rv->rvid ?? 'RV') . ($rv->reference_no ? " — Invoice #" . $rv->reference_no : ''));

            // For Account Receivable: customer receipt is CREDIT (reduces receivable).
            // For Cash/Bank: receipt is DEBIT (cash in).
            $debit = $isReceivable ? 0 : $amt;
            $credit = $isReceivable ? $amt : 0;

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

            $date = $pv->receipt_date ?: ($pv->entry_date ?: ($pv->created_at ? $pv->created_at->toDateString() : now()->toDateString()));
            $date = is_string($date) ? explode(' ', $date)[0] : now()->toDateString();

            $desc = $isReceivable
                ? "Payment Voucher Party Side: " . ($pv->remarks ?: ($pv->pvid ?? 'PV'))
                : "Payment Voucher #" . ($pv->pvid ?? 'PV') . ($pv->remarks ? " — " . $pv->remarks : '');

            // For Account Receivable: customer payment is DEBIT (receivable increases).
            // For Cash/Bank: payment is CREDIT (cash out).
            $debit = $isReceivable ? $amt : 0;
            $credit = $isReceivable ? 0 : $amt;

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

            $lastEntry = \App\Models\AccountLedgerEntry::where('account_id', $accountId)->orderByDesc('id')->first();
            $prevBal = $lastEntry ? (float)$lastEntry->running_balance : (float)($account->opening_balance ?? 0);
            $newBal = $accountType === 'credit' ? ($prevBal + $amt) : ($prevBal - $amt);

            $entryNo = \App\Models\AccountLedgerEntry::generateEntryNo($accountId, 'expense');
            $date = $ev->entry_date ?: ($ev->created_at ? $ev->created_at->toDateString() : now()->toDateString());
            $date = is_string($date) ? explode(' ', $date)[0] : now()->toDateString();

            $desc = "Expense Voucher #" . ($ev->evid ?? 'EV');
            if ($ev->remarks) {
                $desc .= " — " . $ev->remarks;
            }

            \App\Models\AccountLedgerEntry::create([
                'account_id'        => $accountId,
                'branch_id'         => $ev->branch_id ?? ($account->branch_id ?? 0),
                'voucher_type'      => 'expense',
                'voucher_no'        => $ev->evid,
                'voucher_id'        => $ev->id,
                'entry_no'          => $entryNo,
                'transaction_date'  => $date,
                'description'       => $desc,
                'debit'             => 0,
                'credit'            => $amt,
                'running_balance'   => $newBal,
                'created_by'        => auth()->id() ?? 1,
            ]);
        }

        \App\Models\AccountLedgerEntry::recalculateRunningBalance($accountId);
    }
}

