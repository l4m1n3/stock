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
             // Snapshot du nom/contact au moment de la facture (données figées)
            $table->foreignId('client_id')->nullable()->after('branch_id')
                  ->constrained()->nullOnDelete();
            $table->string('client_name')->nullable()->after('client_id');   // Snapshot
            $table->string('client_phone')->nullable()->after('client_name');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn('client_id');
            $table->dropColumn('client_name');
            $table->dropColumn('client_phone');
           
        });
    }
};
