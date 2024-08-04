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

class CommentSubTask implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sub_task_id;
    public $user_id;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($sub_task_id, $user_id)
    {
        $this->sub_task_id = $sub_task_id;
        $this->user_id = $user_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel('new-comment');
    }

    public function broadcastWith()
    {
        return [
            'sub_task_id' => $this->sub_task_id,
            'user_id' => $this->user_id,
        ];
    }
}
