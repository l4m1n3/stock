<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashTransfer extends Model
{
    protected $fillable = [
        'from_method',
        'to_method',
        'amount',
        'note',
        'user_id',
        'branch_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
