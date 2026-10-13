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
    ];

    protected $casts = [
        'show_gst' => 'boolean',
        'show_line_discount' => 'boolean',
        'show_overall_discount' => 'boolean',
        'show_watt' => 'boolean',
        'warehouse_mode' => 'string',
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
        ]);
    }
}
