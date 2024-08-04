<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use App\Models\SubTask;

class NotificationSubTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'noti:sub_task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
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
