<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('preferred_locale', 5)->default('en')->after('remember_token');
            $table->string('timezone', 64)->default('UTC')->after('preferred_locale');
            $table->boolean('receive_product_updates')->default(true)->after('timezone');
            $table->boolean('receive_marketing_emails')->default(false)->after('receive_product_updates');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'preferred_locale',
                'timezone',
                'receive_product_updates',
                'receive_marketing_emails',
            ]);
        });
    }
};

