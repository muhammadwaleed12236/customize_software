<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isSuperAdmin = $user && ($user->hasRole('super admin') || empty($user->branch_id));
        $userBranch = $user ? $user->branch_id : null;

        $query = Category::query();
        if (!$isSuperAdmin && $userBranch) {
            $query->forBranch((int)$userBranch);
        }
        $category = $query->get();
      
        return view("admin_panel.category.index", compact('category'));
    }

    public function catagorystore(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 422);
        }

        $user = Auth::user();
        $userBranch = $user ? ($user->branch_id ?? 1) : 1;

        // Check if category already exists for this branch (excluding edit_id)
        $query = Category::where('name', $request->name)->where('branch_id', $userBranch);
        if ($request->filled('edit_id')) {
            $query->where('id', '!=', $request->edit_id);
        }
        $exists = $query->exists();
        
        if ($exists) {
            return response()->json([
                'error' => 'Category name already exists'
            ], 409);
        }

        // Create or Update category
        if ($request->filled('edit_id')) {
            $category = Category::findOrFail($request->edit_id);
            $message = 'Category Updated Successfully';
        } else {
            $category = new Category();
            $category->branch_id = $userBranch;
            $message = 'Category Inserted Successfully';
        }

        $category->name = $request->name;
        $category->save();

        return response()->json([
            'success' => $message,
            'reload' => true
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $userBranch = $user ? ($user->branch_id ?? 1) : 1;

        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('catagory_swal_error', $validator->errors()->first());
        }

        /**
         * UPDATE CATEGORY
         */
        if ($request->filled('edit_id')) {
            $category = Category::findOrFail($request->edit_id);
            $category->name = $request->name;
            $category->save();

            return response()->json([
                'success' => 'Category Updated Successfully',
                'reload'  => true
            ]);
        }

        /**
         * CREATE CATEGORY
         */
        $category = new Category();
        $category->name = $request->name;
        $category->branch_id = $userBranch;
        $category->save();

        /**
         * IF REQUEST FROM PRODUCT PAGE
         */
        if ($request->page === 'product_edit') {
            return redirect()
                ->route('products.edit', $request->product_id)
                ->with('success', 'Category added successfully');
        } else if ($request->page === 'product_page') {
            return redirect()
                ->route('store')
                ->with('success', 'Category added successfully');
        }

        /**
         * NORMAL FLOW
         */
        return response()->json([
            'success'  => 'Category Created Successfully',
            'redirect' => route('Category.home')
        ]);
    }

    public function delete($id)
    {
        $company = Category::find($id);
        if ($company) {
            $company->delete();
            $msg = [
                'success' => 'Category Deleted Successfully',
                'reload' =>  route('Category.home'),
            ];
        } else {
            $msg = ['error' => 'Category Not Found'];
        }
        return response()->json($msg);
    }
}
