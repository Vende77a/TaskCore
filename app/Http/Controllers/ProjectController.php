<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProjectController extends Controller
{

    public function index() {
        $projects =auth()->user()->projects;
        return Inertia::render('Projects/Index', ['projects' => $projects]);
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
            ->with('tasks')
            ->firstOrFail();

        return Inertia::render('Projects/Show', [
            'project' => $project
        ]);
    }

}
