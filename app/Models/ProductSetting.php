<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'show_tracking_type',
        'show_packaging_type',
        'show_item_name_urdu',
        'show_model',
        'show_hs_code',
        'show_color',
        'show_type',
        'show_brand',
    ];

    protected $casts = [
        'show_tracking_type'  => 'boolean',
        'show_packaging_type' => 'boolean',
        'show_item_name_urdu' => 'boolean',
        'show_model'          => 'boolean',
        'show_hs_code'        => 'boolean',
        'show_color'          => 'boolean',
        'show_type'           => 'boolean',
        'show_brand'          => 'boolean',
    ];

    /**
     * Get or create default settings singleton row
     */
    public static function getSettings()
    {
        return static::firstOrCreate([], [
            'show_tracking_type'  => true,
            'show_packaging_type' => true,
            'show_item_name_urdu' => true,
            'show_model'          => true,
            'show_hs_code'        => true,
            'show_color'          => true,
            'show_type'           => true,
            'show_brand'          => true,
        ]);
    }
}
