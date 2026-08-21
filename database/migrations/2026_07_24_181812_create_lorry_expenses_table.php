<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lorry_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lorry_id')->constrained()->cascadeOnDelete();
            $table->string('category'); // diesel, repair, maintenance, other
            $table->date('expense_date');
            $table->decimal('amount', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lorry_expenses');
    }
};
