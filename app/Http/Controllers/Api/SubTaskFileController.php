<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\SubTaskFileAttachments;

class SubTaskFileController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api', 'check-employment_state'], ['except' => []]);
    }

    public function removeFile(Request $request) {
        SubTaskFileAttachments::find($request->id)->delete();
        $dataInsert = [
            "type" => TYPE_HISTORY['TYPE2'],
            "sub_task_id" => $request->sub_task_id,
            "params" => toJson([
                auth()->user()->last_name,
                "Tệp đính kèm"
            ])
        ];
        createHistory($dataInsert);
        $result = responseApi("200", "Success!");
        
        return response()->json($result, 200);
    }
}
