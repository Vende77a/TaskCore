<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'required|exists:projects,id',
            'priority' => 'required|integer|min:1|max:3',
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
            ->whereHas('project', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->firstOrFail();

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'status' => 'sometimes|required|in:backlog,in_progress,review,done',
            'priority' => 'sometimes|required|integer|min:1|max:3',
            'due_date' => 'sometimes|nullable|date',
            'user_id' => 'sometimes|nullable|exists:users,id',
        ]);

        $data = [];

        if ($request->exists('title')) {
            $data['title'] = $validated['title'];
        }

        if ($request->exists('description')) {
            $data['description'] = $validated['description'] ?? null;
        }

        if ($request->exists('status')) {
            $data['status'] = $validated['status'];
        }

        if ($request->exists('priority')) {
            $data['priority'] = $validated['priority'];
        }

        if ($request->exists('due_date')) {
            $data['due_date'] = $validated['due_date'] ?? null;
        }

        if ($request->exists('user_id')) {
            $data['user_id'] = $validated['user_id'] ?: null;
        }

        $task->update($data);

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
        $validated = $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|integer|exists:tasks,id',
            'tasks.*.order' => 'required|integer|min:0',
            'tasks.*.status' => 'required|in:backlog,in_progress,review,done',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['tasks'] as $taskData) {
                Task::where('id', $taskData['id'])
                    ->whereHas('project', function ($query) {
                        $query->where('user_id', auth()->id());
                    })
                    ->update([
                        'order' => $taskData['order'],
                        'status' => $taskData['status'],
                    ]);
            }
        });

        return back();
    }
}
