<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A receipt is the money a customer actually handed over in one go. It can
        // fan out across several of that customer's invoices via payments.receipt_id
        // — see the following migration — instead of being tied to a single invoice.
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->enum('method', ['cheque', 'bank', 'cash'])->default('cheque');
            $table->string('reference')->nullable();
            $table->date('received_on');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
