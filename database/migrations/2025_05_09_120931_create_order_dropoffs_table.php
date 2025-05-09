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
        Schema::create('order_dropoffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logistic_order_id')->constrained('logistic_orders')->onDelete('cascade');
            $table->string('recipient_name');
            $table->string('address');
            $table->string('phone_number');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('distance_from_pickup', 10, 2)->nullable(); // Distance from pickup in km
            $table->decimal('price', 10, 2)->nullable(); // Price for this particular dropoff
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'delivered'])->default('pending');
            $table->integer('sequence')->default(0); // If there are multiple dropoffs in a specific order
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_dropoffs');
    }
};
