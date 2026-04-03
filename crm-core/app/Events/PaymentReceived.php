<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $agentName;
    public $amount;
    public $leadName;

    /**
     * Create a new event instance.
     */
    public function __construct($agentName, $amount, $leadName)
    {
        $this->agentName = $agentName;
        $this->amount = $amount;
        $this->leadName = $leadName;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('sales-alerts'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'payment.received';
    }
}
