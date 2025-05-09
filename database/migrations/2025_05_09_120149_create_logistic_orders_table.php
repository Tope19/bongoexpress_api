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
        Schema::create('logistic_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('pickup_location_id');
            $table->foreignId('package_type_id')->constrained();
            $table->decimal('weight', 8, 2); // Package weight in kg
            $table->decimal('total_distance', 10, 2)->nullable(); // Total distance in km
            $table->decimal('total_price', 10, 2); // Total price
            $table->text('notes_for_rider')->nullable();
            $table->enum('_state', ['Orders Received', 'On the way', 'Delivered at drop off location'])->default('Orders Received');
            $table->enum('status', ['Pending', 'Ongoing', 'Completed', 'Cancelled'])->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logistic_orders');
    }
};
