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
        Schema::table('sales', function (Blueprint $table) {
            // Montant avant remise (somme des lignes articles + services)
            $table->decimal('subtotal', 10, 2)->default(0)->after('user_id');

            // Type de remise appliquée sur la vente entière
            $table->enum('discount_type', ['percentage', 'fixed'])->nullable()->after('subtotal');

            // Valeur saisie par l'utilisateur (ex: 10 pour 10% ou 10 pour 10 unités monétaires)
            $table->decimal('discount_value', 10, 2)->default(0)->after('discount_type');

            // Montant réel de la remise calculé (toujours en valeur monétaire)
            $table->decimal('discount_amount', 10, 2)->default(0)->after('discount_value');

            // total_amount reste le montant final payé (subtotal - discount_amount)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'discount_type', 'discount_value', 'discount_amount']);
        });
    }
};