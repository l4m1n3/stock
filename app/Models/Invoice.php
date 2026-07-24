<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    // Invoice.php
    protected $fillable = ['sale_id', 'invoice_number', 'total_amount', 'issued_at', 'branch_id', 'status'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
    // Scope pratique pour filtrer les factures actives
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
