<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\CustomerBudget;
use App\Models\CycleBudget;
use App\Models\User;
use Carbon\Carbon;

class NotificationBudgetCSKH extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'noti:budget';

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
        Log::channel('job')->info("--------------------------- STARTTTTTTTTTTTTTTT JOB noti:budget ---------------------------");
        $cycleRoot = CycleBudget::where('is_root', "1")->first();
        $notiCustomerBudget = CustomerBudget::leftJoin('users as user_sale', 'customer_budget.sale_user_id', '=', 'user_sale.id')
            ->leftJoin('users as user_ads', 'customer_budget.ads_user_id', '=', 'user_ads.id')
            ->leftJoin('customers', 'customer_budget.customer_id', '=', 'customers.id')
            ->where('customer_budget.cycle_id', $cycleRoot->id)
            ->where('status', "1")
            ->select(
                'customer_budget.*',
                'customers.customer_name',
                DB::raw('customer_budget.facebook_service_amount * 1.05 as facebook_service_after_tax'),
                DB::raw('customer_budget.tiktok_service_amount * 1.05 as tiktok_service_after_tax'),
                DB::raw('customer_budget.google_service_amount * 1.05 as google_service_after_tax'),
                DB::raw('customer_budget.facebook_service_amount
                    + customer_budget.tiktok_service_amount 
                    + customer_budget.google_service_amount
                    + customer_budget.zalo_service_amount
                    as total_amount_before_tax'
                ),
                DB::raw('customer_budget.facebook_service_amount * 1.05
                    + customer_budget.tiktok_service_amount * 1.05
                    + customer_budget.google_service_amount * 1.05
                    + customer_budget.zalo_service_amount
                    as total_amount_after_tax'
                ),
                DB::raw('customer_budget.advance_amount
                    - customer_budget.fee
                    - customer_budget.additional_service_cost
                    - (customer_budget.facebook_service_amount * 1.05
                        + customer_budget.tiktok_service_amount * 1.05
                        + customer_budget.google_service_amount * 1.05
                        + customer_budget.zalo_service_amount)
                    as customer_balance'
                ),
                DB::raw('
                    (customer_budget.advance_amount
                    - customer_budget.fee
                    - customer_budget.additional_service_cost
                    - (customer_budget.facebook_service_amount * 1.05
                        + customer_budget.tiktok_service_amount * 1.05
                        + customer_budget.google_service_amount * 1.05
                        + customer_budget.zalo_service_amount))
                    / 1.1081
                    as remaining_amount'
                )
            )
            ->orderBy('created_at', 'DESC')
            ->get()
            ->toArray();
        $usersAdmin = User::select('id')->where('is_admin', "1")->get();
        foreach ($notiCustomerBudget as $budget) {
            if (!empty($budget['remaining_amount']) && !empty($budget['budget_per_day'])) {
                $countDays = floor($budget['remaining_amount'] / $budget['budget_per_day']);
                $date = strtotime($budget['last_update_date'] . ' +' . $countDays . 'days');
                $newDateString = date('Y-m-d', $date);
                $today = Carbon::today();
                $targetDate = Carbon::create($newDateString);
                $daysDifference = $today->diffInDays($targetDate);
                if ($targetDate->isPast() || ($targetDate->isFuture() && $daysDifference <= 2)) {
                    $listUserId = [
                        $budget['ads_user_id'],
                        $budget['sale_user_id']
                    ];
                    foreach ($usersAdmin as $user) {
                        $listUserId[] = $user->id;
                    }
                    $url_redirect = [
                        "name" => "FinancialManagement",
                        "params" => []
                    ];
                    $params = [
                        $budget['customer_name']
                    ];
                    createNoti(TYPE_NOTI['FinancialManagement'], toJson($params), $listUserId, toJson($url_redirect));
                }
            }
        }
        Log::channel('job')->info("--------------------------- ENDDDDDDDDDDDDDDDDD JOB noti:budget ---------------------------");
        return 11111;
    }
}
