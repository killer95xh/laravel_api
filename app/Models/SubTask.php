<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubTask extends Model
{
    use HasFactory;
    protected $table = 'sub_task';

    public function user()
    {
        return $this->belongsToMany(User::class, 'sub_task_assignees_user', 'sub_task_id', 'user_id');

    }

    public function fileAttacments() {
        return $this->hasMany(SubTaskFileAttachments::class, 'sub_task_id', 'id');
    }

    public function history() {
        return $this->hasMany(History::class, 'sub_task_id', 'id');
    }

    public function comment() {
        return $this->hasMany(SubTaskComment::class, 'sub_task_id', 'id');
    }

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id', 'id');
    }
}
