<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'supplier_id', 'user_id', 'branch_id',
        'status', 'total_amount', 'notes',
        'ordered_at', 'received_at', 
    ];

    protected $casts = [
        'ordered_at'  => 'datetime',
        'received_at' => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItems::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function recalcTotal(): void
    {
        $this->update([
            'total_amount' => $this->items()->sum(
                \DB::raw('quantity_ordered * purchase_price')
            ),
        ]);
    }
}