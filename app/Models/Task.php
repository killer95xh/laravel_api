<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    protected $fillable = ['status'];
    public function subTask() {
        return $this->hasMany(SubTask::class, 'task_id', 'id');
    }
}
