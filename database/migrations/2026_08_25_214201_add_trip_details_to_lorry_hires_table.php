<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // All optional — a hire can still be recorded with just an amount, same as
        // before. These only feed the client-facing receipt when they're filled in.
        Schema::table('lorry_hires', function (Blueprint $table) {
            $table->string('from_location')->nullable()->after('hirer_name');
            $table->string('to_location')->nullable()->after('from_location');
            $table->decimal('distance_km', 8, 2)->nullable()->after('to_location');
            $table->dateTime('started_at')->nullable()->after('distance_km');
            $table->dateTime('ended_at')->nullable()->after('started_at');
        });
    }

    public function down(): void
    {
        Schema::table('lorry_hires', function (Blueprint $table) {
            $table->dropColumn(['from_location', 'to_location', 'distance_km', 'started_at', 'ended_at']);
        });
    }
};
