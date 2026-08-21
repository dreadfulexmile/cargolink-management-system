<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Every domain table gets a deleted_at column so records are recoverable
     * instead of permanently lost. System/package tables (cache, queue jobs,
     * spatie permission tables) are intentionally left alone.
     */
    private array $tables = [
        'users',
        'customers',
        'charge_types',
        'jobs',
        'job_cost_lines',
        'job_advances',
        'invoices',
        'invoice_lines',
        'payments',
        'expense_categories',
        'expenses',
        'vehicles',
        'lease_payments',
        'director_transactions',
        'creditors',
        'creditor_payments',
        'lorries',
        'lorry_expenses',
        'lorry_hires',
        'job_service_costs',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->softDeletes();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropSoftDeletes();
            });
        }
    }
};
