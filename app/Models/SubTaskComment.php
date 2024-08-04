<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubTaskComment extends Model
{
    use HasFactory;
    protected $table = 'sub_task_comment';

    public function fileAttacments() {
        return $this->hasMany('App\Models\CommentFileAttachments', 'comment_id', 'id');
    }

}
