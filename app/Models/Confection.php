<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Confection extends Model
{

    protected $fillable = [
        'name',
        'description',
        'making_price',
        'branch_id',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'confection_products')
            ->withPivot('quantity')
            ->withTimestamps();
    }
    public function saleConfections()
    {
        return $this->hasMany(SaleConfection::class);
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    /**
     * Prix total = making_price + somme(prix_produit × quantité)
     */
    public function getTotalPriceAttribute(): float
    {
        $productsTotal = $this->products->sum(fn($p) => $p->price * $p->pivot->quantity);
        return $this->making_price + $productsTotal;
    }
}
