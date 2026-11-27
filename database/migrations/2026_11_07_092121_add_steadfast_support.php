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
        Schema::table('stores', function (Blueprint $table) {
            if (! Schema::hasColumn('stores', 'steadfast_api_key')) {
                $table->text('steadfast_api_key')->nullable();
            }

            if (! Schema::hasColumn('stores', 'steadfast_secret_key')) {
                $table->text('steadfast_secret_key')->nullable();
            }

            if (! Schema::hasColumn('stores', 'webhook_key')) {
                $table->ulid('webhook_key')->nullable()->unique();
            }

            if (! Schema::hasColumn('stores', 'steadfast_webhook_token')) {
                $table->text('steadfast_webhook_token')->nullable();
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'consignment_id')) {
                $table->string('consignment_id')->nullable()->index();
            }

            if (! Schema::hasColumn('orders', 'tracking_code')) {
                $table->string('tracking_code')->nullable();
            }

            if (! Schema::hasColumn('orders', 'courier_status')) {
                $table->string('courier_status', 50)->nullable();
            }

            if (! Schema::hasColumn('orders', 'courier_message')) {
                $table->text('courier_message')->nullable();
            }

            if (! Schema::hasColumn('orders', 'courier_booked_at')) {
                $table->timestamp('courier_booked_at')->nullable();
            }

            if (! Schema::hasColumn('orders', 'return_requested_at')) {
                $table->timestamp('return_requested_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
