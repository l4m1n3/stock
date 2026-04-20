<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    // Invoice.php
    protected $fillable = ['sale_id', 'invoice_number', 'total_amount', 'issued_at'];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
