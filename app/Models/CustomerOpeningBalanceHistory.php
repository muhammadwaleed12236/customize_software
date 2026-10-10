<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerOpeningBalanceHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'user_id',
        'old_opening_balance',
        'new_opening_balance',
        'resulting_closing_balance',
        'remarks',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
