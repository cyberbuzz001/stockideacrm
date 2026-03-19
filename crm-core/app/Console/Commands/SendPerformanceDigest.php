<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendPerformanceDigest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:daily-digest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily performance summary to Admins and Managers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->format('Y-m-d');

        $this->info("Generating report for {$today}...");

        // 1. Total Revenue
        $totalRevenue = \App\Models\Payment::whereDate('payment_date', $today)
            ->where('status', 'Verified')
            ->sum('amount');

        // 2. Calls Made
        // Assuming 'Call Started' or similar activity type from lead interaction
        $callsMade = \App\Models\LeadActivity::whereDate('created_at', $today)
            ->where('activity_type', 'like', '%Call%') // Broad match for now
            ->count();

        // 3. Top Agents by Revenue
        $topAgents = \App\Models\User::whereIn('role', ['SBA', 'BA'])
            ->withCount([
                'activities as calls_count' => function ($q) use ($today) {
                    $q->whereDate('created_at', $today);
                }
            ])
            ->withSum([
                'payments' => function ($q) use ($today) {
                    $q->whereDate('payment_date', $today)->where('payments.status', 'Verified');
                }
            ], 'amount')
            ->orderByDesc('payments_sum_amount')
            ->take(5)
            ->get()
            ->map(function ($user) {
                $user->revenue = $user->payments_sum_amount ?? 0;
                return $user;
            });

        $data = [
            'total_revenue' => $totalRevenue,
            'calls_made' => $callsMade,
            'top_agents' => $topAgents
        ];

        // 4. Send Email
        $recipients = \App\Models\User::whereIn('role', ['Admin', 'Manager'])->pluck('email');

        if ($recipients->isNotEmpty()) {
            \Illuminate\Support\Facades\Mail::to($recipients)->send(new \App\Mail\PerformanceDigest($data));
            $this->info("Digest sent to " . $recipients->count() . " recipients.");
        } else {
            $this->warn("No recipients found.");
        }
    }
}
