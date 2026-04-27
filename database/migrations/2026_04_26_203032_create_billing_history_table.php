<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('billing_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained()->onDelete('set null');
            $table->string('type'); // payment, refund, credit, adjustment
            $table->string('status'); // pending, completed, failed, refunded
            $table->string('payment_gateway'); // stripe, paypal, mpesa, bank_transfer
            $table->string('gateway_transaction_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('description');
            $table->json('gateway_data')->nullable(); // Gateway-specific response data
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->string('refund_reason')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('receipt_url')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'type']);
            $table->index('gateway_transaction_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_history');
    }
};
