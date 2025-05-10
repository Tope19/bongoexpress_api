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
        Schema::create('logistic_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logistic_order_id')->constrained('logistic_orders')->onDelete('cascade');
            $table->string('reference')->unique();
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['Pending', 'Success', 'Failed'])->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logistic_payments');
    }
};
