<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Internal service costs are now categorized by service charge type (the same list
        // used on "Job Per Cost"), not tied to a specific job cost line — so the dropdown
        // always includes every service charge type, including ones added later, and doesn't
        // require a matching cost line to already exist on the job. job_cost_line_id stays
        // for existing rows recorded before this change.
        Schema::table('job_service_costs', function (Blueprint $table) {
            $table->foreignId('charge_type_id')->nullable()->after('job_cost_line_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('job_service_costs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('charge_type_id');
        });
    }
};
