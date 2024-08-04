<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use Validator;


class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api', 'check-employment_state'], ['except' => ['login', 'resetPassword']]);
    }

    public function login(Request $request)
    {
        $userName = $request->user_name;
        $password = $request->password;
        if (!$token = auth()->attempt(['user_name' => $userName, 'password' => $password, 'employment_state' => "1"])) {
            $result = responseApi("11", "Đăng nhập thất bại!", ['error' => '401 Unauthorized']);
            return response()->json($result, 401);
        }
        $result = responseApi("200", "Đăng nhập thành công!", [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()->toArray()
        ]);
        return response()->json($result, 200);
    }

    public function logout()
    {
        auth()->logout();
        $result = responseApi("200", "Đăng xuất thành công!");
        return response()->json($result, 200);
    }

    public function resetPassword(Request $request) {
        $randomPassword = uniqid();
        $password = bcrypt($randomPassword);
        $user = User::where('user_name', $request->user_name)->first();
        $update = User::where('user_name', $request->user_name)
            ->update([
                'password' => $password
            ]);
        if ($update) {
            $subject = "[OceanLink] Cập nhật lại mật khẩu cho tài khoản " . $request->user_name;
            $emailTo = $user->email;
            $emailCc = "";
            $name = $request->user_name;
            $content = "Mật khẩu đăng nhập của bạn đã được thay đổi thành: " . $randomPassword;
            $send = sendEmail($subject, $emailTo, $emailCc, $name, $content);
            if ($send) {
                $result = responseApi("200", "Cập nhật lại mật khẩu thành công!");
            }
        } else {
            $result = responseApi("11", "Cập nhật lại mật khẩu thất bại!");
        }

        return response()->json($result, 200);
    }

    public function changePassWord(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string|min:6',
            'new_password' => 'required|string|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $userId = auth()->user()->id;

        $user = User::where('id', $userId)->update(
            ['password' => bcrypt($request->new_password)]
        );

        return response()->json([
            'message' => 'User successfully changed password',
            'user' => $user,
        ], 201);
    }
}
