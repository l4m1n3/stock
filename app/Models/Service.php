<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['name', 'type', 'price'];

    public function saleServices()
    {
        return $this->hasMany(SaleService::class);
    }
}
