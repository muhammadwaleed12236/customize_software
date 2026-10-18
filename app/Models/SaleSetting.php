<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'show_gst',
        'show_line_discount',
        'show_overall_discount',
        'show_watt',
        'warehouse_mode',
        'invoice_logo',
        'invoice_company_name',
        'invoice_tagline',
        'invoice_sub_description',
        'invoice_contacts_left',
        'invoice_contacts_right',
        'invoice_primary_color',
        'invoice_text_color',
        'invoice_terms_notes',
        'invoice_branch_1',
        'invoice_branch_2',
        'invoice_footer_credit',
        'invoice_min_rows',
        'invoice_show_watermark',
    ];

    protected $casts = [
        'show_gst' => 'boolean',
        'show_line_discount' => 'boolean',
        'show_overall_discount' => 'boolean',
        'show_watt' => 'boolean',
        'warehouse_mode' => 'string',
        'invoice_show_watermark' => 'boolean',
        'invoice_min_rows' => 'integer',
    ];

    /**
     * Get or create default settings singleton row
     */
    public static function getSettings()
    {
        return static::firstOrCreate([], [
            'show_gst' => true,
            'show_line_discount' => true,
            'show_overall_discount' => true,
            'show_watt' => false,
            'warehouse_mode' => 'global',
            'invoice_company_name' => 'GLC',
            'invoice_tagline' => 'Long Life Accessories',
            'invoice_sub_description' => "Power Bank, Battery, Data Cable, Charger, Head Phone\nHandsfree, Bluetooth & Multimedia",
            'invoice_contacts_left' => "Faisal Mehmood\n0325 4682466\nFurqaan Shakeel\n03044576200",
            'invoice_contacts_right' => "Anjum Shakeel\n03008033184\nTayyab Shakeel\n03054453139\nPTCL #: 042 37322272\n042 37232370",
            'invoice_primary_color' => '#c23321',
            'invoice_text_color' => '#000000',
            'invoice_terms_notes' => "1. No Return no Exchange without bill\n2. Change of Mind is not accepted\n3. Damaged or Burned not Accepted",
            'invoice_branch_1' => 'Branch#1: Shop #22,45, Basement Madina Center The Mall Hall Road, Lhr.',
            'invoice_branch_2' => 'Branch#2: Shop #40,43, 3rd Floor Hassan Center Hall Road, Lhr.',
            'invoice_footer_credit' => 'Powered by Prowave Technologies 📞 +92 317 3836 223',
            'invoice_min_rows' => 16,
            'invoice_show_watermark' => true,
        ]);
    }
}
