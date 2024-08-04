<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\CustomerBudget;
use App\Models\CycleBudget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CycleBudgetController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api', 'check-employment_state']);
    }

    public function getAllCycleBudget() {
        $cycleBudgets = CycleBudget::orderBy('id', 'DESC')->get();
        $result = responseApi("200", "Success!", $cycleBudgets);

        return response()->json($result, 200);
    }

    public function createCycleBudget(Request $request) {
        DB::beginTransaction();
        try {
            $cycleRootOld = CycleBudget::where('is_root', "1")->first();
            if ($request->is_root == "1") {
                CycleBudget::where('is_root', "1")->update(['is_root' => "0"]);
            }
            $cycleIdNew = CycleBudget::insertGetId([
                "cycle_name" => $request->cycle_name,
                "is_root" => $request->is_root,
            ]);
            $listBudget = $this->getAllCustomerBudgetRoot($cycleRootOld->id);
            foreach ($listBudget as $budgetOld) {
                $dataInsertNewBudget = [
                    "customer_id" => $budgetOld->customer_id,
                    "advance_amount" => floor($budgetOld->customer_balance),
                    "status" => $budgetOld->status,
                    "ads_user_id" => $budgetOld->ads_user_id,
                    "sale_user_id" => $budgetOld->sale_user_id,
                    "cycle_id" => $cycleIdNew,
                    "created_at" => date('Y-m-d H:i:s')
                ];
                CustomerBudget::insert($dataInsertNewBudget);
            }
            DB::commit();
            $result = responseApi("200", "Success!");
        } catch (\Exception $e) {
            Log::info($e);
            DB::rollBack();
            $result = responseApi("11", "Failed!");
        }

        return response()->json($result, 200);
    }

    public function updateCycleBudget(Request $request) {
        DB::beginTransaction();
        try {
            if ($request->is_root == "1") {
                CycleBudget::where('id', '!=', $request->id)
                    ->where('is_root', "1")
                    ->update([
                        'is_root' => "0"
                    ]);
            }
            CycleBudget::where('id', $request->id)
                ->update([
                    "cycle_name" => $request->cycle_name,
                    "is_root" => $request->is_root,
                ]);
            DB::commit();
            $result = responseApi("200", "Success!");
        } catch (\Exception $e) {
            Log::info($e);
            DB::rollBack();
            $result = responseApi("11", "Failed!");
        }

        return response()->json($result, 200);
    }

    public function getAllCustomerBudgetRoot($cycleId) {
        $allCustomerBudget = CustomerBudget::where('cycle_id', $cycleId)
            ->select(
                'customer_budget.*',
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
                )
            )
            ->get();

        return $allCustomerBudget;
    }
}
