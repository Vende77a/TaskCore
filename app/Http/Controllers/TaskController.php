<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;

class TaskController extends Controller
{
    public function show(Task $task)
    {
        $task->load([
            'project.members',
            'user:id,name,email',
        ]);

        abort_unless($task->project->canViewProject(auth()->user()), 403);

        $task->load([
            'comments.user:id,name,email',
            'attachments.user:id,name,email',
        ]);

        return response()->json([
            'task' => [
                'id' => $task->id,
                'project_id' => $task->project_id,
                'user_id' => $task->user_id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status,
                'priority' => $task->priority,
                'due_date' => $task->due_date,
                'order' => $task->order,
                'created_at' => $task->created_at,
                'updated_at' => $task->updated_at,
                'user' => $task->user ? [
                    'id' => $task->user->id,
                    'name' => $task->user->name,
                    'email' => $task->user->email,
                ] : null,
                'comments' => $task->comments->map(fn ($comment) => [
                    'id' => $comment->id,
                    'task_id' => $comment->task_id,
                    'user_id' => $comment->user_id,
                    'body' => $comment->body,
                    'created_at' => $comment->created_at,
                    'updated_at' => $comment->updated_at,
                    'user' => $comment->user ? [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                        'email' => $comment->user->email,
                    ] : null,
                ])->values(),
                'attachments' => $task->attachments->map(fn ($attachment) => [
                    'id' => $attachment->id,
                    'task_id' => $attachment->task_id,
                    'user_id' => $attachment->user_id,
                    'original_name' => $attachment->original_name,
                    'path' => $attachment->path,
                    'mime_type' => $attachment->mime_type,
                    'size' => $attachment->size,
                    'url' => $attachment->url,
                    'created_at' => $attachment->created_at,
                    'updated_at' => $attachment->updated_at,
                    'user' => $attachment->user ? [
                        'id' => $attachment->user->id,
                        'name' => $attachment->user->name,
                        'email' => $attachment->user->email,
                    ] : null,
                ])->values(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $project = Project::with('members')->findOrFail($validated['project_id']);

        abort_unless($project->canCreateTask(auth()->user()), 403);

        $data = [
            'project_id' => $project->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => 'backlog',
            'priority' => 1,
        ];

        if ($project->isAdmin(auth()->user())) {
            $adminValidated = $request->validate([
                'priority' => 'nullable|integer|in:1,2,3',
                'due_date' => 'nullable|date',
                'user_id' => 'nullable|exists:users,id',
            ]);

            if (!empty($adminValidated['user_id'])) {
                $isProjectMember = $project->members()
                    ->where('users.id', $adminValidated['user_id'])
                    ->exists();

                if (!$isProjectMember) {
                    return back()->withErrors([
                        'user_id' => 'Исполнитель должен быть участником проекта.',
                    ]);
                }
            }

            $data['priority'] = $adminValidated['priority'] ?? 1;
            $data['due_date'] = $adminValidated['due_date'] ?? null;
            $data['user_id'] = $adminValidated['user_id'] ?? null;
        }

        $task = Task::create($data);

        if (!empty($task->user_id)) {
            $assignee = User::find($task->user_id);

            if ($assignee) {
                $assignee->notify(new TaskAssignedNotification($task));
            }
        }

        return back();

    }

    public function update(Request $request, $id)
    {
        $task = Task::with('project.members')
            ->findOrFail($id);

        $project = $task->project;
        $user = auth()->user();

        abort_unless($project->canViewProject($user), 403);

        if ($project->isAdmin($user)) {
            $validated = $request->validate([
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'status' => 'nullable|in:backlog,in_progress,review,done',
                'priority' => 'nullable|integer|in:1,2,3',
                'due_date' => 'nullable|date',
                'user_id' => 'nullable|exists:users,id',
            ]);

            if (!empty($validated['user_id'])) {
                $isProjectMember = $project->members()
                    ->where('users.id', $validated['user_id'])
                    ->exists();

                if (!$isProjectMember) {
                    return back()->withErrors([
                        'user_id' => 'Исполнитель должен быть участником проекта.',
                    ]);
                }
            }

            $oldUserId = $task->user_id;
            $task->update($validated);

            if (!empty($validated['user_id']) && (int)$validated['user_id'] !== (int)$oldUserId) {
                $assignee = User::find($validated['user_id']);

                if ($assignee) {
                    $assignee->notify(new TaskAssignedNotification($task->fresh()));
                }
            }

            return back();
        }

        if ($project->isMember($user)) {
            $validated = $request->validate([
                'description' => 'nullable|string',
                'status' => 'nullable|in:backlog,in_progress,review,done',
            ]);

            $task->update($validated);

            return back();
        }

        abort(403);
    }

    public function destroy($id)
    {
        $task = Task::with('project.members')->findOrFail($id);

        abort_unless($task->project->canDeleteTask(auth()->user()), 403);

        $task->delete();

        return back();
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:tasks,id',
            'tasks.*.order' => 'required|integer|min:0',
            'tasks.*.status' => 'required|in:backlog,in_progress,review,done',
        ]);

        $taskIds = collect($validated['tasks'])->pluck('id')->unique();

        $tasks = Task::with('project.members')
            ->whereIn('id', $taskIds)
            ->get();

        if ($tasks->isEmpty()) {
            return back();
        }

        abort_unless($tasks->pluck('project_id')->unique()->count() === 1, 403);

        $project = $tasks->first()->project;

        abort_unless($project->canMoveTask(auth()->user()), 403);

        DB::transaction(function () use ($tasks, $validated) {
            foreach ($validated['tasks'] as $item) {
                $task = $tasks->firstWhere('id', $item['id']);

                if ($task) {
                    $task->update([
                        'order' => $item['order'],
                        'status' => $item['status'],
                    ]);
                }
            }
        });

        return back();
    }
}
