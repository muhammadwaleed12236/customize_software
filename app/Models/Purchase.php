<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'purchase_date' => 'date',
        'subtotal'      => 'decimal:2',
        'discount'      => 'decimal:2',
        'extra_cost'    => 'decimal:2',
        'net_amount'    => 'decimal:2',
        'paid_amount'   => 'decimal:2',
        'due_amount'    => 'decimal:2',
    ];

    // ✅ Always include formatted invoice in API/serialization
    protected $appends = ['formatted_invoice', 'invoice_display', 'receipt_status'];

    public function branch()   { return $this->belongsTo(Branch::class); }
    public function warehouse(){ return $this->belongsTo(Warehouse::class); }
    public function vendor()   { return $this->belongsTo(Vendor::class, 'vendor_id'); }
    public function items()    { return $this->hasMany(PurchaseItem::class); }
    public function inwardGatepasses() { return $this->hasMany(InwardGatepass::class); }

    /**
     * ✅ ERP STANDARD: Check if purchase has been received
     * Returns: 'pending' = awaiting inward gatepass
     *          'partial' = some items received, more expected
     *          'received' = all items received completely
     * 
     * @return string
     */
    public function getReceiptStatusAttribute()
    {
        // ✅ Local Market purchases are received immediately (direct inventory add)
        if ($this->purchase_type === 'local' || (!$this->vendor_id && $this->vendor_name)) {
            return 'received';
        }

        // Get unique products count in this purchase (since VendorRemaining aggregates by product_id)
        $uniqueProductCount = $this->items()->distinct('product_id')->count('product_id');
        if ($uniqueProductCount === 0) return 'received';

        $vendorRemainings = \App\Models\VendorRemaining::where('purchase_id', $this->id)->get();
        $recordsCount = $vendorRemainings->count();

        // 1. If no receipt activity has started
        if ($recordsCount === 0) {
            return 'pending';
        }

        // 2. Check if any product still has remaining qty
        $hasRemaining = $vendorRemainings->where('remaining_qty', '>', 0)->isNotEmpty();
        
        // 3. If everything is received (0 remaining across all records)
        // AND all unique products have at least been initialized in vendor_remaining
        if (!$hasRemaining && $recordsCount >= $uniqueProductCount) {
            return 'received';
        }

        // 4. If some items have been received but some still remaining or some products not yet touched
        $hasReceived = $vendorRemainings->where('received_qty', '>', 0)->isNotEmpty();
        return $hasReceived ? 'partial' : 'pending';
    }

    /**
     * ✅ ERP STANDARD: Get formatted invoice number
     * Ensures consistent format across all displays (P-INV-0001)
     * 
     * @return string
     */
    public function getFormattedInvoiceAttribute()
    {
        if (!$this->invoice_no) {
            return 'N/A';
        }
        
        // If already formatted as P-INV-, return as is
        if (strpos($this->invoice_no, 'P-INV') === 0) {
            return $this->invoice_no;
        }
        
        // Otherwise, format it
        return 'P-INV-' . str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }

    /**
     * ✅ Display invoice with reference info
     * Format: P-INV-0001 (Vendor Name) for reports and lists
     * 
     * @return string
     */
    public function getInvoiceDisplayAttribute()
    {
        $formatted = $this->formatted_invoice;
        if ($this->vendor) {
            return "{$formatted} ({$this->vendor->name})";
        }
        return $formatted;
    }

    /**
     * ✅ Convert Net Amount to Words
     */
    public function amountInWords()
    {
        $amount = (float)$this->net_amount;
        $number = (int)$amount;
        $decimal = round(($amount - $number) * 100);
        $fraction = "";

        if ($decimal > 0) {
            $fraction = " and " . $this->convertNumberToWords($decimal) . " Paisa";
        }

        return $this->convertNumberToWords($number) . " Rupees" . $fraction . " Only";
    }

    public function convertNumberToWords($number)
    {
        $hyphen      = '-';
        $conjunction = ' and ';
        $separator   = ', ';
        $negative    = 'negative ';
        $decimal     = ' point ';
        $dictionary  = array(
            0                   => 'Zero',
            1                   => 'One',
            2                   => 'Two',
            3                   => 'Three',
            4                   => 'Four',
            5                   => 'Five',
            6                   => 'Six',
            7                   => 'Seven',
            8                   => 'Eight',
            9                   => 'Nine',
            10                  => 'Ten',
            11                  => 'Eleven',
            12                  => 'Twelve',
            13                  => 'Thirteen',
            14                  => 'Fourteen',
            15                  => 'Fifteen',
            16                  => 'Sixteen',
            17                  => 'Seventeen',
            18                  => 'Eighteen',
            19                  => 'Nineteen',
            20                  => 'Twenty',
            30                  => 'Thirty',
            40                  => 'Forty',
            50                  => 'Fifty',
            60                  => 'Sixty',
            70                  => 'Seventy',
            80                  => 'Eighty',
            90                  => 'Ninety',
            100                 => 'Hundred',
            1000                => 'Thousand',
            1000000             => 'Million',
            1000000000          => 'Billion',
            1000000000000       => 'Trillion',
            1000000000000000    => 'Quadrillion',
            1000000000000000000 => 'Quintillion'
        );

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            return false;
        }

        if ($number < 0) {
            return $negative . $this->convertNumberToWords(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens   = ((int) ($number / 10)) * 10;
                $units  = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds  = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . $this->convertNumberToWords($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = $this->convertNumberToWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= $this->convertNumberToWords($remainder);
                }
                break;
        }

        return $string;
    }
}