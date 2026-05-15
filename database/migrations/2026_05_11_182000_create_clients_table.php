<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();     
            $table->enum('tier', ['bronze', 'silver', 'gold', 'vip'])->default('bronze');
            $table->decimal('total_spent', 12, 2)->default(0);  // Cumulé automatiquement
            $table->integer('total_visits')->default(0);         // Nb d'achats/visites
            $table->text('notes')->nullable();                   // Notes internes
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};