<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    // Sale.php
    protected $fillable = ['user_id', 'total_amount', 'payment_method', 'sold_at','branch_id'];

     public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // ✅ AJOUTE ÇA
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    // ✅ AJOUTE AUSSI (car tu l'utilises)
    public function saleServices()
    {
        return $this->hasMany(SaleService::class);
    }   

    public function saleConfections()
    {
        return $this->hasMany(SaleConfection::class);
    }
     
}
