<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar_path')->nullable()->after('receive_marketing_emails');
            $table->text('bio')->nullable()->after('avatar_path');
            $table->string('location', 120)->nullable()->after('bio');
            $table->string('website', 255)->nullable()->after('location');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar_path',
                'bio',
                'location',
                'website',
            ]);
        });
    }
};

