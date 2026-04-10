<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(){

    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'required|exists:projects,id',
            'priority' => 'required|integer',
        ]);

        $project = Project::where('id', $validated['project_id'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'project_id' => $project->id,
            'status' => 'backlog',
            'priority' => $validated['priority'],
        ]);

        return redirect()->route('projects.show', $project->id);
    }

    public function update(Request $request, $id)
    {
        $task = Task::where('id', $id)
            ->whereHas('project', function($query) {
                $query->where('user_id', auth()->id());
            })->firstOrFail();

        $validated = $request->validate([
            'status' => 'required|in:backlog,in_progress,done'
        ]);

        $task->update([
            'status' => $validated['status']
        ]);

        return redirect()->back();
    }
}
