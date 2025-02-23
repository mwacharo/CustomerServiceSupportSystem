<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\CallHistory;


class CallStatusUpdated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $call;

    public function __construct(CallHistory $call)
    {
        $this->call = $call;
    }

    public function broadcastOn()
    {
        return ['call-status-updates'];
    }
}