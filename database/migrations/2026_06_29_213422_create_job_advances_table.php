<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['advance', 'iou']);
            $table->decimal('amount', 15, 2);
            $table->string('receipt_no')->nullable();
            $table->string('name')->nullable();
            $table->date('received_on');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_advances');
    }
};
