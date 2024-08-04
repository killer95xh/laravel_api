<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;

class NotificationCSKH extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'noti:cskh';

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
        //quet chay hang gio
        Log::channel('job')->info("--------------------------- STARTTTTTTTTTTTTTTTTTTTTTTTTTT JOB noti:cskh ---------------------------");
        $customersNotiSale = [];
        $customersNotiAdmin = [];
        if (date('H') == "20") {
            $fourHoursLater = Carbon::now()->addHours(4);
            $customersNotiSale20h = Customer::whereBetween('callback_due_date', [now(), $fourHoursLater])
                ->where('deal_status', "0")
                ->where('is_active', "1")
                ->where('count_notification', 0)
                ->get();
            if (count($customersNotiSale20h) > 0) {
                foreach ($customersNotiSale20h as $customer) {
                    $url_redirect = [
                        "name" => "CusTomCare",
                        "params" => [
                            "customer_id" => $customer->id
                        ]
                    ];
                    $params = [
                        $customer->customer_name
                    ];
                    createNoti(TYPE_NOTI['NotiCSKH20h'], toJson($params), [$customer->sale_user_id], toJson($url_redirect));
                    $customer->update([
                        'count_notification' => $customer->count_notification + 1
                    ]);
                }
            }
        } else if (date('H') == "8") {
            $customersNotiSale = Customer::whereDate('callback_due_date', Carbon::today())
                ->where('deal_status', "0")
                ->where('is_active', "1")
                ->where('count_notification', 1)
                ->get();

            $customersNotiAdmin = Customer::whereDate('callback_due_date', "<", Carbon::today())
                ->where('deal_status', "0")
                ->where('is_active', "1")
                ->get();
        } else if (date('H') == "14") {
            $customersNotiSale = Customer::whereDate('callback_due_date', Carbon::today())
                ->where('deal_status', "0")
                ->where('is_active', "1")
                ->where('count_notification', 2)
                ->get();
        }

        //noti sale
        if (count($customersNotiSale) > 0) {
            foreach ($customersNotiSale as $customer) {
                $url_redirect = [
                    "name" => "CusTomCare",
                    "params" => [
                        "customer_id" => $customer->id
                    ]
                ];
                $params = [
                    $customer->customer_name
                ];
                createNoti(TYPE_NOTI['CusTomCare'], toJson($params), [$customer->sale_user_id], toJson($url_redirect));
                $customer->update([
                    'count_notification' => $customer->count_notification + 1
                ]);
            }
        }

        //noti admin
        if (count($customersNotiAdmin) > 0) {
            $usersAdmin = User::select('id')->where('is_admin', "1")->get();
            $listUserId = [];
            foreach ($usersAdmin as $user) {
                $listUserId[] = $user->id;
            }
            foreach ($customersNotiAdmin as $customer) {
                $url_redirect = [
                    "name" => "CusTomCare",
                    "params" => [
                        "customer_id" => $customer->id
                    ]
                ];
                $params = [
                    $customer->customer_name
                ];
                createNoti(TYPE_NOTI['OutDateCSKH'], toJson($params), $listUserId, toJson($url_redirect));
            }
        }
        Log::channel('job')->info("--------------------------- ENDDDDDDDDDDDDDDDD JOB noti:cskh ---------------------------");
        return 111;
    }
}
