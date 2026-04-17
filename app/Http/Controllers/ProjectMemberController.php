<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use App\Notifications\AddedToProjectNotification;

class ProjectMemberController extends Controller
{
    public function store(Request $request, Project $project)
    {
        abort_unless($project->canManageMembers(auth()->user()), 403);

        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|in:member,viewer',
        ]);

        $user = User::where('email', $validated['email'])->firstOrFail();

        if ($user->id === $project->user_id) {
            return back()->withErrors([
                'email' => 'Создатель проекта уже является администратором проекта.',
            ]);
        }

        $alreadyMember = $project->members()
            ->where('users.id', $user->id)
            ->exists();

        if ($alreadyMember) {
            return back()->withErrors([
                'email' => 'Этот пользователь уже добавлен в проект.',
            ]);
        }

        $project->members()->attach($user->id, [
            'role' => $validated['role'],
        ]);

        $user->notify(new AddedToProjectNotification($project, $validated['role']));

        return back();
    }

    public function update(Request $request, Project $project, User $user)
    {
        abort_unless($project->canManageMembers(auth()->user()), 403);

        $member = $project->members()
            ->where('users.id', $user->id)
            ->firstOrFail();

        if ($user->id === $project->user_id) {
            return back()->withErrors([
                'role' => 'Нельзя изменить роль администратора проекта.',
            ]);
        }

        $validated = $request->validate([
            'role' => 'required|in:member,viewer',
        ]);

        $project->members()->updateExistingPivot($user->id, [
            'role' => $validated['role'],
        ]);

        return back();
    }

    public function destroy(Project $project, User $user)
    {
        abort_unless($project->canManageMembers(auth()->user()), 403);

        if ($user->id === $project->user_id) {
            return back()->withErrors([
                'member' => 'Нельзя удалить администратора проекта.',
            ]);
        }

        $member = $project->members()
            ->where('users.id', $user->id)
            ->firstOrFail();

        $project->members()->detach($member->id);

        return back();
    }
}
