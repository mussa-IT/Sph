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
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('referred_user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('referral_code')->unique();
            $table->string('referred_email')->nullable();
            $table->enum('status', ['pending', 'registered', 'converted', 'expired'])->default('pending');
            $table->decimal('reward_amount', 10, 2)->default(0);
            $table->string('reward_type')->default('credit'); // credit, discount, upgrade
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('reward_data')->nullable(); // Additional reward information
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['referrer_id', 'status']);
            $table->index(['referred_user_id', 'status']);
            $table->index('referral_code');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
