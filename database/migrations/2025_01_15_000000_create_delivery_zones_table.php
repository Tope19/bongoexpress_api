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
        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 2);
            $table->foreignId('state_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('city_id')->nullable()->constrained()->onDelete('cascade');
            $table->decimal('pickup_latitude_min', 10, 7);
            $table->decimal('pickup_latitude_max', 10, 7);
            $table->decimal('pickup_longitude_min', 10, 7);
            $table->decimal('pickup_longitude_max', 10, 7);
            $table->decimal('dropoff_latitude_min', 10, 7);
            $table->decimal('dropoff_latitude_max', 10, 7);
            $table->decimal('dropoff_longitude_min', 10, 7);
            $table->decimal('dropoff_longitude_max', 10, 7);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_zones');
    }
};
