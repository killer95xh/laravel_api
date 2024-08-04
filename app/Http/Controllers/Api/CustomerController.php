<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Customer;
use App\Models\CustomerBudget;
use App\Models\CycleBudget;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api', 'check-employment_state'], ['except' => []]);
    }

    public function getCustomerInfo($customerId) {
        $customer = Customer::leftJoin('users', 'customers.sale_user_id', '=', 'users.id')
            ->select('customers.*', 'users.position', 'users.first_name', 'users.last_name', 'users.url_avatar')
            ->where('customers.is_active', "1")
            ->where('customers.id', $customerId)
            ->first();
        $data = [
            "customer" => $customer
        ];
        $result = responseApi("200", "Success!", $data);

        return response()->json($result, 200);
    }

    public function getAllCustomer() {
        $allCustomer = Customer::leftJoin('users', 'customers.sale_user_id', '=', 'users.id')
            ->select('customers.*', 'users.position', 'users.first_name', 'users.last_name', 'users.url_avatar')
            ->where('customers.is_active', "1")
            ->orderBy('customers.callback_due_date', 'ASC')
            ->orderBy('customers.created_at', 'DESC')
            ->get();
        $result = responseApi("200", "Success!", $allCustomer);

        return response()->json($result, 200);
    }

    public function createCustomer(Request $request) {
        $dataInsert = $request->all();
        $dataInsert['created_at'] = date('Y-m-d H:i:s');
        $dataInsert['customer_name'] = mb_strtoupper($dataInsert['customer_name'], 'UTF-8');
        $statusInsert = Customer::insert($dataInsert);
        if ($statusInsert) {
            $result = responseApi("200", "Success!");
        } else {
            $result = responseApi("11", "Failed!");
        }
        
        return response()->json($result, 200);
    }

    public function updateCustomer(Request $request) {
        $dataUpdate = $request->all();
        if (array_key_exists('customer_name', $dataUpdate)) {
            $dataUpdate['customer_name'] = mb_strtoupper($dataUpdate['customer_name'], 'UTF-8');
        }
        unset($dataUpdate['id']);
        unset($dataUpdate['api_from']);
        DB::beginTransaction();
        try {
            $customer = Customer::where('customers.is_active', "1")->find($request->id);
            if (array_key_exists('callback_due_date', $dataUpdate) && date('Y-m-d' ,strtotime($customer->callback_due_date)) != $dataUpdate['callback_due_date']) {
                $dataUpdate['count_notification'] = 0;
            }
            if (array_key_exists('deal_status', $dataUpdate) && $dataUpdate['deal_status'] == "1") {     //Da chot
                //insert Budget
                $budget = CustomerBudget::where('customer_id', $request->id)->first();
                $cycleRoot = CycleBudget::where('is_root', "1")->first();
                if (!$budget) {
                    $dataInsert = [
                        'customer_id' => $request->id,
                        'status' => "1",
                        'last_update_date' => date('Y-m-d'),
                        'sale_user_id' => $request->sale_user_id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'cycle_id' => $cycleRoot->id
                    ];
                    CustomerBudget::insert($dataInsert);
                }
            } else if (array_key_exists('deal_status', $dataUpdate) && $dataUpdate['deal_status'] == "2") {      //Da huy
                //update Budget status
                $cycleRoot = CycleBudget::where('is_root', "1")->first();
                CustomerBudget::where('customer_id', $request->id)
                    ->where('cycle_id', $cycleRoot->id)
                    ->update(["status" => "0"]); //Tam dung
            }
            Customer::where('id', $request->id)
                ->update($dataUpdate);
            DB::commit();
            $result = responseApi("200", "Success!");
        } catch (\Exception $e) {
            Log::info($e);
            DB::rollBack();
            $result = responseApi("11", "Failed!");
        }
        
        return response()->json($result, 200);
    }

    public function deleteCustomer(Request $request) {
        $cycleRoot = CycleBudget::where('is_root', "1")->first();
        $budget = CustomerBudget::where('customer_id', $request->id)
            ->where('cycle_id', $cycleRoot->id)
            ->first();
        if (!$budget || ($budget && $budget->status == '0')) {
            $statusDelete = Customer::where('id', $request->id)
                ->where('deal_status', '2')
                ->where('is_active', '1')
                ->update(['is_active' => '0']);
            if ($statusDelete) {
                $result = responseApi("200", "Xóa khách hàng thành công!");
            } else {
                $result = responseApi("11", "Xóa khách hàng thất bại!");
            }
        } else {
            $result = responseApi("11", "Khách hàng vẫn còn ngân sách trạng thái đang chạy!");
        }
        return response()->json($result, 200);
    }
}
