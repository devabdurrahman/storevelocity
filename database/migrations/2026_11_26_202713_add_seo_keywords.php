<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'seo_keyword')) {
                $table->string('seo_keyword', 60)->nullable();
            }

            if (! Schema::hasColumn('products', 'search_words')) {
                $table->string('search_words', 300)->nullable();
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (! Schema::hasColumn('categories', 'seo_keyword')) {
                $table->string('seo_keyword', 60)->nullable();
            }
        });
    }

    public function down(): void
    {
        // Intentionally empty: these columns may hold real content
    }
};
