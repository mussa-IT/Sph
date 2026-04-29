<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('project_hash')->nullable()->after('description')->index();
            $table->string('wallet_address')->nullable()->after('project_hash')->index();
            $table->string('transaction_hash')->nullable()->after('wallet_address')->index();
            $table->timestamp('blockchain_verified_at')->nullable()->after('transaction_hash');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['project_hash', 'wallet_address', 'transaction_hash', 'blockchain_verified_at']);
        });
    }
};
