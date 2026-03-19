<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class EscalateLeads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leads:escalate';

    protected $description = 'Escalate leads that are Interested but have no activity for 24 hours';

    public function handle()
    {
        $cutoff = now()->subHours(24);

        $systemUserId = \App\Models\User::where('role', 'Admin')->value('id')
            ?? \App\Models\User::where('role', 'Manager')->value('id')
            ?? \App\Models\User::orderBy('id')->value('id');

        $total = 0;
        \App\Models\Lead::where('is_escalated', false)
            ->whereIn('status', ['Interested', 'Follow Up'])
            ->where('updated_at', '<', $cutoff)
            ->chunkById(200, function ($leads) use (&$total, $systemUserId) {
                foreach ($leads as $lead) {
                    $lead->update([
                        'is_escalated' => true,
                        'escalated_at' => now(),
                        'sentiment_label' => 'Critical Attention'
                    ]);

                    if ($systemUserId) {
                        $lead->activities()->create([
                            'user_id' => $systemUserId,
                            'activity_type' => 'System Escalation',
                            'notes' => 'Lead escalated due to 24-hour inactivity in ' . $lead->status . ' status.'
                        ]);
                    }
                    $total++;
                }
            });

        $this->info($total . ' leads escalated successfully.');
    }
}
