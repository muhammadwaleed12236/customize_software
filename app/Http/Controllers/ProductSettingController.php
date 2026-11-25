<?php

namespace App\Http\Controllers;

use App\Models\ProductSetting;
use Illuminate\Http\Request;

class ProductSettingController extends Controller
{
    /**
     * Display the product settings page.
     */
    public function index()
    {
        $settings = ProductSetting::getSettings();
        return view('admin_panel.product.settings', compact('settings'));
    }

    /**
     * Update product settings.
     */
    public function update(Request $request)
    {
        $settings = ProductSetting::getSettings();

        $settings->update([
            'show_tracking_type'  => $request->has('show_tracking_type'),
            'show_packaging_type' => $request->has('show_packaging_type'),
            'show_item_name_urdu' => $request->has('show_item_name_urdu'),
            'show_model'          => $request->has('show_model'),
            'show_hs_code'        => $request->has('show_hs_code'),
            'show_color'          => $request->has('show_color'),
            'show_type'           => $request->has('show_type'),
            'show_brand'          => $request->has('show_brand'),
        ]);

        return redirect()->back()->with('success', 'Product settings updated successfully!');
    }
}
