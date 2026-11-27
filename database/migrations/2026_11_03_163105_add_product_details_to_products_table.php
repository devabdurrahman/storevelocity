<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'short_description')) {
                $table->string('short_description', 300)->nullable();
            }

            if (! Schema::hasColumn('products', 'description')) {
                $table->text('description')->nullable();
            }

            if (! Schema::hasColumn('products', 'compare_price')) {
                $table->decimal('compare_price', 12, 2)->nullable();
            }

            if (! Schema::hasColumn('products', 'sku')) {
                $table->string('sku', 60)->nullable();
            }

            if (! Schema::hasColumn('products', 'unit')) {
                $table->string('unit', 30)->nullable();
            }

            if (! Schema::hasColumn('products', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unique(['store_id', 'sku'], 'products_store_sku_unique');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_store_sku_unique');

            foreach (['short_description', 'description', 'compare_price', 'sku', 'unit', 'is_active'] as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
