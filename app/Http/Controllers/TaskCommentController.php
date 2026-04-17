<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\Request;
use App\Notifications\TaskCommentedNotification;

class TaskCommentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $task->load('project.members');

        abort_unless($task->project->canCommentTask(auth()->user()), 403);

        $validated = $request->validate([
            'body' => 'required|string',
        ]);

        $task->comments()->create([
            'user_id' => auth()->id(),
            'body' => $validated['body'],
        ]);

        $task->loadMissing('user');

        if ($task->user_id && $task->user_id !== auth()->id() && $task->user) {
            $task->user->notify(
                new TaskCommentedNotification($task, auth()->user()->name)
            );
        }

        return back();
    }

    public function destroy(TaskComment $comment)
    {
        $comment->load('task.project.members');

        $project = $comment->task->project;
        $user = auth()->user();

        $canDelete =
            $project->isAdmin($user) ||
            $comment->user_id === $user->id;

        abort_unless($canDelete, 403);

        $comment->delete();

        return back();
    }
}
