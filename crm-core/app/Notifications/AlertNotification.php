<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AlertNotification extends Notification
{
    use Queueable;

    public $message;
    public $type;
    public $actionUrl;

    public function __construct($message, $type = 'info', $actionUrl = null)
    {
        $this->message = $message;
        $this->type = $type;
        $this->actionUrl = $actionUrl;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => $this->message,
            'type' => $this->type,
            'action_url' => $this->actionUrl,
        ];
    }
}
