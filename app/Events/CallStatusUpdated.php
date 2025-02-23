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
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $call;

    /**
     * Create a new event instance.
     */
    public function __construct(CallHistory $call)
    {
        $this->call = $call;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        return new Channel('call-status-updates');
    }

    /**
     * Customize the event name that gets broadcasted.
     */
    public function broadcastAs()
    {
        return 'call.status.updated';
    }

    /**
     * Data to send with the broadcast.
     */
    public function broadcastWith()
    {
        return [
            'id' => $this->call->id,
            'status' => $this->call->status,
            'updated_at' => $this->call->updated_at->toDateTimeString(),
        ];
    }
}
