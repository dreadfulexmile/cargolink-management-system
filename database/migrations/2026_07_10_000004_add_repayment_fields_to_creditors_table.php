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
        Schema::table('creditors', function (Blueprint $table) {
            $table->decimal('monthly_repayment', 15, 2)->nullable()->after('outstanding');
            $table->unsignedTinyInteger('repayment_due_day')->nullable()->after('monthly_repayment');
            $table->unsignedSmallInteger('repayment_term_months')->nullable()->after('repayment_due_day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('creditors', function (Blueprint $table) {
            $table->dropColumn(['monthly_repayment', 'repayment_due_day', 'repayment_term_months']);
        });
    }
};
