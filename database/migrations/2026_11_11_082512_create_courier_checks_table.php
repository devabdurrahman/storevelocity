<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('courier_checks')) {
            Schema::create('courier_checks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('store_id')->constrained()->cascadeOnDelete();
                $table->string('phone', 20);
                $table->unsignedInteger('total')->default(0);
                $table->unsignedInteger('success')->default(0);
                $table->unsignedInteger('cancel')->default(0);
                $table->timestamp('checked_at');
                $table->timestamps();

                $table->unique(['store_id', 'phone']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_checks');
    }
};
