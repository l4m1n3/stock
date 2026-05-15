<?php

namespace App\Observers;

use App\Models\Sale;

class SaleObserver
{
    /**
     * Après chaque vente créée, mettre à jour le client automatiquement.
     */
    public function created(Sale $sale): void
    {
        if (! $sale->client_id) return;

        $client = $sale->client;

        $client->increment('total_visits');
        $client->increment('total_spent', $sale->total_amount);
        $client->refresh();

        $client->recalculateTier($sale);
    }
}