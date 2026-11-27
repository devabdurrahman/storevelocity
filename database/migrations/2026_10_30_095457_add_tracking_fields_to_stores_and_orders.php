<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            if (! Schema::hasColumn('stores', 'facebook_pixel_id')) {
                $table->string('facebook_pixel_id', 32)->nullable();
            }

            if (! Schema::hasColumn('stores', 'ga_measurement_id')) {
                $table->string('ga_measurement_id', 20)->nullable();
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'purchase_tracked_at')) {
                $table->timestamp('purchase_tracked_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['facebook_pixel_id', 'ga_measurement_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('purchase_tracked_at');
        });
    }
};
