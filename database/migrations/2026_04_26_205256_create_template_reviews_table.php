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
        Schema::create('template_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('rating'); // 1-5 stars
            $table->text('comment')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_helpful')->default(false);
            $table->integer('helpful_count')->default(0);
            $table->timestamps();
            
            $table->unique(['template_id', 'user_id']);
            $table->index(['template_id', 'rating']);
            $table->index('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_reviews');
    }
};
