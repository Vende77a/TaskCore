<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskAttachmentController extends Controller
{
    public function store(Request $request, $taskId)
    {
        $task = Task::where('id', $taskId)
            ->whereHas('project', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->firstOrFail();

        $validated = $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx,txt,zip|max:10240',
        ]);

        $file = $validated['file'];
        $path = $file->store('task-attachments', 'public');

        TaskAttachment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);

        return redirect()->back();
    }

    public function destroy($id)
    {
        $attachment = TaskAttachment::where('id', $id)
            ->whereHas('task.project', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->firstOrFail();

        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();

        return redirect()->back();
    }
}
