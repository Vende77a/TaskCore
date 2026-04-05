<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        $task = Task::find(2);
        dump($task->status);
        dump($task->title);
        dump($task->description);
    }
}
