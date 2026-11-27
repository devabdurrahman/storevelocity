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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->ulid('public_id')->unique();
            $table->unsignedInteger('number');
            $table->string('customer_name');
            $table->string('customer_phone', 20);
            $table->text('customer_address');
            $table->text('customer_note')->nullable();
            $table->string('payment_method', 20)->default('cod');
            $table->string('status', 20)->default('pending');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('shipping_fee', 12, 2);
            $table->decimal('total', 12, 2);
            $table->timestamps();

            $table->unique(['store_id', 'number']);
            $table->index(['store_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
