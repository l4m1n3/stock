<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;


class RecomputeClientTotals extends Command
{
    protected $signature = 'clients:recompute-totals';
    protected $description = 'Recalcule total_spent et total_visits pour tous les clients';
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = \App\Models\Client::count();
        $this->info("Recalcul de {$count} clients...");

        \App\Models\Client::all()->each(function ($client) {
            $client->recomputeTotals();
        });

        $this->info('Terminé.');
    }
}
