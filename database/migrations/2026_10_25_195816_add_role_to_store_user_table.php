<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_user', function (Blueprint $table) {
            $table->string('role', 20)->default('owner');
        });
    }

    public function down(): void
    {
        Schema::table('store_user', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
