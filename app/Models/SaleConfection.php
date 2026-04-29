<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleConfection extends Model
{
    protected $fillable = [
        'sale_id',
        'confection_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    public function confection()
    {
        return $this->belongsTo(Confection::class);
    }
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
