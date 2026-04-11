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
            'status' => 'nullable|in:backlog,in_progress,review,done',
            'title' => 'nullable|string|max:255',
        ]);

        $task->update(array_filter([
            'status' => $validated['status'] ?? null,
            'title' => $validated['title'] ?? null,
        ]));

        return redirect()->back();
    }

    public function destroy($id)
    {
        $task = Task::where('id', $id)
            ->whereHas('project', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->firstOrFail();

        $task->delete();

        return redirect()->back();
    }

    public function reorder(Request $request)
    {
        // Это остановит выполнение и выведет данные в окно браузера/консоль
        // Если при перетаскивании ты увидишь окно с данными — значит фронт работает.
        //dd($request->all());

        $data = $request->validate([
            'tasks' => 'required|array',
            'tasks.*' => 'numeric', // упростим пока проверку
        ]);

        foreach ($data['tasks'] as $index => $taskId) {
            // Используем find, чтобы точно убедиться в обновлении
            $task = Task::find($taskId);
            if ($task) {
                $task->order = $index;
                $task->save();
            }
        }

        return redirect()->back();
    }
}
