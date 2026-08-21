<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creditor_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creditor_id')->constrained()->cascadeOnDelete();
            $table->date('period');
            $table->date('due_date')->nullable();
            $table->decimal('amount', 15, 2);
            $table->date('paid_on')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creditor_payments');
    }
};
