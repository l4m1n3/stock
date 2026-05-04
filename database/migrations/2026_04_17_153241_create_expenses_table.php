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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->decimal('amount', 10, 2);
            $table->string('type', 20)->default('autre');
            $table->date('expense_date');
            $table->enum('payment_method', ['amana','nita','cash','wave','western','moneygram'])->default('cash'); // 🔥 nouveau champ pour différencier les méthodes de paiement
            // status des dépenses : payé ou non payé
            $table->string('status', 20)->default('non payé');
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
