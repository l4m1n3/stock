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
        Schema::create('service_schedules', function (Blueprint $table) {
            $table->id();

            // Service concerné
            $table->foreignId('service_id')
                ->constrained()
                ->cascadeOnDelete();

            // Vente associée (optionnel)
            $table->foreignId('sale_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Informations client
            $table->string('client_name')->nullable();
            $table->string('contact_number');

            // Adresse de la prestation
            $table->text('address');

            // Date et heure
            $table->date('scheduled_date');
            $table->time('scheduled_time')->nullable();

            // Statut
            $table->enum('status', [
                'pending',
                'confirmed',
                'completed',
                'cancelled'
            ])->default('pending');

            // Notes supplémentaires
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_schedules');
    }
};