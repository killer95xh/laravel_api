<?php

namespace App\Http\Controllers\Api;

use App\Events\CommentSubTask;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\SubTask;
use App\Models\SubTaskComment;
use App\Models\CommentFileAttachments;
use App\Models\User;

class SubTaskCommentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api', 'check-employment_state'], ['except' => []]);
    }

    public function getCommentBySubTaskId($subTaskId) {
        $comments = SubTaskComment::leftJoin("users", "sub_task_comment.user_id", "=", "users.id")
            ->select("sub_task_comment.*", "users.position", "users.last_name", "users.url_avatar")
            ->orderBy("created_at", "DESC")
            ->where('sub_task_id', $subTaskId)
            ->get();
        if ($comments) {
            foreach ($comments as $key => $comment) {
                $comments[$key]['file_attachments'] = $comment->fileAttacments()->select('path')->get()->toArray();

                $result = responseApi("200", "Success!", $comments);
            }
        } else {
            $result = responseApi("200", "Success!", []);
        }
        return response()->json($result, 200);
    }
    
    public function createSubTaskComment(Request $request) {
        $dataInsert = $request->all();
        $dataInsert['created_at'] = date('Y-m-d H:i:s');
        $dataInsert['content'] = $dataInsert['content'] ?? "";
        $pattern = "/@'([^']*)'/";
        preg_match_all($pattern, $dataInsert['content'], $matches);
        $listDropString = $matches[1];
        $listUserTag = [];
        foreach ($listDropString as $key => $string) {
            $pattern2 = "/\[(.*)\]/";
            preg_match($pattern2, $string, $matches2);
            $pattern3 = "/](.*)$/";
            preg_match($pattern3, $string, $matches3);
            $listUserTag[] = [
                'position' => trim($matches2[1]),
                'last_name' => trim($matches3[1]),
            ];
        }
        DB::beginTransaction();
        try {
            $subTask = SubTask::find($dataInsert['sub_task_id']);
            $id = SubTaskComment::insertGetId($dataInsert);
            if ($id) {
                if (!empty($request->list_file)) {
                    foreach ($request->list_file as $key => $file) {
                        $extention = $file['file']->getClientOriginalExtension();
                        $filename = uniqid() . rand(0, 9999999) . '.' . $extention;
                        $path = "comment/";
                        $file['file']->move(public_path("assets/images/" . $path), $filename);
                        $fullPath = $path . $filename;
                        $dataInsertFile = [
                            "comment_id" => $id,
                            "path" => $fullPath,
                            "created_at" => date("Y-m-d H:i:s")
                        ];
                        CommentFileAttachments::insert($dataInsertFile);
                    }
                }
            }
            DB::commit();
            broadcast(new CommentSubTask($dataInsert['sub_task_id'], auth()->user()->id));
            $listUserIdTag = [];
            if (count($listUserTag) > 0) {
                $listUserIdTag = User::where('id', '!=', auth()->user()->id)
                    ->where(function ($query1) use ($listUserTag) {
                        foreach ($listUserTag as $key => $userTag) {
                            $query1->orWhere(function ($query2) use ($key, $userTag) {
                                $query2->where('position', $userTag['position'])
                                    ->where('last_name', $userTag['last_name']);
                                });
                        }
                    })
                    ->pluck('id')
                    ->toArray();
            }
            if (count($listUserIdTag) > 0) {
                $typeNotiTag = TYPE_NOTI['TagComment'];
                $paramsTag = toJson([ 
                    auth()->user()->last_name,
                    $subTask->sub_task_name
                ]);
                $urlRedirectTag = toJson([
                    "name" => "TaskDetail",
                    "params" => [
                        "task_id" => $subTask->task_id,
                        "sub_task_id" => $subTask->id,
                    ]
                ]);
                createNoti($typeNotiTag, $paramsTag, $listUserIdTag, $urlRedirectTag);
            }
            $typeNoti = TYPE_NOTI['SubTaskComment'];
            $params = toJson([ 
                auth()->user()->last_name,
                $subTask->sub_task_name
            ]);
            $toListUser = [];
            if ($subTask->leader_user_id != auth()->user()->id && !in_array($subTask->leader_user_id, $listUserIdTag)) {
                $toListUser[] = $subTask->leader_user_id;
            }
            foreach ($subTask->user()->get() as $key => $user) {
                if ($user->id != auth()->user()->id && !in_array($user->id, $listUserIdTag)) {
                    $toListUser[] = $user->id;
                }
            }
            $urlRedirect = toJson([
                "name" => "TaskDetail",
                "params" => [
                    "task_id" => $subTask->task_id,
                    "sub_task_id" => $subTask->id,
                ]
            ]);
            if ($subTask->task()->first()->status != "4") {
                createNoti($typeNoti, $params, $toListUser, $urlRedirect);
            }
            $result = responseApi("200", "Success!");
        } catch (\Exception $e) {
            Log::info($e);
            DB::rollBack();
            $result = responseApi("11", "Failed!");
        }
        
        return response()->json($result, 200);
    }

}
