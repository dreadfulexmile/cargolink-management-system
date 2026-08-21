<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_no')->unique();
            $table->enum('mode', ['sea', 'air']);
            $table->enum('direction', ['import', 'export']);
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('salesperson_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('vessel_flight')->nullable();
            $table->date('vessel_date')->nullable();
            $table->string('port_loading')->nullable();
            $table->string('port_discharge')->nullable();
            $table->string('mbl_no')->nullable();
            $table->string('hbl_no')->nullable();
            $table->text('cargo_description')->nullable();
            $table->string('container_no')->nullable();
            $table->string('quantity')->nullable();
            $table->string('cusdec_no')->nullable();
            $table->enum('status', ['open', 'cleared', 'invoiced', 'closed'])->default('open');
            $table->decimal('customer_incentive', 15, 2)->default(0);
            $table->decimal('job_commission', 15, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
