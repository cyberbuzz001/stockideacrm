<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use App\Models\User;
use Carbon\Carbon;

class ExpireClients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clients:auto-expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically mark clients/trials as expired when their end date passes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting client expiration check...');
        $count = 0;
        
        $adminId = User::where('role', 'Admin')->value('id')
            ?? User::where('role', 'Manager')->value('id')
            ?? User::orderBy('id')->value('id');

        // Find expired Free Trials
        Lead::where('status', 'Free Trial')
            ->whereDate('trial_end_date', '<', Carbon::today())
            ->chunkById(200, function ($leads) use (&$count, $adminId) {
                foreach ($leads as $lead) {
                    $lead->update(['status' => 'Service Expired']);
                    if ($adminId) {
                        $lead->activities()->create([
                            'user_id' => $adminId,
                            'activity_type' => 'Status Updated',
                            'notes' => 'System automatically marked Free Trial as expired.'
                        ]);
                    }
                    $count++;
                    $this->line("Marked Lead ID {$lead->id} (Trial) as expired.");
                }
            });

        // Find expired Paid Clients
        Lead::where('status', 'Paid Client')
            ->whereDate('renewal_date', '<', Carbon::today())
            ->chunkById(200, function ($leads) use (&$count, $adminId) {
                foreach ($leads as $lead) {
                    $lead->update(['status' => 'Service Expired']);
                    if ($adminId) {
                        $lead->activities()->create([
                            'user_id' => $adminId,
                            'activity_type' => 'Status Updated',
                            'notes' => 'System automatically marked Paid Client service as expired.'
                        ]);
                    }
                    $count++;
                    $this->line("Marked Lead ID {$lead->id} (Paid Client) as expired.");
                }
            });

        $this->info("Completed! marked {$count} clients as expired.");
    }
}
