<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Internal actual-cost record for a service line — e.g. what was really paid to a
        // subcontractor to fulfill "TRANSPORT CHARGES", as opposed to what the customer was
        // billed for it. Never mirrored onto the invoice; used only to compute real profit.
        Schema::create('job_service_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_cost_line_id')->nullable()->constrained()->nullOnDelete();
            $table->string('paid_to')->nullable();
            $table->string('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_service_costs');
    }
};
