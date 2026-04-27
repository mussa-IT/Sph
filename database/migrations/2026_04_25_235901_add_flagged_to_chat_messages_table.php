<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('chat_messages', 'flagged')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                $table->boolean('flagged')->default(false)->after('message');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('chat_messages', 'flagged')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                $table->dropColumn('flagged');
            });
        }
    }
};
