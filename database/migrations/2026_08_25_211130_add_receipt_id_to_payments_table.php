<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nullable: a payment recorded directly against one invoice (today's normal
        // flow, e.g. from the invoice's own page) still works with no receipt at all.
        // It's only set when the payment was carved out of a customer-level receipt
        // that got split across more than one invoice.
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('receipt_id')->nullable()->after('invoice_id')
                ->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('receipt_id');
        });
    }
};
