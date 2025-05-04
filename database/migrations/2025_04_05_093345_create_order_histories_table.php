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
        // orders
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('order_no')->unique();
            $table->enum('delivery_method', ['Door Delivery', 'Self Pickup']);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('subtotal_price', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->enum('status', ['Pending', 'Ongoing', 'Completed', 'Cancelled'])->default('Pending');
            $table->enum('payment_status', ['Paid', 'Failed', 'Pending']);
            $table->enum('payment_method', ['Bank Transfer', 'Paystack']);
            $table->timestamps();
        });

        // order-items
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('product_size_id')->constrained('product_sizes')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2); // Price at the time of order
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_histories');
    }
};
