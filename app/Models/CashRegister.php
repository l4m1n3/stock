<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashRegister extends Model
{
    protected $fillable = ['branch_id', 'register_date', 'opening_balance'];

    protected $casts = [
        'register_date'   => 'date',
        'opening_balance' => 'decimal:2',
    ];
}
