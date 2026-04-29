<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfectionProduct extends Model
{
    protected $fillable = [
        'confection_id',
        'product_id',
        'quantity',
    ];
    public function confection()
    {
        return $this->belongsTo(Confection::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getTotalCostAttribute()
    {
        return $this->quantity * $this->product->cost_price;
    }
}
