<?php

namespace App\Http\Controllers;

use App\Models\SalesOfficer;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalesOfficerController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasRole('super admin');

        $query = SalesOfficer::with('branch')->latest();

        // If not super admin and user has a branch, filter by branch
        if (!$isSuperAdmin && $user && $user->branch_id) {
            $query->where(function($q) use ($user) {
                $q->where('branch_id', $user->branch_id)
                  ->orWhereNull('branch_id');
            });
        }

        $officers = $query->get();
        $branches = Branch::all();

        return view('admin_panel.sales_officer.index', compact('officers', 'branches', 'isSuperAdmin'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'name_urdu' => 'nullable|string|max:255',
            'mobile'    => 'required|string|max:50',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $user = Auth::user();
        $branchId = $request->branch_id ?? ($user ? $user->branch_id : null) ?? Branch::value('id');

        $data = [
            'name'      => $request->name,
            'name_urdu' => $request->name_urdu,
            'mobile'    => $request->mobile,
            'branch_id' => $branchId,
        ];

        if ($request->edit_id) {
            $officer = SalesOfficer::findOrFail($request->edit_id);
            $officer->update($data);
            $msg = 'Sales Officer updated successfully.';
        } else {
            $officer = SalesOfficer::create($data);
            $msg = 'Sales Officer created successfully.';
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'officer' => $officer
            ]);
        }

        return redirect()->route('sales.officer.index')->with('success', $msg);
    }

    public function edit($id)
    {
        $officer = SalesOfficer::with('branch')->findOrFail($id);
        return response()->json($officer);
    }

    public function destroy($id)
    {
        $officer = SalesOfficer::findOrFail($id);
        $officer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sales Officer deleted successfully.'
        ]);
    }
}
