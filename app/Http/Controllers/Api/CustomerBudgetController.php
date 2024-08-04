<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Customer;
use App\Models\CustomerBudget;
use App\Models\CycleBudget;
use Illuminate\Support\Facades\DB;

class CustomerBudgetController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api', 'check-employment_state']);
    }

    public function getListCustomerBudgetActive() {
        $userId = auth()->user()->id;
        $cycleRoot = CycleBudget::where('is_root', "1")->first();
        if (auth()->user()->is_admin == "1") {
            // $allCustomerBudget = CustomerBudget::leftJoin('customers', 'customer_budget.customer_id', '=', 'customers.id')
            // ->where('customer_budget.cycle_id', $cycleRoot->id)
            // ->where('customer_budget.status', '1')
            // ->select(
            //     'customer_budget.*',
            //     'customers.customer_name',
            //     'customers.company_name',
            //     'customers.phone_number',
            //     'customers.email',
            //     'customers.address',
            // )
            // ->orderBy('created_at', 'DESC')
            // ->get()
            // ->toArray();

            $allCustomerBudget = Customer::where('is_active', '1')
                ->where('deal_status', '1')
                ->orderBy('created_at', 'DESC')
                ->get()
                ->toArray();
        } else {
            // $allCustomerBudget = CustomerBudget::leftJoin('customers', 'customer_budget.customer_id', '=', 'customers.id')
            // ->where('customer_budget.cycle_id', $cycleRoot->id)
            // ->where('customer_budget.status', '1')
            // ->where(function ($query) use ($userId) {
            //     $query->where('customer_budget.sale_user_id', $userId)
            //         ->orWhere('customer_budget.ads_user_id', $userId);
            // })
            // ->select(
            //     'customer_budget.*',
            //     'customers.customer_name',
            //     'customers.company_name',
            //     'customers.phone_number',
            //     'customers.email',
            //     'customers.address'
            // )
            // ->orderBy('created_at', 'DESC')
            // ->get()
            // ->toArray();
            $allCustomerBudget = Customer::where('is_active', '1')
                ->where('deal_status', '1')
                ->where('sale_user_id', $userId)
                ->orderBy('created_at', 'DESC')
                ->get()
                ->toArray();
        }
        $data = [
            "allCustomerBudget" => $allCustomerBudget,
        ];
        $result = responseApi("200", "Success!", $data);

        return response()->json($result, 200);
    }

    public function getAllCustomerBudget() {
        $userId = auth()->user()->id;
        if (auth()->user()->is_admin == "1") {
            $allCustomerBudget = CustomerBudget::leftJoin('users as user_sale', 'customer_budget.sale_user_id', '=', 'user_sale.id')
            ->leftJoin('users as user_ads', 'customer_budget.ads_user_id', '=', 'user_ads.id')
            ->leftJoin('customers', 'customer_budget.customer_id', '=', 'customers.id')
            ->select(
                'customer_budget.*',
                'customers.customer_name',
                'customers.company_name',
                'customers.phone_number',
                'customers.email',
                'customers.address',
                'user_sale.first_name as sale_first_name',
                'user_sale.last_name as sale_last_name',
                'user_sale.position as sale_position',
                'user_ads.last_name as ads_last_name',
                'user_ads.position as ads_position',
                DB::raw('customer_budget.facebook_service_amount * 1.05 as facebook_service_after_tax'),
                DB::raw('customer_budget.tiktok_service_amount * 1.108033 as tiktok_service_after_tax'),
                DB::raw('customer_budget.google_service_amount * 1.05 as google_service_after_tax'),
                DB::raw('customer_budget.facebook_service_amount
                    + customer_budget.tiktok_service_amount 
                    + customer_budget.google_service_amount
                    + customer_budget.zalo_service_amount
                    as total_amount_before_tax'
                ),
                DB::raw('customer_budget.facebook_service_amount * 1.05
                    + customer_budget.tiktok_service_amount * 1.108033
                    + customer_budget.google_service_amount * 1.05
                    + customer_budget.zalo_service_amount
                    as total_amount_after_tax'
                ),
                DB::raw('customer_budget.advance_amount
                    - customer_budget.fee
                    - customer_budget.additional_service_cost
                    - (customer_budget.facebook_service_amount * 1.05
                        + customer_budget.tiktok_service_amount * 1.108033
                        + customer_budget.google_service_amount * 1.05
                        + customer_budget.zalo_service_amount)
                    as customer_balance'
                ),
                DB::raw('
                    (customer_budget.advance_amount
                    - customer_budget.fee
                    - customer_budget.additional_service_cost
                    - (customer_budget.facebook_service_amount * 1.05
                        + customer_budget.tiktok_service_amount * 1.108033
                        + customer_budget.google_service_amount * 1.05
                        + customer_budget.zalo_service_amount))
                    / 1.1081
                    as remaining_amount'
                )
            )
            ->orderBy('customers.customer_name', 'ASC')
            ->get()
            ->toArray();
        } else {
            $allCustomerBudget = CustomerBudget::leftJoin('users as user_sale', 'customer_budget.sale_user_id', '=', 'user_sale.id')
            ->leftJoin('users as user_ads', 'customer_budget.ads_user_id', '=', 'user_ads.id')
            ->leftJoin('customers', 'customer_budget.customer_id', '=', 'customers.id')
            ->where("customer_budget.sale_user_id", $userId)
            ->orWhere("customer_budget.ads_user_id", $userId)
            ->select(
                'customer_budget.*',
                'customers.customer_name',
                'customers.company_name',
                'customers.phone_number',
                'customers.email',
                'customers.address',
                'user_sale.first_name as sale_first_name',
                'user_sale.last_name as sale_last_name',
                'user_sale.position as sale_position',
                'user_ads.last_name as ads_last_name',
                'user_ads.position as ads_position',
                DB::raw('customer_budget.facebook_service_amount * 1.05 as facebook_service_after_tax'),
                DB::raw('customer_budget.tiktok_service_amount * 1.108033 as tiktok_service_after_tax'),
                DB::raw('customer_budget.google_service_amount * 1.05 as google_service_after_tax'),
                DB::raw('customer_budget.facebook_service_amount
                    + customer_budget.tiktok_service_amount 
                    + customer_budget.google_service_amount
                    + customer_budget.zalo_service_amount
                    as total_amount_before_tax'
                ),
                DB::raw('customer_budget.facebook_service_amount * 1.05
                    + customer_budget.tiktok_service_amount * 1.108033
                    + customer_budget.google_service_amount * 1.05
                    + customer_budget.zalo_service_amount
                    as total_amount_after_tax'
                ),
                DB::raw('customer_budget.advance_amount
                    - customer_budget.fee
                    - customer_budget.additional_service_cost
                    - (customer_budget.facebook_service_amount * 1.05
                        + customer_budget.tiktok_service_amount * 1.108033
                        + customer_budget.google_service_amount * 1.05
                        + customer_budget.zalo_service_amount)
                    as customer_balance'
                ),
                DB::raw('
                    (customer_budget.advance_amount
                    - customer_budget.fee
                    - customer_budget.additional_service_cost
                    - (customer_budget.facebook_service_amount * 1.05
                        + customer_budget.tiktok_service_amount * 1.108033
                        + customer_budget.google_service_amount * 1.05
                        + customer_budget.zalo_service_amount))
                    / 1.1081
                    as remaining_amount'
                )
            )
            ->orderBy('created_at', 'DESC')
            ->get()
            ->toArray();
        }
        foreach ($allCustomerBudget as $key => $budget) {
            if (!empty($budget['remaining_amount']) && !empty($budget['budget_per_day'])) {
                $countDays = floor($budget['remaining_amount'] / $budget['budget_per_day']);
                $date = strtotime($budget['last_update_date'] . ' +' . $countDays . 'days');
                $newDateString = date('Y-m-d', $date);
                $allCustomerBudget[$key]['projected_budget_end_date'] = $newDateString;
            } else {
                $allCustomerBudget[$key]['projected_budget_end_date'] = $budget['last_update_date'];
            }
        }
        $data = [
            "allCustomerBudget" => $allCustomerBudget,
        ];
        $result = responseApi("200", "Success!", $data);

        return response()->json($result, 200);
    }

    public function updateCustomerBudget(Request $request) {
        $cycleRoot = CycleBudget::where('is_root', "1")->first();
        $dataUpdate = $request->all();
        unset($dataUpdate['id']);
        $budget = CustomerBudget::leftJoin('customers', 'customer_budget.customer_id', '=', 'customers.id')
            ->where('customer_budget.id', $request->id)
            ->select('customer_budget.*', 'customers.customer_name')
            ->first();
        $statusUpdate = CustomerBudget::where('id', $request->id)
            ->update($dataUpdate);
        if ($statusUpdate) {
            if ($dataUpdate['ads_user_id'] != $budget->ads_user_id && $budget->cycle_id == $cycleRoot->id) {
                $typeNoti = TYPE_NOTI['FinancialManagementSub'];
                $params = toJson([ 
                    $budget->customer_name
                ]);
                $toListUser = [$dataUpdate['ads_user_id']];
                $urlRedirect = toJson([
                    "name" => "FinancialManagement",
                    "params" => []
                ]);
                if ($budget->status == "1") {
                    createNoti($typeNoti, $params, $toListUser, $urlRedirect);
                }
            }
            if ($dataUpdate['sale_user_id'] != $budget->sale_user_id && $budget->cycle_id == $cycleRoot->id) {
                $typeNoti = TYPE_NOTI['FinancialManagementSub'];
                $params = toJson([ 
                    $budget->customer_name
                ]);
                $toListUser = [$dataUpdate['sale_user_id']];
                $urlRedirect = toJson([
                    "name" => "FinancialManagement",
                    "params" => []
                ]);
                if ($budget->status == "1") {
                    createNoti($typeNoti, $params, $toListUser, $urlRedirect);
                }
            }
            $result = responseApi("200", "Success!");
        } else {
            $result = responseApi("11", "Failed!");
        }
        
        return response()->json($result, 200);
    }
}
