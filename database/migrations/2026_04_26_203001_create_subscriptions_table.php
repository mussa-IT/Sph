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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('active'); // active, cancelled, expired, trial
            $table->string('billing_cycle'); // monthly, yearly
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('payment_gateway'); // stripe, paypal, mpesa, bank_transfer
            $table->string('gateway_subscription_id')->nullable();
            $table->string('gateway_customer_id')->nullable();
            $table->json('gateway_data')->nullable(); // Additional gateway-specific data
            $table->boolean('auto_renew')->default(true);
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index('gateway_subscription_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
