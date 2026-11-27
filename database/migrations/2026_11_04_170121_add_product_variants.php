<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_variants')) {
            Schema::create('product_variants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->string('option1', 60);
                $table->string('option2', 60)->nullable();
                $table->string('sku', 60)->nullable();
                $table->decimal('price', 12, 2)->nullable();
                $table->unsignedInteger('stock')->default(0);
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'has_variants')) {
                $table->boolean('has_variants')->default(false);
            }

            if (! Schema::hasColumn('products', 'option1_name')) {
                $table->string('option1_name', 30)->nullable();
            }

            if (! Schema::hasColumn('products', 'option2_name')) {
                $table->string('option2_name', 30)->nullable();
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'variant_id')) {
                $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            }

            if (! Schema::hasColumn('order_items', 'variant_name')) {
                $table->string('variant_name', 130)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'variant_id')) {
                $table->dropConstrainedForeignId('variant_id');
            }

            if (Schema::hasColumn('order_items', 'variant_name')) {
                $table->dropColumn('variant_name');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            foreach (['has_variants', 'option1_name', 'option2_name'] as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('product_variants');
    }
};
