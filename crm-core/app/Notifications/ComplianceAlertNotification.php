<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ComplianceAlertNotification extends Notification
{
    use Queueable;

    protected $lead;
    protected $alertType;

    public function __construct(Lead $lead, $alertType = 'FOLLOW-UP REQUIRED')
    {
        $this->lead = $lead;
        $this->alertType = $alertType;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'compliance_alert',
            'title' => '🚨 ' . $this->alertType,
            'message' => "Client {$this->lead->name} has not confirmed service or accepted terms.",
            'lead_id' => $this->lead->id,
            'lead_name' => $this->lead->name,
            'action_url' => route('leads.show', $this->lead->id),
        ];
    }
}
