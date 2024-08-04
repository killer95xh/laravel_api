<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommentFileAttachments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Task;
use App\Models\SubTask;
use App\Models\SubTaskComment;
use App\Models\SubTaskAssigneesUser;
use App\Models\SubTaskFileAttachments;
use App\Models\User;

class SubTaskController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api', 'check-employment_state'], ['except' => []]);
    }

    public function getSubTaskInfo($subTaskId) {
        $subTask = SubTask::leftJoin("users", "sub_task.leader_user_id", "=", "users.id")
            ->select("sub_task.*", "users.position as leader_position", "users.last_name as leader_last_name", "users.url_avatar")
            ->where("sub_task.id", $subTaskId)
            ->first();
        if ($subTask) {
            $subTask['project_name'] = $subTask->task()->first()->project_name;
            $subTask['task_supervisor_user_id'] = $subTask->task()->first()->task_supervisor_user_id;
            $taskSupervisor = User::find($subTask['task_supervisor_user_id']);
            $subTask['task_supervisor_last_name'] = $taskSupervisor->last_name;
            $subTask['task_supervisor_url_avatar'] = $taskSupervisor->url_avatar;
            $subTask['file_attachments'] = $subTask->fileAttacments()->get();
            $subTask['list_employee'] = $subTask->user()->select('position', 'last_name', 'users.id', "url_avatar")->get()->toArray();
            $comments = SubTaskComment::leftJoin("users", "sub_task_comment.user_id", "=", "users.id")
                ->select("sub_task_comment.*", "users.position", "users.last_name", "users.url_avatar")
                ->orderBy("created_at", "DESC")
                ->where('sub_task_id', $subTaskId)
                ->get();
            foreach ($comments as $key => $comment) {
                $comments[$key]['file_attachments'] = $comment->fileAttacments()->select('path')->get()->toArray();
            }
            $subTask['history'] = $subTask->history()->orderBy("created_at", "DESC")->get();
            $subTask['comments'] = $comments->toArray();
        }
        $result = responseApi("200", "Success!", $subTask);

        return response()->json($result, 200);
    }

    public function getListSubTaskByTaskId($taskId) {
        $listSubTask = SubTask::leftJoin('users', 'sub_task.leader_user_id', '=', 'users.id')
            ->select('sub_task.*', 'users.position as leader_position', 'users.last_name as leader_last_name')
            ->where("sub_task.task_id", $taskId)
            ->orderBy("type", 'DESC')
            ->get();
        $arrListSubTask = $listSubTask->toArray();
        if ($listSubTask) {
            $countSubTask = count($listSubTask);
            foreach ($listSubTask as $key => $subTask) {
                $arrListSubTask[$key]['list_employee'] = $subTask->user()->select('position', 'last_name')->get()->toArray();
                $arrListSubTask[$key]['progress'] = ceil($subTask->progress_target > 0 ? $subTask->progress_completed / $subTask->progress_target * 100 : 0);
                $arrListSubTask[$key]['progress_task'] = round($countSubTask > 0 && $subTask->progress_target > 0 ? $subTask->progress_completed / $subTask->progress_target * 100 * (1 / $countSubTask) : 0);
            }
        }
        $result = responseApi("200", "Success!", $arrListSubTask);

        return response()->json($result, 200);
    }

    public function createSubTask(Request $request) {
        $dataInsert = $request->all();
        $dataInsert['sub_task_name'] = mb_strtoupper($dataInsert['sub_task_name'], 'UTF-8');
        $dataInsert['created_at'] = date('Y-m-d H:i:s');
        $dataInsert['progress_target'] = $dataInsert['progress_target'] ?? 0;
        $dataInsert['priority_level'] = $dataInsert['priority_level'] ?? 1;
        if (empty($dataInsert['deadline_end_date'])) {
            $dataInsert['deadline_end_date'] = date('Y-m-d H:i:s');
        }
        unset($dataInsert['employee_user_id']);
        unset($dataInsert['file_attachments']);
        DB::beginTransaction();
        try {
            $task = Task::find($dataInsert['task_id']);
            $subTaskId = SubTask::insertGetId($dataInsert);

            $typeNoti = TYPE_NOTI['SubTask'];
            $params = toJson([ 
                $dataInsert['sub_task_name'],
                $task->project_name,
            ]);
            $toListUser = [$request->leader_user_id];
            $urlRedirect = toJson([
                "name" => "TaskDetail",
                "params" => [
                    "task_id" => $dataInsert['task_id'],
                    "sub_task_id" => $subTaskId,
                ]
            ]);

            if (isset($request->employee_user_id)) {
                foreach ($request->employee_user_id as $value) {
                    SubTaskAssigneesUser::insert([
                        "user_id" => $value['id'],
                        "sub_task_id" => $subTaskId,
                        "created_at" => date('Y-m-d H:i:s')
                    ]);
                    $toListUser[] = $value['id'];
                }
            }
            if (isset($request->file_attachments)) {
                foreach ($request->file_attachments as $key => $file) {
                    $extention = $file['file']->getClientOriginalExtension();
                    $filename = uniqid() . rand(0, 9999999) . '.' . $extention;
                    $path = "sub_task/";
                    $file['file']->move(public_path("assets/images/" . $path), $filename);
                    $fullPath = $path . $filename;
                    $dataInsertFile = [
                        "sub_task_id" => $subTaskId,
                        "path" => $fullPath,
                        "created_at" => date("Y-m-d H:i:s")
                    ];
                    SubTaskFileAttachments::insert($dataInsertFile);
                }
            }
            createNoti($typeNoti, $params, $toListUser, $urlRedirect);
            DB::commit();
            $result = responseApi("200", "Success!");
        } catch (\Exception $e) {
            Log::info($e);
            DB::rollBack();
            $result = responseApi("11", "Failed!");
        }
        
        return response()->json($result, 200);
    }

    public function createSubTaskAds(Request $request) {
        $subTaskExists = SubTask::where("task_id", $request->task_id)
            ->where("type", "2")
            ->first();
        if (!$subTaskExists) {
            $dataInsert = $request->all();
            $dataInsert['sub_task_name'] = mb_strtoupper($dataInsert['sub_task_name'], 'UTF-8');
            $dataInsert['created_at'] = date('Y-m-d H:i:s');
            $insert = SubTask::insertGetId($dataInsert);
            if ($insert) {
                $result = responseApi("200", "Success!");
            } else {
                $result = responseApi("11", "Failed!");
            }
        } else {
            $result = responseApi("11", "Công việc chạy Ads của dự án đã tồn tại!");
        }
        
        return response()->json($result, 200);
    }
    public function updateSubTask(Request $request) {
        $subTask = SubTask::find($request->id);
        if (isset($request->list_employee)) {
            $textUserName = "";
            $dataInsertHistory = [
                "type" => TYPE_HISTORY['TYPE4'],
                "sub_task_id" => $request->id
            ];
            if($request->list_employee == "delete_all") {
                SubTaskAssigneesUser::where('sub_task_id', $request->id)->delete();
                $result = responseApi("200", "Success!");
            } else {
                $list_employee_id = [];
                foreach ($request->list_employee as $value) {
                    $list_employee_id[] = $value['id'];
                }
                $list_employee_id = array_unique($list_employee_id);
                $subTaskUsers = SubTaskAssigneesUser::where('sub_task_id', $request->id)->select('user_id')->get();
                $subTaskUserIds = [];
                foreach ($subTaskUsers as $value) {
                    $subTaskUserIds[] = $value->user_id;
                }
                DB::beginTransaction();
                try {
                    $typeNoti = TYPE_NOTI['SubTask'];
                    $params = toJson([ 
                        $subTask->sub_task_name,
                        $subTask->task()->first()->project_name,
                    ]);
                    $toListUser = array_values(array_diff($list_employee_id, $subTaskUserIds));
                    $urlRedirect = toJson([
                        "name" => "TaskDetail",
                        "params" => [
                            "task_id" => $subTask->task_id,
                            "sub_task_id" => $subTask->id,
                        ]
                    ]);
                    SubTaskAssigneesUser::where('sub_task_id', $request->id)->delete();
                    foreach ($list_employee_id as $emp) {
                        $dataInsert = [
                            'sub_task_id' => $request->id,
                            'user_id' => $emp,
                        ];
                        SubTaskAssigneesUser::insert($dataInsert);
                    }
                    if ($subTask->task()->first()->status != "4") {
                        createNoti($typeNoti, $params, $toListUser, $urlRedirect);
                    }
                    $listEmployee = User::whereIn('id', $list_employee_id)->get();
                    foreach ($listEmployee as $user) {
                        $textUserName .= $user->last_name . ", ";
                    }
                    $textUserName = rtrim($textUserName, ", ");
                    DB::commit();
                    $result = responseApi("200", "Success!");
                } catch (\Exception $e) {
                    Log::info($e);
                    DB::rollBack();
                    $result = responseApi("11", "Failed!");
                }
            }
            $dataInsertHistory['params'] = toJson([
                auth()->user()->last_name,
                "Người thực hiện",
                $textUserName
            ]);
            createHistory($dataInsertHistory);
        } else if (isset($request->file_attachments)) {
            DB::beginTransaction();
            try {
                foreach ($request->file_attachments as $file) {
                    $extention = $file['file']->getClientOriginalExtension();
                    $filename = uniqid() . rand(0, 9999999) . '.' . $extention;
                    $path = "sub_task/";
                    $file['file']->move(public_path("assets/images/" . $path), $filename);
                    $fullPath = $path . $filename;
                    $dataInsertFile = [
                        "sub_task_id" => $request->id,
                        "path" => $fullPath,
                        "created_at" => date("Y-m-d H:i:s")
                    ];
                    SubTaskFileAttachments::insert($dataInsertFile);
                }
                $dataInsertHistory = [
                    "type" => TYPE_HISTORY['TYPE3'],
                    "sub_task_id" => $request->id,
                    "params" => toJson([
                        auth()->user()->last_name,
                        count($request->file_attachments),
                        "Tệp đính kèm",
                    ])
                ];
                createHistory($dataInsertHistory);
                DB::commit();
                $result = responseApi("200", "Success!");
            } catch (\Exception $e) {
                Log::info($e);
                DB::rollBack();
                $result = responseApi("11", "Failed!");
            }
        } else if (isset($request->status)) {
            if ($subTask->type == "1" && $subTask->status == "1" && in_array($request->status, ["3", "4", "5", "6"])) {
                if ($subTask->task()->first()->status == "3") {
                    $subTask->task()->update(["status" => "2"]);
                }
            } else if ($subTask->type == "2" && $subTask->status == "1" && in_array($request->status, ["3", "4", "5", "6"])) {
                if ($subTask->task()->first()->status == "2" || $subTask->task()->first()->status == "3") {
                    $subTask->task()->update(["status" => "1"]);
                }
            }
            $update = SubTask::where('id', $request->id)->update(["status" => $request->status]);
            if ($update) {
                if (in_array($request->status, ["4", "5", "6"])) {
                    $toListUser = [];
                    if ($request->status == "4") {
                        $toListUser = [$subTask->leader_user_id];
                    } else if ($request->status == "5") {
                        $toListUser = [$subTask->task()->first()->task_supervisor_user_id];
                    } else if ($request->status == "6") {
                        $toListUser = array_column(getListUserAdmin()->toArray(), 'id');
                    }
                    $typeNoti = TYPE_NOTI['UpdateStatusSubTask'];
                    $params = toJson([
                        auth()->user()->first_name . " " . auth()->user()->last_name,
                        $subTask->sub_task_name,
                        $subTask->task()->first()->project_name,
                        SUB_TASK_STATUS[$request->status]
                    ]);
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
                }
                $dataInsertHistory = [
                    "type" => TYPE_HISTORY['TYPE4'],
                    "sub_task_id" => $request->id,
                    "params" => toJson([
                        auth()->user()->last_name,
                        "Trạng thái",
                        SUB_TASK_STATUS[$request->status]
                    ])
                ];
                createHistory($dataInsertHistory);
                $result = responseApi("200", "Success!");
            } else {
                $result = responseApi("11", "Failed!");
            }
        } else {
            $dataUpdate = $request->all();
            if (isset($request->sub_task_name)) {
                $dataUpdate['sub_task_name'] = mb_strtoupper($dataUpdate['sub_task_name'], 'UTF-8');
            }
            unset($dataUpdate['id']);
            $update = SubTask::where('id', $request->id)->update($dataUpdate);
            if ($update) {
                $dataInsertHistory = [
                    "type" => TYPE_HISTORY['TYPE4'],
                    "sub_task_id" => $request->id,
                    "params" => [
                        auth()->user()->last_name,
                        "<Không xác định>",
                        "<Không xác định>",
                    ]
                ];
                if (isset($request->sub_task_name)) {
                    $dataInsertHistory['params'] = toJson([
                        auth()->user()->last_name,
                        "Tên công việc",
                        $request->sub_task_name
                    ]);
                }
                if (isset($request->description)) {
                    $dataInsertHistory['params'] = toJson([
                        auth()->user()->last_name,
                        "Chi tiết",
                        $request->description
                    ]);
                }
                if (isset($request->progress_completed) && isset($request->progress_target)) {
                    $dataInsertHistory['params'] = toJson([
                        auth()->user()->last_name,
                        "Khối lương CV",
                        $request->progress_completed . "/" . $request->progress_target
                    ]);
                }
                if (isset($request->progress_type)) {
                    $dataInsertHistory['params'] = toJson([
                        auth()->user()->last_name,
                        "Tên khối lượng CV",
                        $request->progress_type
                    ]);
                }
                if (isset($request->deadline_end_date)) {
                    $dataInsertHistory['params'] = toJson([
                        auth()->user()->last_name,
                        "Deadline",
                        date('d-m-Y', strtotime($request->deadline_end_date))
                    ]);
                }
                if (isset($request->priority_level)) {
                    $dataInsertHistory['params'] = toJson([
                        auth()->user()->last_name,
                        "Ưu tiên",
                        SUB_TASK_PRIORITY_LEVEL[$request->priority_level]
                    ]);
                }
                if (isset($request->leader_user_id)) {
                    $typeNoti = TYPE_NOTI['SubTask'];
                    $params = toJson([ 
                        $subTask->sub_task_name,
                        $subTask->task()->first()->project_name,
                    ]);
                    $toListUser = [$request->leader_user_id];
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

                    $userName = User::find($request->leader_user_id)->last_name;
                    $dataInsertHistory['params'] = toJson([
                        auth()->user()->last_name,
                        "Trưởng phòng",
                        $userName
                    ]);
                }
                createHistory($dataInsertHistory);
                $result = responseApi("200", "Success!");
            } else {
                $result = responseApi("11", "Failed!");
            }
        }

        return response()->json($result, 200);
    }

    public function getListFileBySubTaskId ($subTaskId) {
        $fileAttachments = SubTaskFileAttachments::where('sub_task_id', $subTaskId)->get();
        $result = responseApi("200", "Success!", $fileAttachments);

        return response()->json($result, 200);
    }

    public function deleteSubTask (Request $request) {
        DB::beginTransaction();
        try {
            $subTask = SubTask::find($request->id);
            $commentIds = $subTask->comment()->pluck('id')->all();
            CommentFileAttachments::whereIn('comment_id', $commentIds)->delete();
            $subTask->history()->delete();
            $subTask->comment()->delete();
            $subTask->fileAttacments()->delete();
            SubTaskAssigneesUser::where('sub_task_id', $request->id)->delete();
            $subTask->delete();
            
            DB::commit();
            $result = responseApi("200", "Success!");
        } catch (\Exception $e) {
            Log::info($e);
            DB::rollBack();
            $result = responseApi("11", "Failed!");
        }

        return response()->json($result, 200);
    }
}
