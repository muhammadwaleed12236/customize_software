<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    
    // Add closing_balance and previous_balance to appends so it's included in JSON responses
    protected $appends = ['closing_balance', 'previous_balance'];
    
    // app/Models/Customer.php
    protected $fillable = [
        'branch_id','customer_id', 'customer_name', 'customer_name_ur', 'cnic', 'filer_type', 'zone',
        'contact_person', 'mobile', 'email_address', 'contact_person_2', 'mobile_2',
        'email_address_2', 'opening_balance', 'credit_upto', 'credit_limit', 'address' , 'status','customer_type', 'no_credit_limit'
    ];

    public function ledgers()
    {
        return $this->hasMany(CustomerLedger::class, 'customer_id');
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    // Get the latest ledger entry (chronologically by created_at, then id)
    public function latestLedger()
    {
        return $this->hasOne(CustomerLedger::class, 'customer_id')
            ->orderByRaw("COALESCE(created_at, '1970-01-01') DESC")
            ->orderBy('id', 'desc');
    }

    public function openingBalanceHistories()
    {
        return $this->hasMany(CustomerOpeningBalanceHistory::class, 'customer_id')->latest();
    }

    // Accessor for closing balance (computes dynamic live balance matching Customer Ledger Report)
    public function getClosingBalanceAttribute()
    {
        try {
            return (float) app(\App\Http\Controllers\ReportingController::class)
                ->getCustomerOpeningBalance((int)$this->id, date('Y-m-d', strtotime('+1 day')));
        } catch (\Exception $e) {
            return (float) ($this->latestLedger()->value('closing_balance') ?? $this->opening_balance ?? 0);
        }
    }

    // Accessor for previous balance (synonym for latest closing balance before current invoice)
    public function getPreviousBalanceAttribute()
    {
        return $this->getClosingBalanceAttribute();
    }
}
