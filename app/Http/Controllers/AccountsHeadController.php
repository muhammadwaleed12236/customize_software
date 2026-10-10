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
            $openingSum = (float)$accounts->sum('opening_balance');
            $currentSum = (float)$accounts->sum(function ($acc) {
                $lastEntry = AccountLedgerEntry::where('account_id', $acc->id)->latest('id')->first();
                return $lastEntry ? (float)$lastEntry->running_balance : (float)($acc->opening_balance ?? 0);
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
            $lastEntry = AccountLedgerEntry::where('account_id', $acc->id)->latest('id')->first();
            $acc->current_balance = $lastEntry ? (float)$lastEntry->running_balance : (float)($acc->opening_balance ?? 0);
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

        // ✅ Create account with auto-generated code
        Account::create([
            'branch_id'       => $branchId,
            'head_id'         => $request->head_id,
            'account_code'    => $accountCode,
            'title'           => $request->title,
            'type'            => $request->type,
            'opening_balance' => $request->opening_balance ?? 0,
            'status'          => $status,
        ]);

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
            'status'          => 'nullable|in:on',
        ];

        $request->validate($rules);

        // Store old values for audit log
        $oldTitle   = $account->title;
        $oldOpening = (float)($account->opening_balance ?? 0);
        $oldHeadId  = $account->head_id;
        $oldType    = $account->type;
        $oldStatus  = (int)($account->status == 1 || $account->status == 'active');

        $newTitle   = $request->title;
        $newOpening = (float)($request->opening_balance ?? 0);
        $newHeadId  = (int)$request->head_id;
        $newType    = $request->type;
        $newStatus  = $request->status === 'on' ? 1 : 0;

        $data = [
            'title'           => $newTitle,
            'type'            => $newType,
            'opening_balance' => $newOpening,
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
        $lastEntry = AccountLedgerEntry::where('account_id', $account->id)->latest('id')->first();
        $currentBal = $lastEntry ? (float)$lastEntry->running_balance : $oldOpening;
        $resultingCurrentBal = $currentBal + $diff;

        $changes = [];
        if (abs($diff) > 0.0001) {
            $changes[] = "Opening Balance: PKR " . number_format($oldOpening, 2) . " → PKR " . number_format($newOpening, 2);
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

        $account->update($data);

        return redirect()->back()->with('success', 'Account updated successfully.');
    }

    public function getAccountHistory($id)
    {
        $account = Account::with(['head', 'branch'])->findOrFail($id);
        $lastEntry = AccountLedgerEntry::where('account_id', $id)->latest('id')->first();
        $currentBal = $lastEntry ? (float)$lastEntry->running_balance : (float)($account->opening_balance ?? 0);

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

        // Date filters
        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');

        // Fetch ledger entries
        $query = \App\Models\AccountLedgerEntry::where('account_id', $accountId)
            ->orderBy('id', 'asc');

        if ($dateFrom) {
            $query->where('transaction_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('transaction_date', '<=', $dateTo);
        }

        $entries = $query->get();

        // Totals
        $totalDebit  = $entries->sum('debit');
        $totalCredit = $entries->sum('credit');
        $initialOpening = (float)($account->opening_balance ?? 0);
        if ($dateFrom) {
            $prevEntry = \App\Models\AccountLedgerEntry::where('account_id', $accountId)
                ->where('transaction_date', '<', $dateFrom)
                ->orderBy('id', 'desc')
                ->first();
            $openingBalance = $prevEntry ? (float)$prevEntry->running_balance : $initialOpening;
        } else {
            $openingBalance = $initialOpening;
        }

        // Closing balance = last entry running balance (or opening balance if no entries)
        $closingBalance = $entries->last()?->running_balance ?? $openingBalance;

        return view('admin_panel.chart_of_accounts.account_ledger', compact(
            'account',
            'entries',
            'totalDebit',
            'totalCredit',
            'openingBalance',
            'closingBalance',
            'dateFrom',
            'dateTo',
            'isSuperAdmin'
        ));
    }
}

