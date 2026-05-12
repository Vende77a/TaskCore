<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskAttachmentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $task->load('project.members');

        abort_unless($task->project->canAttachFiles(auth()->user()), 403);

        $validated = $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $file = $validated['file'];
        $path = $file->store('attachments', 'public');

        $task->attachments()->create([
            'user_id' => auth()->id(),
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);

        return back();
    }

    public function destroy(TaskAttachment $attachment)
    {
        $attachment->load('task.project.members');

        $project = $attachment->task->project;
        $user = auth()->user();

        $canDelete =
            $project->isAdmin($user) ||
            $attachment->user_id === $user->id;

        abort_unless($canDelete, 403);

        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();

        return back();
    }
}
