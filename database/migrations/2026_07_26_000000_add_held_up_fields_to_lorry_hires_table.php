<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lorry_hires', function (Blueprint $table) {
            $table->decimal('held_hours', 8, 2)->nullable()->after('amount');
            $table->decimal('held_hourly_rate', 15, 2)->nullable()->after('held_hours');
            $table->decimal('held_fee', 15, 2)->default(0)->after('held_hourly_rate');
        });
    }

    public function down(): void
    {
        Schema::table('lorry_hires', function (Blueprint $table) {
            $table->dropColumn(['held_hours', 'held_hourly_rate', 'held_fee']);
        });
    }
};
