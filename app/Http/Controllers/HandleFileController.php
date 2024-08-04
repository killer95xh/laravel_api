<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\NotificationController;
use App\Models\Customer;
use App\Models\SubTask;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HandleFileController extends Controller
{
    public function publicPathIMG($folder, $fileName)
    {
        $path = public_path("assets/images/" . $folder . "/" . $fileName);

        if (!file_exists($path)) {
            abort(404);
        }
        $file = file_get_contents($path);
        $type = mime_content_type($path);

        return response($file, 200)
            ->header('Content-Type', $type);
    }

    public function test(Request $request)
    {
        //chay moi phut
        Log::channel('job')->info("--------------------------- STARTTTTTTTTTTTTTTT JOB noti:sub_task ---------------------------");
        $currentDateTime = Carbon::now()->second(0)->microsecond(0);
        $deadlineDateTime4h = $currentDateTime->copy()->addHours(4);
        $subTasksNotiBefore4h = SubTask::where("status", "!=", "6") //ngoai tru status "Da ban giao"
            ->where('type', '1') //ngoai tru cong viec chay Ads
            ->where('deadline_end_date', $deadlineDateTime4h)
            ->get();

        $deadlineDateTime24h = $currentDateTime->copy()->addHours(24);
        $subTasksNotiBefore24h = SubTask::where("status", "!=", "6") //ngoai tru status "Da ban giao"
            ->where('type', '1') //ngoai tru cong viec chay Ads
            ->where('deadline_end_date', $deadlineDateTime24h)
            ->get();

        $subTasksNotiAfter = SubTask::where("status", "!=", "6") //ngoai tru status "Da ban giao"
            ->where('type', '1') //ngoai tru cong viec chay Ads
            ->whereRaw('(EXTRACT(EPOCH FROM (NOW() - deadline_end_date)) / 60) / 720 >= 0')
            ->whereRaw('(EXTRACT(EPOCH FROM (NOW() - deadline_end_date)) / 60) % 720 <= 1') //noti qua han moi 12h
            ->get();

        if (count($subTasksNotiBefore4h) > 0) {
            foreach ($subTasksNotiBefore4h as $subTask) {
                if ($subTask->task()->first()->status != '4') {
                    $url_redirect = [
                        "name" => "TaskDetail",
                        "params" => [
                            "task_id" => $subTask->task_id,
                            "sub_task_id" => $subTask->id
                        ]
                    ];
                    $params = [
                        $subTask->sub_task_name,
                        "4"
                    ];
                    $listUserIds = [
                        $subTask->leader_user_id,
                    ];
                    foreach ($subTask->user()->get() as $user) {
                        $listUserIds[] = $user->id;
                    }
                    createNoti(TYPE_NOTI['DeadlineSubTask1'], toJson($params), $listUserIds, toJson($url_redirect));
                }
            }
        }
        if (count($subTasksNotiBefore24h) > 0) {
            foreach ($subTasksNotiBefore24h as $subTask) {
                if ($subTask->task()->first()->status != '4') {
                    $url_redirect = [
                        "name" => "TaskDetail",
                        "params" => [
                            "task_id" => $subTask->task_id,
                            "sub_task_id" => $subTask->id
                        ]
                    ];
                    $params = [
                        $subTask->sub_task_name,
                        "24"
                    ];
                    $listUserIds = [
                        $subTask->leader_user_id,
                    ];
                    foreach ($subTask->user()->get() as $user) {
                        $listUserIds[] = $user->id;
                    }
                    createNoti(TYPE_NOTI['DeadlineSubTask1'], toJson($params), $listUserIds, toJson($url_redirect));
                }
            }
        }
        if (count($subTasksNotiAfter) > 0) {
            foreach ($subTasksNotiAfter as $subTask) {
                if ($subTask->task()->first()->status != '4') {
                    $url_redirect = [
                        "name" => "TaskDetail",
                        "params" => [
                            "task_id" => $subTask->task_id ,
                            "sub_task_id" => $subTask->id 
                        ]
                    ];
                    $params = [
                        $subTask->sub_task_name
                    ];
                    $listUserIds = [
                        $subTask->leader_user_id,
                        $subTask->task()->first()->task_supervisor_user_id
                    ];
                    foreach ($subTask->user()->get() as $user) {
                        $listUserIds[] = $user->id;
                    }
                    createNoti(TYPE_NOTI['DeadlineSubTask2'], toJson($params), $listUserIds, toJson($url_redirect));
                }
            }
        }
        Log::channel('job')->info("--------------------------- ENDDDDDDDDDDDDDDDDD JOB noti:sub_task ---------------------------");
        return 11111;
    }
}
