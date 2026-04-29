<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    protected $fillable = ['name', 'description', 'price', 'stock_quantity', 'alert_threshold', 'branch_id'];
    
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function confections()
    {
        return $this->belongsToMany(Confection::class, 'confection_products')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
