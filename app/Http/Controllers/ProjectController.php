<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProjectController extends Controller
{

    public function index() {

        $projects =auth()->user()->projects;

        return Inertia::render('Projects/Index', [
            'projects' => $projects
        ]);
    }

    public function store(Request $request){
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Project::create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('projects.index');
    }

    public function show($id)
    {
        $project = Project::where('id', $id)
            ->where('user_id', auth()->id())
            ->with([
                'tasks' => function ($query) {
                    $query->with([
                        'user',
                        'comments.user',
                        'attachments.user',
                    ])->orderBy('order', 'asc');
                }
            ])
            ->firstOrFail();

        return Inertia::render('Projects/Show', [
            'project' => $project,
            'users' => User::select('id', 'name', 'email')
                ->orderBy('name')
                ->get(),
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
