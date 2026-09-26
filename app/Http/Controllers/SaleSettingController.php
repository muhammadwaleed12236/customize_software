<?php

namespace App\Http\Controllers;

use App\Models\SaleSetting;
use Illuminate\Http\Request;

class SaleSettingController extends Controller
{
    /**
     * Display the sale screen settings page.
     */
    public function index()
    {
        $settings = SaleSetting::getSettings();
        return view('admin_panel.sale.settings', compact('settings'));
    }

    /**
     * Update the sale screen settings.
     */
    public function update(Request $request)
    {
        $settings = SaleSetting::getSettings();

        $settings->update([
            'show_gst' => $request->has('show_gst'),
            'show_line_discount' => $request->has('show_line_discount'),
            'show_overall_discount' => $request->has('show_overall_discount'),
        ]);

        return redirect()->route('sale.settings.index')
            ->with('success', 'Sale Screen Settings updated successfully!');
    }
}
