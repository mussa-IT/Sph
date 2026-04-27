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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('favicon_url')->nullable();
            $table->string('primary_color', 7)->default('#3B82F6');
            $table->string('secondary_color', 7)->default('#10B981');
            $table->string('accent_color', 7)->default('#F59E0B');
            $table->string('custom_domain')->nullable();
            $table->boolean('custom_domain_active')->default(false);
            $table->json('custom_css')->nullable();
            $table->json('custom_js')->nullable();
            $table->string('custom_footer_text')->nullable();
            $table->string('custom_header_text')->nullable();
            $table->boolean('remove_branding')->default(false);
            $table->boolean('enable_white_label')->default(false);
            $table->string('plan')->default('free'); // 'free', 'professional', 'enterprise'
            $table->json('plan_limits')->nullable();
            $table->json('settings')->nullable();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamp('plan_expires_at')->nullable();
            $table->timestamps();
            
            $table->index(['is_active', 'plan']);
            $table->index(['owner_id', 'is_active']);
            $table->index('custom_domain');
        });

        Schema::create('organization_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['owner', 'admin', 'member', 'viewer'])->default('member');
            $table->json('permissions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('joined_at')->default(now());
            $table->timestamps();
            
            $table->unique(['organization_id', 'user_id']);
            $table->index(['organization_id', 'is_active']);
            $table->index(['user_id', 'is_active']);
        });

        Schema::create('organization_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('invited_by')->constrained('users')->onDelete('cascade');
            $table->string('email');
            $table->enum('role', ['admin', 'member', 'viewer'])->default('member');
            $table->string('token')->unique();
            $table->text('message')->nullable();
            $table->timestamp('expires_at')->default(now()->addDays(7));
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamps();
            
            $table->index(['organization_id', 'email']);
            $table->index('token');
        });

        Schema::create('organization_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('plan');
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('billing_cycle')->default('monthly');
            $table->enum('status', ['active', 'cancelled', 'expired', 'trial'])->default('active');
            $table->string('payment_gateway');
            $table->string('transaction_id')->nullable();
            $table->timestamp('starts_at')->default(now());
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->boolean('auto_renew')->default(true);
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            
            $table->index(['organization_id', 'status']);
            $table->index('status');
        });

        Schema::create('organization_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // 'string', 'boolean', 'integer', 'json'
            $table->timestamps();
            
            $table->unique(['organization_id', 'key']);
            $table->index(['organization_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
