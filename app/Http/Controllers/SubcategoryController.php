<?php

namespace App\Http\Controllers;

use App\Models\Subcategory;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class SubcategoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isSuperAdmin = $user && ($user->hasRole('super admin') || empty($user->branch_id));
        $userBranch = $user ? $user->branch_id : null;

        $catQuery = Category::query();
        $subQuery = Subcategory::with('category');

        if (!$isSuperAdmin && $userBranch) {
            $catQuery->forBranch((int)$userBranch);
            $subQuery->forBranch((int)$userBranch);
        }

        $category = $catQuery->get();
        $subcategory = $subQuery->get();

        return view("admin_panel.subcategory.index", compact('subcategory', 'category'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $userBranch = $user ? ($user->branch_id ?? 1) : 1;

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'category_id' => 'required',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => $validator->errors()->first()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('catagory_swal_error', $validator->errors()->first());
        }

        // UPDATE
        if ($request->filled('edit_id')) {
            $subcategory = Subcategory::find($request->edit_id);
            if (!$subcategory) {
                return response()->json([
                    'status' => 'error',
                    'error' => 'Record not found',
                    'message' => 'Record not found'
                ], 404);
            }
            $message = 'Subcategory Updated Successfully';
        }
        // CREATE
        else {
            $subcategory = new Subcategory();
            $subcategory->branch_id = $userBranch;
            $message = 'Subcategory Created Successfully';
        }

        $subcategory->name = $request->name;
        $subcategory->category_id = $request->category_id;
        $subcategory->save();

        // RESPONSE FOR ALERT
        if ($request->page === 'product_edit') {
            return redirect()
                ->route('products.edit', $request->product_id)
                ->with('success', 'Subcategory added successfully');
        } else if ($request->page === 'product_page') {
            return redirect()
                ->route('store')
                ->with('success', 'Subcategory added successfully');
        }

        return response()->json([
            'status' => 'success',
            'success' => $message,
            'message' => $message,
            'reload' => true
        ]);
    }

    public function delete($id)
    {
        $company = Subcategory::find($id);
        if ($company) {
            $company->delete();
            $msg = [
                'success' => 'Subcategory Deleted Successfully',
                'reload' =>  route('subcategory.home'),
            ];
        } else {
            $msg = ['error' => 'Subcategory Not Found'];
        }
        return response()->json($msg);
    }
}
