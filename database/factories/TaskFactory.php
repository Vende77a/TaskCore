<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'user_id' => null,
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'status' => 'backlog',
            'priority' => 1,
            'due_date' => null,
            'order' => 0,
        ];
    }
}
