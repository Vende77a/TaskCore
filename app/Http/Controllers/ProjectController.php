<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $projects = Project::whereHas('members', function ($query) use ($userId) {
            $query->where('users.id', $userId);
        })
            ->with('user:id,name,email')
            ->latest()
            ->get();

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $project = Project::create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
        ]);

        $project->members()->attach(auth()->id(), [
            'role' => 'admin',
        ]);

        return redirect()->route('projects.index');
    }

    public function show($id)
    {
        $project = Project::where('id', $id)
            ->whereHas('members', function ($query) {
                $query->where('users.id', auth()->id());
            })
            ->with([
                'members:id,name,email',
                'tasks' => function ($query) {
                    $query->with('user')->orderBy('order', 'asc');
                }
            ])
            ->firstOrFail();

        $currentUser = $project->members->firstWhere('id', auth()->id());

        return Inertia::render('Projects/Show', [
            'project' => $project,
            'projectMembers' => $project->members
                ->sortBy(function ($member) use ($project) {
                    return $member->id === $project->user_id ? 0 : 1;
                })
                ->map(function ($member) use ($project) {
                    return [
                        'id' => $member->id,
                        'name' => $member->name,
                        'email' => $member->email,
                        'role' => $member->id === $project->user_id ? 'admin' : $member->pivot->role,
                    ];
                })
                ->values(),
            'currentUserRole' => $currentUser?->pivot?->role,
            'currentUserId' => auth()->id(),
        ]);
    }

    public function destroy($id)
    {
        $project = Project::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $project->delete();

        return redirect()->route('projects.index');
    }
}
