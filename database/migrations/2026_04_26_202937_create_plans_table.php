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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Free, Pro, Team, Enterprise
            $table->string('slug')->unique(); // free, pro, team, enterprise
            $table->text('description')->nullable();
            $table->decimal('monthly_price', 10, 2)->default(0);
            $table->decimal('yearly_price', 10, 2)->default(0);
            $table->json('features'); // JSON array of features
            $table->json('limits'); // JSON object with plan limits
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_popular')->default(false);
            $table->string('stripe_price_id_monthly')->nullable();
            $table->string('stripe_price_id_yearly')->nullable();
            $table->string('paypal_plan_id')->nullable();
            $table->integer('trial_days')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
