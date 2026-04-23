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
        // USERS
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('branch_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();

            $table->enum('role', ['dg', 'manager', 'staff'])
                ->default('staff')
                ->change();
        });

        // SALES
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('branch_id')
                ->after('user_id')
                ->constrained()
                ->cascadeOnDelete();
        });

        // INVOICES
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('branch_id')
                ->after('sale_id')
                ->constrained()
                ->cascadeOnDelete();
        });

        // STOCK MOVEMENTS
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('branch_id')
                ->constrained()
                ->cascadeOnDelete();
        });

        // EXPENSES
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('branch_id')
                ->constrained()
                ->cascadeOnDelete();
        });

        // (OPTIONNEL MAIS RECOMMANDÉ) PRODUCTS
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('branch_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
        });

        // (BONUS) ACTIVITY LOGS
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreignId('branch_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // USERS
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });

        // SALES
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });

        // INVOICES
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });

        // STOCK MOVEMENTS
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });

        // EXPENSES
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });

        // PRODUCTS
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });

        // ACTIVITY LOGS
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });
    }
};
