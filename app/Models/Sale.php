<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    // Sale.php
    protected $fillable = [
        'user_id',
        'client_id',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'total_amount',
        'payment_method',
        'sold_at',
        'branch_id',
    ];
    protected $casts = [
        // ✅ Sans ce cast, sold_at reste une string brute depuis la BDD
        'sold_at'         => 'datetime',
        'subtotal'        => 'decimal:2',
        'discount_value'  => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount'    => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function saleServices()
    {
        return $this->hasMany(SaleService::class);
    }

    public function saleConfections()
    {
        return $this->hasMany(SaleConfection::class);
    }
    public function services()
    {
        return $this->hasMany(SaleService::class);
    }

    public function confections()
    {
        return $this->hasMany(SaleConfection::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
    public function serviceSchedules()
    {
        return $this->hasMany(ServiceSchedule::class);
    }
}