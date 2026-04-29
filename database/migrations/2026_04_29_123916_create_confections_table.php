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
        Schema::create('confections', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // ex: Bouquet mariage, anniversaire
            $table->text('description')->nullable();
            $table->decimal('making_price', 10, 2)->default(0); // prix confection
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('confections');
    }
};
