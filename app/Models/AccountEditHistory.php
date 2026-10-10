<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountEditHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'branch_id',
        'user_id',
        'old_opening_balance',
        'new_opening_balance',
        'resulting_current_balance',
        'old_title',
        'new_title',
        'changes_summary',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
