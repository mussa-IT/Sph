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
        Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('icon')->nullable();
            $table->string('category'); // 'communication', 'productivity', 'development', 'analytics', etc.
            $table->json('configuration_schema')->nullable(); // JSON schema for integration settings
            $table->json('default_settings')->nullable(); // Default configuration values
            $table->boolean('is_active')->default(true);
            $table->boolean('is_beta')->default(false);
            $table->boolean('requires_oauth')->default(false);
            $table->string('oauth_provider')->nullable(); // 'github', 'google', 'slack', etc.
            $table->json('oauth_scopes')->nullable();
            $table->string('webhook_url')->nullable();
            $table->json('supported_events')->nullable(); // Events this integration can trigger
            $table->json('supported_actions')->nullable(); // Actions this integration can perform
            $table->integer('usage_count')->default(0);
            $table->timestamps();
            
            $table->index(['is_active', 'category']);
            $table->index('category');
        });

        Schema::create('user_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('integration_id')->constrained()->onDelete('cascade');
            $table->foreignId('team_id')->nullable()->constrained()->onDelete('cascade');
            $table->json('settings')->nullable(); // User-specific integration settings
            $table->json('credentials')->nullable(); // Encrypted credentials
            $table->boolean('is_enabled')->default(true);
            $table->string('status')->default('disconnected'); // 'disconnected', 'connected', 'error'
            $table->text('error_message')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamp('expires_at')->nullable(); // OAuth token expiration
            $table->timestamps();
            
            $table->unique(['user_id', 'integration_id', 'team_id']);
            $table->index(['user_id', 'is_enabled']);
            $table->index(['team_id', 'is_enabled']);
        });

        Schema::create('integration_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_integration_id')->constrained()->onDelete('cascade');
            $table->string('event_type'); // 'sync', 'webhook', 'action', etc.
            $table->string('action')->nullable();
            $table->json('payload')->nullable();
            $table->json('response')->nullable();
            $table->string('status')->default('pending'); // 'pending', 'success', 'error'
            $table->text('error_message')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->timestamps();
            
            $table->index(['user_integration_id', 'event_type']);
            $table->index('status');
            $table->index('created_at');
        });

        Schema::create('integration_webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_integration_id')->constrained()->onDelete('cascade');
            $table->string('event'); // 'project.created', 'task.completed', etc.
            $table->string('url');
            $table->string('secret')->nullable();
            $table->json('headers')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('delivery_count')->default(0);
            $table->integer('failure_count')->default(0);
            $table->timestamp('last_delivered_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_integration_id', 'event']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integrations');
    }
};
