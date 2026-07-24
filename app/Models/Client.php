<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'tier',
        'total_spent',
        'total_visits',
        'notes',
        'branch_id',
    ];

    protected $casts = [
        'birthday'    => 'date',
        'total_spent' => 'decimal:2',
    ];

    // ─── Relations ────────────────────────────────────────────────
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // public function loyaltyLogs()
    // {
    //     return $this->hasMany(ClientLoyaltyLog::class);
    // }

    // ─── Seuils de classement ─────────────────────────────────────
    public static array $tierThresholds = [
        'bronze' => 0,
        'silver' => 50_000,   // 50 000 FCFA
        'gold'   => 150_000,  // 150 000 FCFA
        'vip'    => 500_000,  // 500 000 FCFA
    ];

    /**
     * Recalcule le tier selon total_spent et journalise si changement.
     */
    public function recalculateTier(?Sale $sale = null): void
    {
        $newTier = 'bronze';
        foreach (self::$tierThresholds as $tier => $threshold) {
            if ($this->total_spent >= $threshold) {
                $newTier = $tier;
            }
        }

        if ($newTier !== $this->tier) {
            $this->loyaltyLogs()->create([
                'sale_id'  => $sale?->id,
                'event'    => 'tier_upgrade',
                'old_tier' => $this->tier,
                'new_tier' => $newTier,
                'amount'   => $sale?->total_amount ?? 0,
                'note'     => "Passage de {$this->tier} à {$newTier}",
            ]);
            $this->tier = $newTier;
            $this->save();
        }
    }

    // ─── Accesseurs ───────────────────────────────────────────────
    public function getTierBadgeAttribute(): string
    {
        return match ($this->tier) {
            'silver' => '🥈 Silver',
            'gold'   => '🥇 Gold',
            'vip'    => '💎 VIP',
            default  => '🥉 Bronze',
        };
    }
   public function recomputeTotals(): void
{
    $this->update([
        'total_spent'  => $this->sales()->sum('total_amount'),
        'total_visits' => $this->sales()->count(),
    ]);
}
}
