<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('stores', 'facebook_pixel_id')) {
            Schema::table('stores', function (Blueprint $table) {
                $table->string('facebook_pixel_id', 32)->nullable();
            });
        }

        if (! Schema::hasColumn('stores', 'ga_measurement_id')) {
            Schema::table('stores', function (Blueprint $table) {
                $table->string('ga_measurement_id', 20)->nullable();
            });
        }

        if (! Schema::hasColumn('orders', 'purchase_tracked_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->timestamp('purchase_tracked_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('stores', 'facebook_pixel_id')) {
            Schema::table('stores', fn (Blueprint $table) => $table->dropColumn('facebook_pixel_id'));
        }

        if (Schema::hasColumn('stores', 'ga_measurement_id')) {
            Schema::table('stores', fn (Blueprint $table) => $table->dropColumn('ga_measurement_id'));
        }

        if (Schema::hasColumn('orders', 'purchase_tracked_at')) {
            Schema::table('orders', fn (Blueprint $table) => $table->dropColumn('purchase_tracked_at'));
        }
    }
};
