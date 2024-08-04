<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class OceanNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $type_noti;
    public $params;
    public $to_list_user;
    public $url_redirect;
    public $notification_id;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($type_noti, $params, $to_list_user, $url_redirect, $notification_id)
    {
        $this->type_noti = $type_noti;
        $this->params = $params;
        $this->to_list_user = $to_list_user;
        $this->url_redirect = $url_redirect;
        $this->notification_id = $notification_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel('ocean-notification');
    }

    public function broadcastWith()
    {
        return [
            'type_noti' => $this->type_noti,
            'params' => $this->params,
            'to_list_user' => $this->to_list_user,
            'url_redirect' => $this->url_redirect,
            'notification_id' => $this->notification_id,
        ];
    }
}
