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
        Schema::create('price_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('base_fare', 8, 2); // Base price for any delivery
            $table->decimal('price_per_km', 8, 2); // Price per kilometer
            $table->decimal('price_per_kg', 8, 2); // Price per kilogram
            $table->decimal('min_price', 8, 2); // Minimum delivery price
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_settings');
    }
};
