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

        $data = [
            'show_gst' => $request->has('show_gst'),
            'show_line_discount' => $request->has('show_line_discount'),
            'show_overall_discount' => $request->has('show_overall_discount'),
            'show_watt' => $request->has('show_watt'),
            'warehouse_mode' => $request->input('warehouse_mode', 'global') === 'product_wise' ? 'product_wise' : 'global',
            'purchase_party_source' => in_array($request->input('purchase_party_source'), ['both', 'vendors_only', 'customers_only']) 
                ? $request->input('purchase_party_source') 
                : 'both',
        ];

        // Invoice customization settings
        if ($request->has('invoice_company_name')) {
            $data['invoice_company_name'] = $request->input('invoice_company_name');
            $data['invoice_tagline'] = $request->input('invoice_tagline');
            $data['invoice_sub_description'] = $request->input('invoice_sub_description');
            $data['invoice_contacts_left'] = $request->input('invoice_contacts_left');
            $data['invoice_contacts_right'] = $request->input('invoice_contacts_right');
            $data['invoice_primary_color'] = $request->input('invoice_primary_color', '#c23321');
            $data['invoice_text_color'] = $request->input('invoice_text_color', '#000000');
            $data['invoice_terms_notes'] = $request->input('invoice_terms_notes');
            $data['invoice_branch_1'] = $request->input('invoice_branch_1');
            $data['invoice_branch_2'] = $request->input('invoice_branch_2');
            $data['invoice_footer_credit'] = $request->input('invoice_footer_credit');
            $data['invoice_min_rows'] = (int) $request->input('invoice_min_rows', 16);
            $data['invoice_show_watermark'] = $request->has('invoice_show_watermark');
        }

        // Handle invoice logo upload
        if ($request->hasFile('invoice_logo')) {
            $file = $request->file('invoice_logo');
            $filename = 'invoice_logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/invoice'), $filename);
            $data['invoice_logo'] = 'uploads/invoice/' . $filename;
        } elseif ($request->boolean('remove_invoice_logo')) {
            $data['invoice_logo'] = null;
        }

        $settings->update($data);

        return redirect()->route('sale.settings.index')
            ->with('success', 'Screen & Procurement Settings updated successfully!');
    }
}
