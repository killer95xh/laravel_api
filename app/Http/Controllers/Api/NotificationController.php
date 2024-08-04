<?php

namespace App\Http\Controllers\Api;

use App\Events\OceanNotification;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Notification;
use App\Models\NotificationUser;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api', 'check-employment_state'], ['except' => ['createNotification']]);
    }

    public function getListNotiByUserId($userId) {
        $notifications = NotificationUser::from("notification_user as a")
            ->leftJoin("notification as b", "a.notification_id", "=", "b.id")
            ->select("b.*", "a.is_seen", "a.id as noti_user_id")
            ->where("a.user_id", $userId)
            ->orderBy("b.created_at", "DESC")
            ->get();
        $result = responseApi("200", "Success!", $notifications);

        return response()->json($result, 200);
    }

    public function updateIsSeen (Request $request) {
        $update = NotificationUser::where('notification_id', $request->noti_id)
            ->where('user_id', auth()->user()->id)
            ->update([
                "is_seen" =>  $request->is_seen
            ]);
        if ($update) {
            $notifications = NotificationUser::from("notification_user as a")
                ->leftJoin("notification as b", "a.notification_id", "=", "b.id")
                ->select("b.*", "a.is_seen", "a.id as noti_user_id")
                ->where("a.user_id", auth()->user()->id)
                ->orderBy("b.created_at", "DESC")
                ->get();
            $result = responseApi("200", "Success!", $notifications);
        } else {
            $result = responseApi("11", "Failed!");
        }

        return response()->json($result, 200);
    }

    public function updateAllNotiSeenByUserId (Request $request) {
        $update = NotificationUser::where('user_id', $request->user_id)->update([
            "is_seen" =>  "1"
        ]);
        if ($update) {
            $notifications = NotificationUser::from("notification_user as a")
                ->leftJoin("notification as b", "a.notification_id", "=", "b.id")
                ->select("b.*", "a.is_seen", "a.id as noti_user_id")
                ->where("a.user_id", auth()->user()->id)
                ->orderBy("b.created_at", "DESC")
                ->get();
            $result = responseApi("200", "Success!", $notifications);
        } else {
            $result = responseApi("11", "Failed!");
        }

        return response()->json($result, 200);
    }
}
