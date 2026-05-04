<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paie extends Model
{
    protected $fillable = [
        'user_id',
        'periode_start',
        'periode_end',
        'salaire_brut',
        'total_primes',
        'total_retenues',
        'salaire_net',
        'statut'
    ];
 
    protected $casts = [
        'periode_start' => 'date',
        'periode_end' => 'date',
        'salaire_brut' => 'decimal:2',
        'total_primes' => 'decimal:2',
        'total_retenues' => 'decimal:2',
        'salaire_net' => 'decimal:2',

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function calculerSalaireNet()
    {
        $this->salaire_net =  $this->salaire_brut + $this->total_primes - $this->total_retenues;
        $this->save();
        return $this;
    }
}
