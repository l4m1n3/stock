<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleService extends Model
{


    // SaleService.php
    protected $fillable = ['sale_id', 'service_id', 'price'];
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
