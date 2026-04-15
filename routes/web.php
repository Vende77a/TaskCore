<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\TaskAttachmentController;
use App\Models\Project;
use App\Models\Task;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::get('/dashboard', function () {
    $userId = auth()->id();

    $taskQuery = Task::whereHas('project', function ($query) use ($userId) {
        $query->where('user_id', $userId);
    });

    $stats = [
        'projects' => Project::where('user_id', $userId)->count(),
        'tasks' => (clone $taskQuery)->count(),
        'in_progress' => (clone $taskQuery)->where('status', 'in_progress')->count(),
        'review' => (clone $taskQuery)->where('status', 'review')->count(),
        'done' => (clone $taskQuery)->where('status', 'done')->count(),
    ];

    $recentProjects = Project::where('user_id', $userId)
        ->latest()
        ->take(5)
        ->get(['id', 'title', 'description', 'created_at']);

    $recentTasks = Task::whereHas('project', function ($query) use ($userId) {
        $query->where('user_id', $userId);
    })
        ->with('project:id,title')
        ->latest()
        ->take(6)
        ->get([
            'id',
            'project_id',
            'title',
            'status',
            'priority',
            'due_date',
            'created_at',
        ]);

    return Inertia::render('Dashboard', [
        'stats' => $stats,
        'recentProjects' => $recentProjects,
        'recentTasks' => $recentTasks,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::post('/tasks/reorder', [TaskController::class, 'reorder'])->name('tasks.reorder');
});

Route::middleware('auth')->group(function () {
    Route::resource('/projects', ProjectController::class)
        ->only(['index', 'store', 'show', 'destroy']);
});

Route::post('/tasks', [TaskController::class, 'store'])
    ->name('tasks.store')
    ->middleware('auth');

Route::patch('/tasks/{id}', [TaskController::class, 'update'])
    ->name('tasks.update')
    ->middleware('auth');

Route::delete('/tasks/{id}', [TaskController::class, 'destroy'])
    ->name('tasks.destroy')
    ->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::post('/tasks/{task}/comments', [TaskCommentController::class, 'store'])
        ->name('tasks.comments.store');

    Route::delete('/comments/{id}', [TaskCommentController::class, 'destroy'])
        ->name('comments.destroy');

    Route::post('/tasks/{task}/attachments', [TaskAttachmentController::class, 'store'])
        ->name('tasks.attachments.store');

    Route::delete('/attachments/{id}', [TaskAttachmentController::class, 'destroy'])
        ->name('attachments.destroy');
});


require __DIR__.'/auth.php';
