<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VelocityEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $type;
    public $agentName;

    public function __construct($message, $type = 'win', $agentName = 'Agent')
    {
        $this->message = $message;
        $this->type = $type;
        $this->agentName = $agentName;
    }

    public function broadcastOn()
    {
        return new Channel('velocity-public');
    }

    public function broadcastAs()
    {
        return 'VelocityUpdate';
    }
}
