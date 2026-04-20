<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    // Sale.php
    protected $fillable = ['user_id', 'total_amount', 'payment_method', 'sold_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
