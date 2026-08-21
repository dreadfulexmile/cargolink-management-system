<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lorry_hires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lorry_id')->constrained()->cascadeOnDelete();
            $table->date('hire_date');
            $table->string('hirer_name')->nullable();
            $table->decimal('amount', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lorry_hires');
    }
};
