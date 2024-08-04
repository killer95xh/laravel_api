<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Events\OceanNotification;
use App\Models\History;
use Illuminate\Support\Facades\DB;

use App\Models\Notification;
use App\Models\NotificationUser;
use App\Models\User;


if (!function_exists('sendNotiFireBase')) {
    function sendNotiFireBase($dataNoti, $ids) {
        $FcmToken = User::whereNotNull('device_token')
            ->whereIn('id', $ids)
            ->pluck('device_token')
            ->all();
        $data = [
            "registration_ids" => $FcmToken,
            "notification" => [
                "title" => 'OceanLink',
                "body" => getTextNotiFireBase($dataNoti),
                "data" => $dataNoti,
                "sound" => 'default',
            ]
        ];
        Log::info('REQUEST CALL API FIREBASE: ' . toJson($data));
        $headers = [
            'Authorization:key=' . env('FIREBASE_SERVER_KEY'),
            'Content-Type: application/json',
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, env('FIREBASE_URL'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, toJson($data));

        try {
            $result = curl_exec($ch);
            $resultStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if(curl_errno($ch)) {
                Log::info('CURL ERROR CALL API FIREBASE: ' . curl_error($ch));
            }
            Log::info('RESPONSE CALL API FIREBASE HTTP_CODE: ' . $resultStatus . ': ' . $result);
            if (empty($result)) {
                Log::info('Lỗi không nhận được RESPONSE CALL API FIREBASE');
            }
            curl_close($ch);
        } catch (Exception $e) {
            Log::info('EXCEPTION CALL API FIREBASE: ' . $e);
        }
        return $result;
    }
}

if (!function_exists('getTextNotiFireBase')) {
    function getTextNotiFireBase($dataNoti) {
        $params = json_decode($dataNoti['params'], 1);
        $text = "";
        switch ($dataNoti['type_noti']) {
            case "1":
                $text = "Đến hạn gọi lại cho khách hàng " . $params[0] . ". Vui lòng kiểm tra lại tiến độ."; //CSKH
                break;
        
            case "2":
                $text = "Ngân sách của khách hàng " . $params[0] . ". cần cập nhật. Vui lòng kiểm tra."; //Ngân sách
                break;

            case "3":
                $text = "Bạn đã có thể theo dõi quản lý ngân sách của " . $params[0] . ".";  //Ngân sách
                break;
                
            case "4":
                $text = "Dự án " . $params[0] . " sắp đến ngày bàn giao. Hãy kiểm tra lại tiến độ."; //Task
                break;
                
            case "5":
                $text = "Bạn đã được giao cho dự án " . $params[0] . "."; //Task
                break;
                
            case "6":
                $text = $params[0] . " đã bình luận trong công việc " . $params[1] . "."; //Sub Task
                break;
                
            case "7":
                $text = "Bạn đã được giao cho công việc " . $params[0] . " của dự án " . $params[1] . "."; //Sub Task
                break;
                
            case "8":
                $text = "Công việc " . $params[0] . " đã được bàn giao cho bạn, vui lòng kiểm tra."; //Sub Task
                break;
                
            case "9":
                $text = $params[0] . " đã chuyển trạng thái của công việc " . $params[1] . " của dự án " . $params[2] . " thành " . $params[3] . "."; //Sub Task
                break;
                
            case "10":
                $text = "Chú ý " . $params[0] . " mai đến hạn gọi."; //CSKH
                break;
                
            case "11":
                $text = "Quá hạn gọi lại cho khách hàng " . $params[0] . ". Vui lòng kiểm tra lại tiến độ."; //CSKH
                break;
                
            case "12":
                $text = $params[0] . " đã nhắc đến bạn trong bình luận công việc " . $params[1] . "."; //Sub Task
                break;
                
            case "13":
                $text = "Deadline " . $params[0] . " còn " . $params[1] . "h nữa đến hạn, hãy kiểm tra lại tiến độ!!"; //Sub Task
                break;
                
            case "14":
                $text = "Deadline " . $params[0] . " đã quá hạn, hãy kiểm tra lại tiến độ!!"; //Sub Task
                break;
        }

        return $text;
    }
}

if (!function_exists('createNoti')) {
    function createNoti($typeNoti, $params, $toListUser, $urlRedirect) {
        if (count($toListUser) > 0) {
            $dataNoti = [
                "type_noti" => $typeNoti,
                "params" => $params,
                "url_redirect" => $urlRedirect
            ];
            $dataNoti['created_at'] = date('Y-m-d H:i:s');
            DB::beginTransaction();
            try {
                $idInsert = Notification::insertGetId($dataNoti);
                $listUserId = array_unique($toListUser);
                foreach ($listUserId as $userId) {
                    $dataInsert = [
                        'notification_id' => $idInsert,
                        'user_id' => $userId,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    NotificationUser::insert($dataInsert);
                }
                unset($dataNoti['created_at']);
                broadcast(new OceanNotification($typeNoti, $params, $listUserId, $urlRedirect, $idInsert));
                sendNotiFireBase($dataNoti, $listUserId);
                DB::commit();
                return true;
            } catch (\Exception $e) {
                Log::info($e);
                DB::rollBack();
                return false;
            }
        }
    }
}

if (!function_exists('createHistory')) {
    function createHistory($dataInsert) {
        $dataInsert['created_at'] = date('Y-m-d H:i:s');
        $history = History::insert($dataInsert);
        if ($history) {
            $result = responseApi("200", "Success!");
        } else {
            $result = responseApi("11", "Failed!");
        }
        
        return response()->json($result, 200);
    }
}

if (!function_exists('toJson')) {
    function toJson($data) {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

if (!function_exists('sendEmail')) {
    function sendEmail($subject, $emailTo, $emailCc, $name, $content) {
        try {
            Mail::send('emails.index', compact('name', 'content'), function($email) use ($subject, $emailTo, $emailCc) {
                $email->subject($subject);
                $email->to($emailTo);
                if ($emailCc != null && $emailCc != '') {
                    $email->cc(explode(',', $emailCc));
                }
            });
            return true;
        } catch (Exception $e) {
            Log::info('sendEmail to ' . $emailTo . ' Exception: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('responseApi')) {
    function responseApi($returnCode, $message, $data = []) {
        $result = [
            "returnCode" => $returnCode,
            "message" => $message,
            "data" => $data
        ];

        return $result;
    }
}

if (!function_exists('insertOrUpdateDB')) {
    function insertOrUpdateDB($table, $credentials)
    {
        try {
            $credentials = collect($credentials);
            $creFirstData = $credentials->first();
    
            if ($creFirstData) {
                $columns = implode(", ", array_keys($creFirstData));
                $updates = collect(array_keys($creFirstData))->map(function ($item) {
                    return $item . " = VALUES($item)";
                })->implode(',');
            } else {
                return false;
            }
    
            $values = $credentials->map(function ($cre) {
                $items = collect($cre)
                    ->map(function ($item) {
                        return !is_null($item) ? "'" . $item . "'" : "null";
                    });
                $items = $items->implode(',');
    
                return '(' . $items . ')';
            })->implode(',');
    
            DB::statement("INSERT INTO {$table}({$columns}) VALUES {$values} ON DUPLICATE KEY UPDATE {$updates}");
            return true;
        } catch (Exception $e) {
            Log::info("insertOrUpdateDB Exception: " . $e);
            return false;
        }
    }
}

if (!function_exists('getListUserAdmin')) {
    function getListUserAdmin() {
        $users = User::where('employment_state', "1")
            ->where('is_admin', "1")
            ->get();
        return $users;
    }
}