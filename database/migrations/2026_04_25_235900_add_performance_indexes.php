<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'projects_user_status_idx');
            $table->index(['user_id', 'created_at'], 'projects_user_created_idx');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'tasks_user_status_idx');
            $table->index(['project_id', 'status'], 'tasks_project_status_idx');
        });

        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->index(['user_id', 'updated_at'], 'chat_sessions_user_updated_idx');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'read', 'created_at'], 'notifications_user_read_created_idx');
            $table->index(['user_id', 'type', 'created_at'], 'notifications_user_type_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex('projects_user_status_idx');
            $table->dropIndex('projects_user_created_idx');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('tasks_user_status_idx');
            $table->dropIndex('tasks_project_status_idx');
        });

        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->dropIndex('chat_sessions_user_updated_idx');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_user_read_created_idx');
            $table->dropIndex('notifications_user_type_created_idx');
        });
    }
};
