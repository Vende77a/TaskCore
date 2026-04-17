<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('project_user', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('role')->default('viewer');

            $table->timestamps();

            $table->unique(['project_id', 'user_id']);
        });

        $projects = DB::table('projects')
            ->select('id', 'user_id')
            ->get();

        if ($projects->isNotEmpty()) {
            $now = now();

            DB::table('project_user')->insert(
                $projects->map(function ($project) use ($now) {
                    return [
                        'project_id' => $project->id,
                        'user_id' => $project->user_id,
                        'role' => 'admin',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                })->all()
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_user');
    }
};
