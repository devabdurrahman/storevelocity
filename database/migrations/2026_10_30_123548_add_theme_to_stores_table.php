<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            if (! Schema::hasColumn('stores', 'theme')) {
                $table->string('theme', 30)->default('classic');
            }

            if (! Schema::hasColumn('stores', 'theme_settings')) {
                $table->json('theme_settings')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            if (Schema::hasColumn('stores', 'theme')) {
                $table->dropColumn('theme');
            }

            if (Schema::hasColumn('stores', 'theme_settings')) {
                $table->dropColumn('theme_settings');
            }
        });
    }
};
