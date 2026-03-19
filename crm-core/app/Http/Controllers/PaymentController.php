<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Lead;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['Admin', 'Manager'])) {
            abort(403);
        }

        $totalCollected = Payment::where('status', 'Verified')->sum('amount');
        $thisMonth = Payment::where('status', 'Verified')
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');
        $thisWeek = Payment::where('status', 'Verified')
            ->whereBetween('payment_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('amount');

        $lastMonth = Payment::where('status', 'Verified')
            ->whereMonth('payment_date', now()->subMonth()->month)
            ->whereYear('payment_date', now()->subMonth()->year)
            ->sum('amount');
        $momGrowth = $lastMonth > 0 ? (($thisMonth - $lastMonth) / $lastMonth) * 100 : ($thisMonth > 0 ? 100 : 0);

        $lastWeek = Payment::where('status', 'Verified')
            ->whereBetween('payment_date', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])
            ->sum('amount');
        $wowGrowth = $lastWeek > 0 ? (($thisWeek - $lastWeek) / $lastWeek) * 100 : ($thisWeek > 0 ? 100 : 0);
        $pendingCount = Payment::where('status', 'Pending')->count();

        $monthlyTrendRaw = Payment::where('status', 'Verified')
            ->where('payment_date', '>=', now()->subMonths(5)->startOfMonth())
            ->get();

        $monthlyTrend = $monthlyTrendRaw->groupBy(function ($payment) {
            return $payment->payment_date ? $payment->payment_date->format('Y-m') : $payment->created_at->format('Y-m');
        })->map(function ($group, $key) {
            return (object) [
                'month_key' => $key,
                'month_label' => \Carbon\Carbon::parse($key . '-01')->format('M Y'),
                'total' => $group->sum('amount')
            ];
        })->sortBy('month_key')->values();

        $verifiedPayments = Payment::with(['lead.assignee', 'user'])
            ->where('status', 'Verified')
            ->latest('payment_date')
            ->paginate(15);

        $payments = Payment::with(['lead.assignee'])
            ->where('status', 'Pending')
            ->latest()
            ->paginate(15);

        return view('payments.index', compact(
            'payments',
            'verifiedPayments',
            'totalCollected',
            'thisMonth',
            'thisWeek',
            'pendingCount',
            'monthlyTrend',
            'momGrowth',
            'wowGrowth'
        ));
    }

    public function verify(Request $request, Payment $payment)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['Admin', 'Manager'])) {
            abort(403);
        }

        $request->validate([
            'action' => 'required|in:approve,reject',
            'remarks' => 'nullable|string|max:1000'
        ]);

        if ($request->action === 'approve') {
            if ($payment->status !== 'Pending') {
                return redirect()->back()->with('error', 'This payment is already processed.');
            }

            $lead = $payment->lead;
            if (!$lead) {
                return redirect()->back()->with('error', 'Lead not found for this payment.');
            }

            $kycMandatory = SystemSetting::get('kyc_mandatory', '0');
            if ($kycMandatory === '1' && (!$lead->is_kyc_completed || !$lead->is_rpm_completed)) {
                return redirect()->back()->with('error', 'KYC and RPM must be completed before approval.');
            }

            DB::transaction(function () use ($payment, $lead, $user, $request) {
                $payment->update([
                    'status' => 'Verified',
                    'verified_by' => $user->id,
                    'remarks' => $request->remarks
                ]);

                $lead->update([
                    'status' => 'Paid Client',
                    'service_start_date' => now(),
                    'renewal_date' => now()->addMonths(1),
                ]);

                $lead->activities()->create([
                    'user_id' => $user->id,
                    'activity_type' => 'Payment Approved',
                    'notes' => 'Administrator approved the payment of INR ' . number_format((float) $payment->amount, 2)
                ]);

                \App\Models\SystemAnnouncement::create([
                    'title' => 'Payment Verified',
                    'body' => 'INR ' . number_format((float) $payment->amount, 2) . ' collected by ' . ($lead->assignee->name ?? 'Agent'),
                    'type' => 'success',
                    'expires_at' => now()->addHours(24)
                ]);
            });

            return redirect()->back()->with('success', 'Payment approved and Lead upgraded to Paid Client.');
        }

        $payment->update([
            'status' => 'Rejected',
            'remarks' => $request->remarks
        ]);

        return redirect()->back()->with('warning', 'Payment rejected.');
    }

    public function invoice(Payment $payment)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['Admin', 'Manager', 'SBA', 'BA'])) {
            abort(403);
        }

        if ($user->role === 'BA' && (int) $payment->lead?->assigned_to !== (int) $user->id) {
            abort(403);
        }
        if (in_array($user->role, ['SBA', 'Manager'], true)) {
            $teamIds = $user->getAllTeamIds();
            if (!in_array((int) $payment->lead?->assigned_to, $teamIds, true)) {
                abort(403);
            }
        }

        return view('payments.invoice', compact('payment'));
    }

    public function salesOrders()
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['Admin', 'Manager'])) {
            abort(403);
        }

        $payments = Payment::with(['lead.assignee'])
            ->where('status', 'Verified')
            ->latest('payment_date')
            ->paginate(15);

        return view('payments.sales_orders', compact('payments'));
    }
}
