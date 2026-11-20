<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentVoucher extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'processed' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($voucher) {
            // Auto-detect branch from paying account so voucher branch matches paying cashbook
            if (!empty($voucher->row_account_id)) {
                $accVal = $voucher->row_account_id;
                $firstAccId = null;
                if (is_array($accVal)) {
                    $firstAccId = $accVal[0] ?? null;
                } elseif (is_numeric($accVal)) {
                    $firstAccId = (int)$accVal;
                } elseif (is_string($accVal)) {
                    $decoded = json_decode($accVal, true);
                    if (is_array($decoded) && !empty($decoded)) {
                        $firstAccId = $decoded[0] ?? null;
                    } elseif (is_numeric($accVal)) {
                        $firstAccId = (int)$accVal;
                    }
                }
                if ($firstAccId) {
                    $acc = \App\Models\Account::find($firstAccId);
                    if ($acc && $acc->branch_id) {
                        $voucher->branch_id = (int)$acc->branch_id;
                        return;
                    }
                }
            }

            if (empty($voucher->branch_id) && auth()->check() && auth()->user()->branch_id) {
                $voucher->branch_id = (int)auth()->user()->branch_id;
            }
        });
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public static function generateInvoiceNo()
    {
        $prefix = 'PVID-';

        // Fetch last payment voucher
        $lastInvoice = self::orderBy('id', 'desc')->first();

        $lastNumber = 0;
        if ($lastInvoice && $lastInvoice->pvid) {
            $lastNumber = (int)substr($lastInvoice->pvid, strlen($prefix));
        }

        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

        return $prefix . $newNumber;
    }


    public function Vendor()
    {
        return $this->belongsTo(Vendor::class, 'party_id', 'id');
    }

    public function getPartyAttribute()
    {
        if ($this->type === 'vendor') {
            return $this->Vendor;   // vendor relation return karega
        } elseif ($this->type === 'customer' || $this->type === '1') {
            return $this->Customer; // customer relation return karega
        }
        return null;
    }


    // Account Head
    public function AccountHead()
    {
        return $this->belongsTo(AccountHead::class, 'row_account_head', 'id');
    }

    // Account
    public function Account()
    {
        return $this->belongsTo(Account::class, 'row_account_id', 'id');
    }
}
