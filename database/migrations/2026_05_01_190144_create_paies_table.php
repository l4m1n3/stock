<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('paies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->date('periode_start');
            $table->date('periode_end');
            $table->decimal('salaire_brut', 10, 2);
            $table->decimal('total_primes', 10, 2)->default(0);
            $table->decimal('total_retenues', 10, 2)->default(0);
            $table->decimal('salaire_net', 10, 2);
            // $table->enum('statut', ['brouillon', 'validé', 'payé'])->default('brouillon');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paies');
    }
};
