<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    protected $table = 'projects';

    protected $fillable = ['user_id', 'title','description'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function roleFor($user): ?string
    {
        if (!$user) {
            return null;
        }

        if ($this->relationLoaded('members')) {
            $member = $this->members->firstWhere('id', $user->id);
            return $member?->pivot?->role;
        }

        $member = $this->members()
            ->where('users.id', $user->id)
            ->first();

        return $member?->pivot?->role;
    }

    public function isAdmin($user): bool
    {
        return $this->roleFor($user) === 'admin';
    }

    public function isMember($user): bool
    {
        return $this->roleFor($user) === 'member';
    }

    public function isViewer($user): bool
    {
        return $this->roleFor($user) === 'viewer';
    }

    public function canViewProject($user): bool
    {
        return in_array($this->roleFor($user), ['admin', 'member', 'viewer'], true);
    }

    public function canManageProject($user): bool
    {
        return $this->isAdmin($user);
    }

    public function canManageMembers($user): bool
    {
        return $this->isAdmin($user);
    }

    public function canCreateTask($user): bool
    {
        return in_array($this->roleFor($user), ['admin', 'member'], true);
    }

    public function canMoveTask($user): bool
    {
        return in_array($this->roleFor($user), ['admin', 'member'], true);
    }

    public function canEditTaskDescription($user): bool
    {
        return in_array($this->roleFor($user), ['admin', 'member'], true);
    }

    public function canChangeTaskMeta($user): bool
    {
        return $this->isAdmin($user);
    }

    public function canDeleteTask($user): bool
    {
        return $this->isAdmin($user);
    }

    public function canCommentTask($user): bool
    {
        return in_array($this->roleFor($user), ['admin', 'member'], true);
    }

    public function canAttachFiles($user): bool
    {
        return in_array($this->roleFor($user), ['admin', 'member'], true);
    }

}
