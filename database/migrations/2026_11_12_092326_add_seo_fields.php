<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            if (! Schema::hasColumn('stores', 'seo')) {
                $table->json('seo')->nullable();
            }
        });

        foreach (['products', 'categories'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'seo_title')) {
                    $table->string('seo_title', 70)->nullable();
                }

                if (! Schema::hasColumn($tableName, 'seo_description')) {
                    $table->string('seo_description', 170)->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        // Intentionally empty: these columns may hold real content
    }
};
