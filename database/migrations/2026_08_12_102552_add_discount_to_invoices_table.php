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
        Schema::table('invoices', function (Blueprint $table) {
            // On garde une trace du sous-total et de la remise sur la facture,
            // au cas où le montant de la vente changerait plus tard.
            $table->decimal('subtotal', 10, 2)->default(0)->after('sale_id');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('subtotal');

            // total_amount reste le montant final facturé
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'discount_amount']);
        });
    }
};