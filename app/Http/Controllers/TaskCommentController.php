<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    public function store(Request $request, $taskId)
    {
        $task = Task::where('id', $taskId)
            ->whereHas('project', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->firstOrFail();

        $validated = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        TaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'body' => $validated['body'],
        ]);

        return redirect()->back();
    }

    public function destroy($id)
    {
        $comment = TaskComment::where('id', $id)
            ->whereHas('task.project', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->firstOrFail();

        $comment->delete();

        return redirect()->back();
    }
}
