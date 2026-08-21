<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->enum('ownership', ['owned', 'leased'])->default('leased')->after('reg_no');
            $table->unsignedTinyInteger('lease_due_day')->nullable()->after('monthly_rental');
        });

        DB::statement('ALTER TABLE vehicles MODIFY monthly_rental DECIMAL(15,2) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE vehicles MODIFY monthly_rental DECIMAL(15,2) NOT NULL');

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['ownership', 'lease_due_day']);
        });
    }
};
