<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            if (! Schema::hasColumn('stores', 'delivery_inside_enabled')) {
                $table->boolean('delivery_inside_enabled')->default(true);
            }

            if (! Schema::hasColumn('stores', 'delivery_inside_free_over')) {
                $table->decimal('delivery_inside_free_over', 10, 2)->nullable();
            }

            if (! Schema::hasColumn('stores', 'delivery_outside_enabled')) {
                $table->boolean('delivery_outside_enabled')->default(false);
            }

            if (! Schema::hasColumn('stores', 'delivery_outside_fee')) {
                $table->decimal('delivery_outside_fee', 10, 2)->nullable();
            }

            if (! Schema::hasColumn('stores', 'delivery_outside_free_over')) {
                $table->decimal('delivery_outside_free_over', 10, 2)->nullable();
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'delivery_zone')) {
                $table->string('delivery_zone', 20)->nullable();
            }
        });
    }

    public function down(): void
    {
        // Intentionally empty: these columns may hold real data
    }
};
