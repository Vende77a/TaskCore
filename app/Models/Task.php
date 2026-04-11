<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    protected $table = 'tasks';

    protected $fillable = ['project_id', 'user_id', 'title','description', 'status', 'priority', 'order'];

    public function project() {
        return $this->belongsTo(Project::class);
    }
}
