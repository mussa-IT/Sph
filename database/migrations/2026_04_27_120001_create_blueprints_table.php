<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blueprints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type');
            $table->unsignedBigInteger('file_size');
            $table->string('file_hash')->unique()->index(); // SHA256 hash
            $table->string('blockchain_hash')->nullable()->index(); // Onchain hash
            $table->string('transaction_hash')->nullable()->index(); // Transaction hash
            $table->timestamp('blockchain_anchored_at')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blueprints');
    }
};
