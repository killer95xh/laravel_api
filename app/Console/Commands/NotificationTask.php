<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Models\Task;
use Carbon\Carbon;

class NotificationTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'noti:task';

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
        //chay 8h sang
        Log::channel('job')->info("--------------------------- STARTTTTTTTTTTTTTTT JOB noti:task ---------------------------");
        $tasksNoti = Task::whereDate('contract_end_date', '=', Carbon::today()->addDays(2)->toDateString())
            ->where('status', '!=', '4')
            ->get();
        //noti tong phu trach
        if (count($tasksNoti) > 0) {
            foreach ($tasksNoti as $task) {
                $url_redirect = [
                    "name" => "TaskDetail",
                    "params" => [
                        "task_id" => $task->id 
                    ]
                ];
                $params = [
                    $task->project_name
                ];
                createNoti(TYPE_NOTI['TaskDetail'], toJson($params), [$task->task_supervisor_user_id], toJson($url_redirect));
            }
        }
        Log::channel('job')->info("--------------------------- ENDDDDDDDDDDDDDDDDD JOB noti:task ---------------------------");
        return 11111;
    }
}
