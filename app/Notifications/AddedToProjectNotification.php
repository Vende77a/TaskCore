<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AddedToProjectNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Project $project,
        public string $role
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'added_to_project',
            'project_id' => $this->project->id,
            'project_title' => $this->project->title,
            'role' => $this->role,
            'message' => "Вас добавили в проект \"{$this->project->title}\" как {$this->role}.",
        ];
    }
}
