<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommentFileAttachments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Task;
use App\Models\SubTask;
use App\Models\SubTaskAssigneesUser;
use App\Models\User;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api', 'check-employment_state'], ['except' => []]);
    }

    public function getTaskInfo($taskId) {
        $task = Task::leftJoin("users", "tasks.task_supervisor_user_id", "=", "users.id")
            ->leftJoin("sub_task", "tasks.assigned_sub_task_id", "=", "sub_task.id")
            ->leftJoin("customers", "tasks.customer_id", "=", "customers.id")
            ->leftJoin("customer_budget", "customers.id", "=", "customer_budget.id")
            ->select("tasks.*", "tasks.id as task_id", "sub_task.sub_task_name", "customers.*", "customer_budget.ads_user_id", "customer_budget.sale_user_id", "users.position as task_supervisor_position", "users.last_name as task_supervisor_last_name", "users.url_avatar as task_supervisor_url_avatar")
            ->where('tasks.id', $taskId)
            ->first();
        if ($task) {
            $task = $task->toArray();
            $subTasks = SubTask::where('task_id', $taskId)->where('type', '1')->get();
            $progress = 0;
            $countSubTask = count($subTasks);
            if ($countSubTask > 0) {
                foreach ($subTasks as $subTask) {
                    $progressSubTask = $subTask->progress_target > 0 ? $subTask->progress_completed / $subTask->progress_target * 100 * (1 / $countSubTask) : 0;
                    $progress += $progressSubTask;
                }
            }
            $task['progress'] = ceil($progress);
        }
        $result = responseApi("200", "Success!", $task);
        return response()->json($result, 200);
    }

    public function getAllTask() {
        $userId = auth()->user()->id;
        if (auth()->user()->is_admin == "1") {
            $allTask = Task::leftJoin('users', 'tasks.task_supervisor_user_id', '=', 'users.id')
                ->leftJoin('customers', 'tasks.customer_id', '=', 'customers.id')
                ->select('tasks.*', 'customers.customer_name', 'users.position as task_supervisor_position', 'users.first_name as task_supervisor_first_name', 'users.last_name as task_supervisor_last_name', 'users.url_avatar as task_supervisor_url_avatar')
                ->orderBy('tasks.contract_end_date', 'DESC')
                ->get();
        } else {
            $allTask = Task::leftJoin('sub_task', 'tasks.id', '=', 'sub_task.task_id')
                ->leftJoin('sub_task_assignees_user as a', 'sub_task.id', '=', 'a.sub_task_id')
                ->where("tasks.task_supervisor_user_id", $userId)
                ->orWhere("sub_task.leader_user_id", $userId)
                ->orWhere("a.user_id", $userId)
                ->leftJoin('users', 'tasks.task_supervisor_user_id', '=', 'users.id')
                ->leftJoin('customers', 'tasks.customer_id', '=', 'customers.id')
                ->select('tasks.*', 'customers.customer_name', 'users.position as task_supervisor_position', 'users.first_name as task_supervisor_first_name', 'users.last_name as task_supervisor_last_name', 'users.url_avatar as task_supervisor_url_avatar')
                ->orderBy('tasks.contract_end_date', 'DESC')
                ->groupBy('tasks.id', 'customers.customer_name', 'users.position', 'users.first_name', 'users.last_name', 'users.url_avatar')
                ->get();
        }
        foreach ($allTask as $key => $task) {
            $subTasks = $task->subTask()->where('type', '1')->get();
            $progress = 0;
            $countSubTask = count($subTasks);
            if ($countSubTask > 0) {
                foreach ($subTasks as $subTask) {
                    $progressSubTask = $subTask->progress_target > 0 ? $subTask->progress_completed / $subTask->progress_target * 100 * (1 / $countSubTask) : 0;
                    $progress += $progressSubTask;
                }
            }
            $allTask[$key]['progress'] = ceil($progress);
        }
        $result = responseApi("200", "Success!", $allTask);

        return response()->json($result, 200);
    }

    public function createTask(Request $request) {
        $dataInsert = $request->all();
        $dataInsert['created_at'] = date('Y-m-d H:i:s');
        $dataInsert['project_name'] = mb_strtoupper($dataInsert['project_name'], 'UTF-8');
        unset($dataInsert['sub_task_name']);
        unset($dataInsert['leader_user_id']);
        unset($dataInsert['employee_user_id']);
        unset($dataInsert['check_create_sub_task']);
        DB::beginTransaction();
        try {
            $taskId = Task::insertGetId($dataInsert);
            $typeNoti = TYPE_NOTI['TaskDetail'];
            $params = toJson([ 
                $dataInsert['project_name']
            ]);
            $toListUser = [$dataInsert['task_supervisor_user_id']];
            $urlRedirect = toJson([
                "name" => "TaskDetail",
                "params" => [
                    "task_id" => $taskId,
                ]
            ]);
            if ($request->check_create_sub_task) {
                $dataInsertSubTask = [
                    "sub_task_name" => mb_strtoupper($request->sub_task_name, 'UTF-8'),
                    "leader_user_id" => $request->leader_user_id
                ];
                $dataInsertSubTask['task_id'] = $taskId;
                $dataInsertSubTask['created_at'] = date('Y-m-d H:i:s');
                $subTaskId = SubTask::insertGetId($dataInsertSubTask);


                $typeNotiSubTask = TYPE_NOTI['SubTask'];
                $paramsSubTask = toJson([
                    $dataInsertSubTask['sub_task_name'],
                    $dataInsert['project_name']
                ]);
                $toListUserSubTask = [$request->leader_user_id];
                $urlRedirectSubTask = toJson([
                    "name" => "TaskDetail",
                    "params" => [
                        "task_id" => $taskId,
                        "sub_task_id" => $subTaskId,
                    ]
                ]);
                
                if (!empty($request->employee_user_id)) {
                    foreach ($request->employee_user_id as $value) {
                        SubTaskAssigneesUser::insert([
                            "user_id" => $value['id'],
                            "sub_task_id" => $subTaskId,
                            "created_at" => date('Y-m-d H:i:s')
                        ]);
                        $toListUserSubTask[] = $value['id'];
                    }
                }
                createNoti($typeNotiSubTask, $paramsSubTask, $toListUserSubTask, $urlRedirectSubTask);
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

    public function updateTask(Request $request) {
        $dataUpdate = $request->all();
        $task = Task::where('id', $request->id)->first();
        if (!empty($request->project_name)) {
            $dataUpdate['project_name'] = mb_strtoupper($dataUpdate['project_name'], 'UTF-8');
        }
        if (!empty($request->status)) {
            $dataUpdate['status_before_update'] = $task->status;
        }
        if (!empty($request->assigned_sub_task_id_to) && !empty($request->assigned_sub_task_id_from)) {
            $dataUpdate['assigned_sub_task_id'] = $request->assigned_sub_task_id_from;
            unset($dataUpdate['assigned_sub_task_id_to']);
            unset($dataUpdate['assigned_sub_task_id_from']);
        }
        unset($dataUpdate['id']);
        $update = Task::where('id', $request->id)->update($dataUpdate);
        if ($update) {
            if (!empty($request->task_supervisor_user_id)) {
                $typeNoti = TYPE_NOTI['TaskDetailll'];
                $params = toJson([ 
                    $task->project_name
                ]);
                $toListUser = [$dataUpdate['task_supervisor_user_id']];
                $urlRedirect = toJson([
                    "name" => "TaskDetail",
                    "params" => [
                        "task_id" => $request->id,
                    ]
                ]);
                if ($task->status != "4") {
                    createNoti($typeNoti, $params, $toListUser, $urlRedirect);
                }
            }
            if (!empty($request->assigned_sub_task_id_to) && !empty($request->assigned_sub_task_id_from)) {
                // data create noti
                $typeNoti = TYPE_NOTI['UpdateAssignedSubTask'];
                $subTaskAssignedOld = $task->subTask()->where('id', $request->assigned_sub_task_id_to)->first();
                $subTaskAssignedNew = $task->subTask()->where('id', $request->assigned_sub_task_id_from)->first();
                $params = toJson([ 
                    $subTaskAssignedOld->sub_task_name
                ]);
                $toListUser = [$subTaskAssignedNew->leader_user_id];
                $urlRedirect = toJson([ 
                    "name" => "TaskDetail",
                    "params" => [
                        "task_id" => $request->id,
                        "sub_task_id" => $subTaskAssignedOld->id
                    ]
                ]);
                // data create history
                $dataInsert = [
                    "type" => TYPE_HISTORY['TYPE1'],
                    "sub_task_id" => $request->assigned_sub_task_id_to,
                    "params" => toJson([
                        auth()->user()->last_name,
                        $subTaskAssignedNew->sub_task_name
                    ])
                ];
                createHistory($dataInsert);
                if ($task->status != "4") {
                    createNoti($typeNoti, $params, $toListUser, $urlRedirect);
                }
            }
            $result = responseApi("200", "Success!");
        } else {
            $result = responseApi("11", "Failed!");
        }

        return response()->json($result, 200);
    }

    public function deleteTask(Request $request) {
        $task = Task::find($request->id);
        DB::beginTransaction();
        try {
            foreach ($task->subTask()->get() as $subTaskFor) {
                $subTask = SubTask::find($subTaskFor->id);
                $commentIds = $subTask->comment()->pluck('id')->all();
                CommentFileAttachments::whereIn('comment_id', $commentIds)->delete();
                $subTask->history()->delete();
                $subTask->comment()->delete();
                $subTask->fileAttacments()->delete();
                SubTaskAssigneesUser::where('sub_task_id', $subTaskFor->id)->delete();
                $subTask->delete();
            }
            $task->delete();
            DB::commit();
            $result = responseApi("200", "Success!");
        } catch (\Exception $e) {
            Log::info($e);
            DB::rollBack();
            $result = responseApi("11", "Failed!");
        }

        return response()->json($result, 200);
    }

    public function duplicateTask(Request $request) {
        $oldTask = Task::find($request->id);
        DB::beginTransaction();
        try {
            $dataInsertNewTask = [
                "customer_id" => $oldTask->customer_id,
                "project_name" => $oldTask->project_name,
                "task_supervisor_user_id" => $oldTask->task_supervisor_user_id,
                "priority_level" => $oldTask->priority_level,
                "contract_start_date" => date('Y-m-d'),
                "contract_end_date" => date('Y-m-d', strtotime('+1 month', strtotime(date('Y-m-d')))),
                "created_at" => date('Y-m-d H:i:s'),
            ];
            $idNewTask = Task::insertGetId($dataInsertNewTask);
            foreach ($oldTask->subTask()->get() as $subTaskOld) {
                $dataInsertNewSubTask = [
                    "task_id" => $idNewTask,
                    "description" => $subTaskOld->description,
                    "sub_task_name" => $subTaskOld->sub_task_name,
                    "leader_user_id" => $subTaskOld->leader_user_id,
                    "type" => $subTaskOld->type,
                    "created_at" => date('Y-m-d H:i:s'),
                ];
                $idNewSubTask = SubTask::insertGetId($dataInsertNewSubTask);
                $subTaskAssigneesUsers = SubTaskAssigneesUser::where('sub_task_id', $subTaskOld->id)->get();
                foreach ($subTaskAssigneesUsers as $subTaskAssign) {
                    $duplicatedRecord = $subTaskAssign->replicate();
                    $duplicatedRecord->sub_task_id = $idNewSubTask;
                    $duplicatedRecord->created_at = date('Y-m-d H:i:s');
                    $duplicatedRecord->updated_at = null;
                    $duplicatedRecord->save();
                }
            }
            $oldTask->update(['status' => "4"]);
            DB::commit();
            $result = responseApi("200", "Success!");
        } catch (\Exception $e) {
            Log::info($e);
            DB::rollBack();
            $result = responseApi("11", "Failed!");
        }

        return response()->json($result, 200);
    }

    public function getListUserInTask($taskId) {
        $task = Task::find($taskId);
        if ($task) {
            $listUserId = [$task->task_supervisor_user_id];
            foreach ($task->subTask()->get() as $subTask) {
                $listUserId[] = $subTask->leader_user_id;
                $usersIdAssign = SubTaskAssigneesUser::where('sub_task_id', $subTask->id)->pluck('user_id')->toArray();
                $listUserId = array_unique(array_merge($listUserId, $usersIdAssign));
            }
            $listUsers = User::whereIn('id', $listUserId)
                ->orWhere('is_admin', '1')
                ->orderBy('position', 'ASC')
                ->get();
            $result = responseApi("200", "Success!", $listUsers);
        } else {
            $result = responseApi("200", "Success!", []);
        }

        return response()->json($result, 200);
    }
}
