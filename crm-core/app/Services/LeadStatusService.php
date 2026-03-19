<?php

namespace App\Services;

class LeadStatusService
{
    /**
     * Get all status definitions with detailed information
     */
    public static function getStatusDefinitions()
    {
        return [
            'Paid Client' => [
                'definition' => 'Prospects who have successfully paid for a specific advisory subscription (e.g., Stock Cash, Stock Future).',
                'qualification' => 'Payment is confirmed and visible in the "Total Payment" section of the dashboard.',
                'required_actions' => [
                    'Generate and send the Invoice',
                    'Ensure KYC UPDATED status is marked as Complete',
                    'Complete the RPM (Risk Profile Management) form',
                ],
                'next_step' => 'Move the client to the "Research Desk" to begin receiving premium market calls.',
                'checklist' => [
                    'Is the Invoice generated?',
                    'Is the KYC document uploaded?',
                    'Has the Risk Profile (RPM) been discussed?',
                    'Is payment verified in system?',
                ],
                'color' => 'emerald',
                'icon' => 'money',
            ],
            'Free Trial' => [
                'definition' => 'Hot leads who are actively testing your advisory services for a limited period (typically 2-5 days).',
                'qualification' => 'The lead has agreed to receive demo calls and is logged under the "Total Free Trial" count on the dashboard.',
                'required_actions' => [
                    'Ensure the lead is receiving the correct segment-based messages',
                    'Call the lead twice daily - once after the first morning tip and once after market close',
                    'Discuss the result and gather feedback',
                ],
                'next_step' => 'Transition the lead to "Expected Payment" or "Make Payment" status before the trial expires.',
                'checklist' => [
                    'Has trial start date been set?',
                    'Is the correct segment assigned?',
                    'Has morning call been made?',
                    'Has evening follow-up been scheduled?',
                ],
                'color' => 'cyan',
                'icon' => 'target',
            ],
            'Follow Up' => [
                'definition' => 'Leads who have shown interest but are not ready to commit to a trial or payment immediately.',
                'qualification' => 'The lead requested a call at a specific time or needs to discuss the investment with a family member.',
                'required_actions' => [
                    'Log a specific date and time in the system',
                    'Add notes regarding their specific investment capacity',
                    'Note preferred segment (e.g., MCX or All Equity)',
                ],
                'next_step' => 'Re-engage at the scheduled time to move them into the "Free Trial" bucket.',
                'checklist' => [
                    'Is follow-up date/time set?',
                    'Are client concerns documented?',
                    'Is investment capacity noted?',
                ],
                'color' => 'purple',
                'icon' => 'phone',
            ],
            'Call Back' => [
                'definition' => 'Leads who requested to be contacted at a later time or specific date.',
                'qualification' => 'Lead explicitly asked for a callback at a particular time.',
                'required_actions' => [
                    'Schedule exact callback time in CRM',
                    'Set reminder notification',
                    'Note reason for callback',
                ],
                'next_step' => 'Contact at scheduled time and attempt to move to Free Trial.',
                'checklist' => [
                    'Is callback time scheduled?',
                    'Is reminder set?',
                    'Is reason documented?',
                ],
                'color' => 'blue',
                'icon' => 'clock',
            ],
            'Expected Payment' => [
                'definition' => 'High-intent leads who have verbally committed to subscribing but have not yet completed the transaction.',
                'qualification' => 'The lead has requested payment details or a payment link. This amount is tracked under "Expected Payment" on the main dashboard.',
                'required_actions' => [
                    'Follow up every 2-4 hours until payment is confirmed',
                    'Assist the client with any technical issues regarding the payment gateway',
                    'Send payment link and invoice details',
                ],
                'next_step' => 'Once payment is verified, move the record to "Paid Client" status.',
                'checklist' => [
                    'Has payment link been sent?',
                    'Is expected amount documented?',
                    'Is follow-up schedule set?',
                ],
                'color' => 'pink',
                'icon' => 'card',
            ],
            'Make Payment' => [
                'definition' => 'Leads who are in the final stage of payment process.',
                'qualification' => 'Lead has agreed to pay and is actively processing payment.',
                'required_actions' => [
                    'Provide payment details immediately',
                    'Stay on call to assist with payment',
                    'Confirm payment receipt',
                ],
                'next_step' => 'Move to "Paid Client" upon confirmation.',
                'checklist' => [
                    'Are payment details shared?',
                    'Is client on call?',
                    'Is payment being processed?',
                ],
                'color' => 'indigo',
                'icon' => 'cash',
            ],
            'Trading' => [
                'definition' => 'Active clients who are currently trading based on advisory calls.',
                'qualification' => 'Client has confirmed they are executing trades.',
                'required_actions' => [
                    'Monitor trading activity',
                    'Provide timely market calls',
                    'Gather feedback on call accuracy',
                ],
                'next_step' => 'Maintain relationship and upsell additional segments.',
                'checklist' => [
                    'Is trading confirmed?',
                    'Are calls being delivered?',
                    'Is feedback being collected?',
                ],
                'color' => 'green',
                'icon' => 'chart',
            ],
            'NPC' => [
                'definition' => 'No Pick Call - Leads that do not respond to outbound calls.',
                'qualification' => 'The call resulted in "Ringing," "Busy," or "Switched Off." These are tracked in the "NPC Leads" report.',
                'required_actions' => [
                    'Mark as NPC and schedule a retry for a different time of day',
                    'Try alternate contact methods (SMS, WhatsApp)',
                    'Track attempt count',
                ],
                'next_step' => 'If a lead remains NPC for more than 5-7 attempts across 3 days, move them to "Dead Leads."',
                'checklist' => [
                    'Has attempt count been updated?',
                    'Is retry time scheduled?',
                    'Have alternate methods been tried?',
                ],
                'color' => 'red',
                'icon' => 'phone-off',
            ],
            'Switch Off' => [
                'definition' => 'Leads whose phone is switched off or out of service.',
                'qualification' => 'Phone shows as switched off or unreachable.',
                'required_actions' => [
                    'Schedule retry for different time',
                    'Try alternate contact number if available',
                    'Track attempt count',
                ],
                'next_step' => 'Retry at different times, move to Dead Leads after 7 attempts.',
                'checklist' => [
                    'Is retry scheduled?',
                    'Are alternate numbers tried?',
                    'Is attempt count updated?',
                ],
                'color' => 'orange',
                'icon' => 'plug',
            ],
            'Not Reachable' => [
                'definition' => 'Leads that cannot be reached despite multiple attempts.',
                'qualification' => 'Phone rings but no answer, or number is invalid.',
                'required_actions' => [
                    'Try at different times of day',
                    'Verify phone number accuracy',
                    'Track all attempts',
                ],
                'next_step' => 'Move to Dead Leads after 7 unsuccessful attempts.',
                'checklist' => [
                    'Have different times been tried?',
                    'Is number verified?',
                    'Are attempts documented?',
                ],
                'color' => 'yellow',
                'icon' => 'phone-miss',
            ],
            'Dead Lead' => [
                'definition' => 'Prospects who have explicitly stated they are not interested or have remained unreachable for a long duration.',
                'qualification' => 'The lead has no investment capital, does not trade in your segments, or has requested to be on the DND (Do Not Disturb) list.',
                'required_actions' => [
                    'Move the lead to the "Dead Leads" folder',
                    'Document reason for closure',
                    'Remove from active calling queue',
                ],
                'next_step' => 'Archive the data for potential re-marketing campaigns after 6 months.',
                'checklist' => [
                    'Is closure reason documented?',
                    'Is lead removed from queue?',
                    'Is data archived?',
                ],
                'color' => 'gray',
                'icon' => 'x',
            ],
            'Not Interested' => [
                'definition' => 'Leads who explicitly declined the service.',
                'qualification' => 'Lead clearly stated they are not interested.',
                'required_actions' => [
                    'Document specific reason for disinterest',
                    'Remove from active pipeline',
                    'Tag for future remarketing',
                ],
                'next_step' => 'Archive for 6-month remarketing campaign.',
                'checklist' => [
                    'Is reason documented?',
                    'Is lead tagged properly?',
                    'Is removal confirmed?',
                ],
                'color' => 'slate',
                'icon' => 'ban',
            ],
        ];
    }

    /**
     * Get status definition by name
     */
    public static function getStatusDefinition($status)
    {
        $definitions = self::getStatusDefinitions();
        return $definitions[$status] ?? null;
    }

    /**
     * Get status color class
     */
    public static function getStatusColor($status)
    {
        $definition = self::getStatusDefinition($status);
        return $definition ? $definition['color'] : 'gray';
    }

    /**
     * Get status icon
     */
    public static function getStatusIcon($status)
    {
        $definition = self::getStatusDefinition($status);
        return $definition ? $definition['icon'] : 'list';
    }

    /**
     * Check if status requires checklist
     */
    public static function requiresChecklist($status)
    {
        $checklistStatuses = ['Paid Client', 'Free Trial', 'Expected Payment', 'Make Payment'];
        return in_array($status, $checklistStatuses);
    }

    /**
     * Get checklist for status
     */
    public static function getChecklist($status)
    {
        $definition = self::getStatusDefinition($status);
        return $definition ? $definition['checklist'] : [];
    }
}
