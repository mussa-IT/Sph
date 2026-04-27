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
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->longText('content');
            $table->json('metadata')->nullable(); // Template metadata, settings, etc.
            $table->string('thumbnail_url')->nullable();
            $table->string('preview_url')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'archived'])->default('draft');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->json('tags')->nullable();
            $table->integer('downloads')->default(0);
            $table->integer('purchases')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('reviews_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('featured_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'is_featured']);
            $table->index(['seller_id', 'status']);
            $table->index(['category_id', 'status']);
            $table->index('price');
            $table->index('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
