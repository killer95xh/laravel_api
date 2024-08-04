<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\History;

class HistoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api', 'check-employment_state'], ['except' => []]);
    }

    public function createHistory(Request $request) {
        $dataInsert = $request->all();
        $dataInsert['created_at'] = date('Y-m-d H:i:s');
        $task = History::insert($dataInsert);
        if ($task) {
            $result = responseApi("200", "Success!");
        } else {
            $result = responseApi("11", "Failed!");
        }
        
        return response()->json($result, 200);
    }

    public function getListHistoryBySubTaskId($subTaskId) {
        $list = History::where("sub_task_id", $subTaskId)
            ->orderBy("created_at", "DESC")
            ->get();
        $result = responseApi("200", "Success!", $list);

        return response()->json($result, 200);
    }
}
