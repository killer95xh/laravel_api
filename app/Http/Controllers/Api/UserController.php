<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api', 'check-employment_state'], ['except' => []]);
    }

    public function getUserInfo($userId) {
        $user = User::find($userId);
        $data = [
            "user" => $user
        ];
        $result = responseApi("200", "Success!", $data);

        return response()->json($result, 200);
    }

    public function setDeviceToken(Request $request) {
        $update = auth()->user()->update([
            "device_token" => $request->token
        ]);
        if ($update) {
            $user = auth()->user();
            $result = responseApi("200", "Success!", $user);
        } else {
            $result = responseApi("11", "Failed!");
        }
        return response()->json($result, 200);
    }

    public function getAllUser() {
        $allUser = User::orderBy('created_at', 'DESC')->get();
        $result = responseApi("200", "Success!", $allUser);

        return response()->json($result, 200);
    }

    public function getListSelectSale(Request $request) {
        $listSelectSale = User::where('employment_state', $request->employment_state)
            ->orderBy('position', 'ASC')
            ->orderBy('created_at', 'DESC')
            ->get();
        $data = [
            "listSelectSale" => $listSelectSale
        ];
        $result = responseApi("200", "Success!", $data);

        return response()->json($result, 200);
    }

    public function createUser(Request $request) {
        $dataInsert = array_map('ltrim', $request->all());
        $dataInsert['password'] = bcrypt($dataInsert['password']);
        $dataInsert['created_at'] = date('Y-m-d H:i:s');
        unset($dataInsert['avatar']);
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $extention = $file->getClientOriginalExtension();
            $filename = time() . uniqid() . '.' . $extention;
            $path = "avatar/";
            $file->move(public_path("assets/images/" . $path), $filename);
            $fullPath = $path . $filename;
            $dataInsert['url_avatar'] = $fullPath;
        }
        $statusInsert = User::insert($dataInsert);
        if ($statusInsert) {
            $result = responseApi("200", "Success!");
        } else {
            $result = responseApi("11", "Failed!");
        }
        
        return response()->json($result, 200);
    }

    public function updateUser(Request $request) {
        $dataUpdate = array_map('ltrim', $request->all());
        unset($dataUpdate['id']);
        unset($dataUpdate['avatar']);
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $extention = $file->getClientOriginalExtension();
            $filename = time() . uniqid() . '.' . $extention;
            $path = "avatar/";
            $file->move(public_path("assets/images/" . $path), $filename);
            $fullPath = $path . $filename;
            $dataUpdate['url_avatar'] = $fullPath;
        }
        if ($dataUpdate['password'] != "************") {
            $dataUpdate['password'] = bcrypt($dataUpdate['password']);
        } else {
            unset($dataUpdate['password']);
        }
        $statusUpdate = User::where('id', $request->id)
            ->update($dataUpdate);
        if ($statusUpdate) {
            $result = responseApi("200", "Success!");
        } else {
            $result = responseApi("11", "Failed!");
        }
        
        return response()->json($result, 200);
    }

    public function updateUserAvatar(Request $request) {
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $extention = $file->getClientOriginalExtension();
            $filename = time() . uniqid() . '.' . $extention;
            $path = "avatar/";
            $file->move(public_path("assets/images/" . $path), $filename);
            $fullPath = $path . $filename;
            $dataUpdate['url_avatar'] = $fullPath;
        }
        $statusUpdate = User::where('id', auth()->user()->id)
            ->update([
                'url_avatar' => $fullPath
            ]);
        if ($statusUpdate) {
            $user = User::where('id', auth()->user()->id)->first();
            $result = responseApi("200", "Success!", $user);
        } else {
            $result = responseApi("11", "Failed!");
        }
        
        return response()->json($result, 200);
    }
}
