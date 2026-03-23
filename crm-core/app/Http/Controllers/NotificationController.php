<?php

namespace App\Http\Controllers;

use App\Models\EmployeeMessage;
use App\Models\InternalMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class NotificationController extends Controller
{
    /**
     * Get unread counts for chat and mail
     */
    public function getCounts(Request $request)
    {
        // CRITICAL BUG FIX: Unlock session file immediately so navigation clicks aren't blocked!
        session_write_close();

        $user = auth()->user();
        if (!$user) {
            abort(403);
        }
        $cacheKey = "user_notifications_{$user->id}";

        $cachedData = Cache::remember($cacheKey, 10, function () use ($user) {
            
            // Chat Unread Count
            $chatCount = EmployeeMessage::where('receiver_id', $user->id)
                ->where('is_read', false)
                ->count();

            // Per-sender breakdown
            $chatBySender = EmployeeMessage::where('receiver_id', $user->id)
                ->where('is_read', false)
                ->selectRaw('sender_id, count(*) as count')
                ->groupBy('sender_id')
                ->pluck('count', 'sender_id');

            // Mail Unread Count
            $mailCount = InternalMail::where(function ($q) use ($user) {
                $q->whereJsonContains('recipient_ids', (string) $user->id)
                    ->orWhereJsonContains('recipient_ids', 'all');
            })
                ->whereDoesntHave('readReceipts', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->count();

            // Latest Advisory Call ID + Text
            $latestAdvisory = \App\Models\AdvisoryCall::latest('id')->first();
            $latestAdvisoryId = $latestAdvisory->id ?? 0;
            $latestAdvisoryText = $latestAdvisory
                ? ($latestAdvisory->segment . ' | ' . $latestAdvisory->stock_name . ' | ' . $latestAdvisory->action_type . ' @ INR ' . $latestAdvisory->entry_price . ' | SL: INR ' . $latestAdvisory->stoploss . ' | TGT: INR ' . $latestAdvisory->target)
                : '';

            // Latest System Announcement ID
            $latestAnnouncementId = \App\Models\SystemAnnouncement::where('expires_at', '>', now())->max('id') ?? 0;

            // Detailed list for dropdown
            $dropdownFollowups = \App\Models\Lead::where('assigned_to', $user->id)
                ->whereIn('status', ['Call Back', 'Follow Up', 'Interested'])
                ->whereNotNull('follow_up_date')
                ->where('follow_up_date', '<=', now())
                ->orderBy('follow_up_date', 'asc')
                ->take(5)
                ->get();

            // Pending Payments (for Admin & Manager)
            $dropdownPayments = collect();
            if (in_array($user->role, ['Admin', 'Manager'])) {
                $query = \App\Models\Payment::where('status', 'Pending');
                if ($user->role === 'Manager') {
                    $teamIds = $user->getAllTeamIds();
                    $query->whereHas('lead', fn($q) => $q->whereIn('assigned_to', $teamIds));
                }
                $dropdownPayments = $query->with(['lead', 'user'])->take(5)->get();
            }

            // Expiring Clients (Renewals)
            $dropdownRenewals = \App\Models\Lead::where('assigned_to', $user->id)
                ->where('status', 'Paid Client')
                ->whereNotNull('service_expired_at')
                ->whereBetween('service_expired_at', [now(), now()->addDays(7)])
                ->orderBy('service_expired_at', 'asc')
                ->take(5)
                ->get();

            $totalNotificationsCount = $dropdownFollowups->count() + $dropdownPayments->count() + $dropdownRenewals->count();

            return [
                'chat' => $chatCount,
                'chat_by_sender' => $chatBySender,
                'mail' => $mailCount,
                'latest_advisory_id' => $latestAdvisoryId,
                'latest_advisory_text' => $latestAdvisoryText,
                'latest_announcement_id' => $latestAnnouncementId,
                'total_notifications_count' => $totalNotificationsCount,
                'dropdown_followups' => $dropdownFollowups->map(function ($lead) {
                    return [
                        'id' => $lead->id,
                        'name' => $lead->name,
                        'time' => $lead->follow_up_date ? $lead->follow_up_date->format('d M, H:i') : '',
                    ];
                }),
                'dropdown_payments' => $dropdownPayments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => (float) $payment->amount,
                        'lead_name' => $payment->lead->name ?? 'Unknown',
                        'agent_name' => $payment->user->name ?? 'Unknown',
                    ];
                }),
                'dropdown_renewals' => $dropdownRenewals->map(function ($lead) {
                    return [
                        'id' => $lead->id,
                        'name' => $lead->name,
                        'expires_on' => $lead->service_expired_at ? $lead->service_expired_at->format('d M y') : '',
                    ];
                }),
                'due_followups' => [], // Placeholder - filled below
                'ticker_message' => \App\Models\SystemSetting::get('ticker_message', ''),
                'ticker_urgency' => \App\Models\SystemSetting::get('ticker_urgency', 'normal'),
            ];
        });

        // REAL-TIME follow-up reminders (NEVER cached)
        $dueFollowups = \App\Models\Lead::where('assigned_to', $user->id)
            ->whereIn('status', [
                'Follow Up',
                'Call Back',
                'Interested',
                'Warm Lead',
                'Hot Lead',
                'Negotiation',
            ])
            ->where('follow_up_date', '<=', now()->addMinutes(5))
            ->where('follow_up_date', '>=', now()->subMinutes(30))
            ->orderBy('follow_up_date', 'asc')
            ->get();

        $cachedData['due_followups'] = $dueFollowups->map(function ($lead) {
            return [
                'id' => $lead->id,
                'name' => $lead->name,
                'time' => $lead->follow_up_date->format('H:i'),
                'exact_time' => $lead->follow_up_date->timestamp,
            ];
        })->values()->toArray();

        // Dynamic Announcement Checking (Not cached so it appears instantly)
        $newAnnouncement = null;
        if ($request->has('last_announcement_id') && $cachedData['latest_announcement_id'] > (int) $request->last_announcement_id) {
            $newAnnouncement = \App\Models\SystemAnnouncement::find($cachedData['latest_announcement_id']);
        }
        $cachedData['new_announcement'] = $newAnnouncement;

        return response()->json($cachedData);
    }

    /**
     * Mark all database notifications as read
     */
    public function readAll()
    {
        $user = auth()->user();
        if ($user) {
            $user->unreadNotifications->markAsRead();
        }
        return redirect()->back();
    }
}
