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
        Schema::create('template_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained()->onDelete('cascade');
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('payment_gateway');
            $table->string('transaction_id')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->decimal('platform_fee', 10, 2)->default(0);
            $table->decimal('seller_earnings', 10, 2)->default(0);
            $table->timestamp('refunded_at')->nullable();
            $table->text('refund_reason')->nullable();
            $table->timestamps();
            
            $table->index(['template_id', 'buyer_id']);
            $table->index(['seller_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_purchases');
    }
};
