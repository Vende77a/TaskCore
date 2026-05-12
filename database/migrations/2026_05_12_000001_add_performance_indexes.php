<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_user', function (Blueprint $table) {
            $table->index(['user_id', 'project_id'], 'project_user_user_id_project_id_index');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->index(['project_id', 'status', 'order'], 'tasks_project_status_order_index');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['notifiable_type', 'notifiable_id', 'read_at'], 'notifications_notifiable_read_index');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_notifiable_read_index');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('tasks_project_status_order_index');
        });

        Schema::table('project_user', function (Blueprint $table) {
            $table->dropIndex('project_user_user_id_project_id_index');
        });
    }
};
