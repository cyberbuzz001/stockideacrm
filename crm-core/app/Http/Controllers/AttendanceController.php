<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['Admin', 'Manager'])) {
            abort(403);
        }

        $query = Attendance::with('user');

        // Only filter by date if explicitly provided
        if ($request->filled('date')) {
            $query->where('date', $request->date);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->user_id);
        }

        $attendances = $query->latest('date')->latest('login_at')->paginate(20);
        $employees = User::where('role', '!=', 'Admin')->get();

        // Calculate Stats for Today (Unpaginated)
        $today = now()->toDateString();
        $todayStats = Attendance::where('date', $today)
            ->selectRaw("SUM(CASE WHEN attendance_type = 'Present' THEN 1 ELSE 0 END) as present")
            ->selectRaw("SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active")
            ->selectRaw("AVG(productivity_score) as avg_productivity")
            ->selectRaw("SUM(CASE WHEN attendance_type IN ('Absent','Leave') THEN 1 ELSE 0 END) as absent")
            ->first();
        $stats = [
            'present' => (int) ($todayStats->present ?? 0),
            'active' => (int) ($todayStats->active ?? 0),
            'avg_productivity' => (float) ($todayStats->avg_productivity ?? 0),
            'absent' => (int) ($todayStats->absent ?? 0),
        ];

        return view('attendance.index', compact('attendances', 'employees', 'stats'));
    }

    /**
     * Monthly Matrix Report
     */
    public function monthlyReport(Request $request)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['Admin', 'Manager'])) {
            abort(403);
        }

        $month = $request->input('month', now()->format('Y-m'));
        $startOfMonth = \Carbon\Carbon::parse($month)->startOfMonth();
        $endOfMonth = \Carbon\Carbon::parse($month)->endOfMonth();
        $daysInMonth = $startOfMonth->daysInMonth;

        // Fetch all employees
        $employees = User::where('role', '!=', 'Admin')->get();

        // Fetch attendance for date range
        $attendances = Attendance::whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->groupBy('user_id');

        // Structure Data: [userId][day] = {status, hours}
        $reportData = [];
        foreach ($employees as $emp) {
            $userRecords = $attendances->get($emp->id);
            $recordsByDate = $userRecords
                ? $userRecords->keyBy(function ($att) {
                    return $att->date instanceof Carbon ? $att->date->toDateString() : $att->date;
                })
                : collect();
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dateStr = $startOfMonth->copy()->day($d)->toDateString();

                $record = $recordsByDate->get($dateStr);

                if ($record) {
                    $reportData[$emp->id][$d] = [
                        'type' => $record->attendance_type, // Present, Absent, etc
                        'hours' => $record->effective_hours,
                        'status' => $record->status
                    ];
                } else {
                    $reportData[$emp->id][$d] = null; // No record
                }
            }
        }

        return view('attendance.monthly-report', compact('employees', 'reportData', 'daysInMonth', 'month', 'startOfMonth'));
    }

    /**
     * Export Monthly Report to CSV
     */
    public function exportMonthly(Request $request)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['Admin', 'Manager'])) {
            abort(403);
        }

        $month = $request->input('month', now()->format('Y-m'));
        $startOfMonth = \Carbon\Carbon::parse($month)->startOfMonth();
        $endOfMonth = \Carbon\Carbon::parse($month)->endOfMonth();
        $daysInMonth = $startOfMonth->daysInMonth;

        $employees = User::where('role', '!=', 'Admin')->get();
        $attendances = Attendance::whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->groupBy('user_id');

        $filename = "attendance_report_{$month}.csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($employees, $attendances, $startOfMonth, $daysInMonth) {
            $file = fopen('php://output', 'w');

            // Header Row
            $header = ['Employee Name'];
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $header[] = $startOfMonth->copy()->day($d)->format('d M');
            }
            fputcsv($file, $header);

            // Data Rows
            foreach ($employees as $emp) {
                $row = [$emp->name];
                $userRecords = $attendances->get($emp->id);
                $recordsByDate = $userRecords
                    ? $userRecords->keyBy(function ($att) {
                        return $att->date instanceof Carbon ? $att->date->toDateString() : $att->date;
                    })
                    : collect();

                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $dateStr = $startOfMonth->copy()->day($d)->toDateString();
                    $record = $recordsByDate->get($dateStr);

                    if ($record) {
                        $type = $record->attendance_type;
                        $code = ($type === 'Present') ? 'P' : (($type === 'Half Day') ? 'HD' : (($type === 'Leave') ? 'L' : 'A'));
                        $row[] = "{$code} (" . number_format($record->effective_hours, 1) . "h)";
                    } else {
                        $row[] = '-';
                    }
                }
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Store/Update Attendance (Admin Only)
     */
    public function store(Request $request)
    {
        if (auth()->user()->role !== 'Admin')
            abort(403);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'attendance_type' => 'required|in:Present,Absent,Leave,Half Day',
            'admin_remarks' => 'nullable|string'
        ]);

        $attendance = Attendance::updateOrCreate(
            ['user_id' => $request->user_id, 'date' => $request->date],
            [
                'attendance_type' => $request->attendance_type,
                'admin_remarks' => $request->admin_remarks,
                // Reset tracking stats if marking absent/leave to avoid confusion
                'status' => $request->attendance_type === 'Present' ? 'Active' : $request->attendance_type
            ]
        );

        return back()->with('success', 'Attendance updated successfully.');
    }

    /**
     * Heartbeat API to track User Activity (Mouse/Keyboard/Clicks)
     * Called every 1 minute via JS
     */
    public function heartbeat(Request $request)
    {
        // CRITICAL BUG FIX: Unlock session file immediately so navigation clicks aren't blocked!
        session_write_close();

        $user = auth()->user();
        if (!$user)
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);

        $validated = $request->validate([
            'active_input' => 'nullable|boolean',
        ]);

        $now = now();
        $today = $now->toDateString();

        // Rule 1: Auto-Checkout / Shift End (dynamic from Control Center)
        $shiftEnd = SystemSetting::get('shift_end_time', '17:30');
        $autoCheckout = SystemSetting::get('auto_checkout_enabled', '1');
        $shiftEndAt = null;
        try {
            $shiftEndAt = Carbon::createFromFormat('H:i', $shiftEnd, $now->getTimezone())
                ->setDate($now->year, $now->month, $now->day);
        } catch (\Exception $e) {
            $shiftEndAt = Carbon::createFromFormat('H:i', '17:30', $now->getTimezone())
                ->setDate($now->year, $now->month, $now->day);
        }

        // Rule 2: Lunch Break (dynamic from Control Center)
        $lunchStart = SystemSetting::get('lunch_start', '13:00');
        $lunchEnd = SystemSetting::get('lunch_end', '13:30');
        $lunchStartAt = null;
        $lunchEndAt = null;
        try {
            $lunchStartAt = Carbon::createFromFormat('H:i', $lunchStart, $now->getTimezone())
                ->setDate($now->year, $now->month, $now->day);
            $lunchEndAt = Carbon::createFromFormat('H:i', $lunchEnd, $now->getTimezone())
                ->setDate($now->year, $now->month, $now->day);
        } catch (\Exception $e) {
            $lunchStartAt = Carbon::createFromFormat('H:i', '13:00', $now->getTimezone())
                ->setDate($now->year, $now->month, $now->day);
            $lunchEndAt = Carbon::createFromFormat('H:i', '13:30', $now->getTimezone())
                ->setDate($now->year, $now->month, $now->day);
        }

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($autoCheckout === '1' && $shiftEndAt && $now->greaterThanOrEqualTo($shiftEndAt)) {
            if ($attendance) {
                $attendance->status = 'Off-Duty';
                if (!$attendance->logout_at) {
                    $attendance->logout_at = $now;
                }
                $attendance->save();
            }
            return response()->json(['status' => 'Shift Ended', 'message' => 'Office hours over'], 200);
        }

        if ($lunchStartAt && $lunchEndAt && $now->greaterThanOrEqualTo($lunchStartAt) && $now->lessThan($lunchEndAt)) {
            if ($attendance && $attendance->status !== 'Off-Duty') {
                $attendance->status = 'Idle';
                $attendance->save();
            }
            return response()->json(['status' => 'Lunch Break', 'message' => 'Tracking paused for lunch'], 200);
        }

        // If no attendance record exists for today (edge case), create one
        if (!$attendance) {
            $attendance = Attendance::firstOrCreate(
                ['user_id' => $user->id, 'date' => $today],
                [
                    'login_at' => $now,
                    'ip_address' => $request->ip(),
                    'status' => 'Active',
                    'last_activity_at' => $now
                ]
            );
        }

        // If Admin marked this as Absent/Leave/Half Day, don't override with auto-tracking
        if (in_array($attendance->attendance_type, ['Absent', 'Leave', 'Half Day'])) {
            return response()->json(['status' => $attendance->attendance_type], 200);
        }

        $isUserActiveInput = (bool) ($validated['active_input'] ?? false); // Did JS detect mouse/key movement?

        // Avoid double-counting if heartbeat fires too frequently
        if ($attendance->updated_at && $attendance->updated_at->diffInSeconds($now) < 50) {
            if ($isUserActiveInput) {
                $attendance->last_activity_at = $now;
                $attendance->status = 'Active';
                $attendance->save();
            }
            return response()->json([
                'status' => $attendance->status,
                'effective_hours' => number_format((float) $attendance->effective_hours, 2),
                'productivity' => number_format((float) $attendance->productivity_score, 1) . '%'
            ]);
        }

        // Logic:
        // 1. If explicit input detected OR recent explicit action (call/save), marked as Active.
        // 2. If no input for > 10 mins -> Idle.
        // 3. If no input for > 15 mins -> Inactive.

        if ($isUserActiveInput) {
            // User physically moved mouse or typed
            $attendance->last_activity_at = $now;
            $attendance->status = 'Active';
        }

        // Calculate time since last activity (dynamic thresholds from Control Center)
        $idleThreshold = (int) SystemSetting::get('heartbeat_idle_minutes', 10);
        $inactiveThreshold = (int) SystemSetting::get('heartbeat_inactive_minutes', 15);
        $minutesSinceActivity = $attendance->last_activity_at ? $attendance->last_activity_at->diffInMinutes($now) : 0;

        if ($minutesSinceActivity >= $inactiveThreshold) {
            $attendance->status = 'Inactive';
        } elseif ($minutesSinceActivity >= $idleThreshold) {
            $attendance->status = 'Idle';
        } else {
            // Still within active buffer (Active or reading screen)
        }

        // Calculate Effective Hours (Only Increment if Active)
        if ($attendance->status === 'Active') {
            // Add 1 minute to effective hours (since this heartbeat runs every min)
            $attendance->effective_hours += (1 / 60);
        } else {
            $attendance->idle_duration += 1; // Add 1 minute to idle
        }

        // Calculate Productivity Score
        $totalTime = $attendance->login_at ? $attendance->login_at->diffInMinutes($now) : 0;
        // Exclude Lunch Time from total time if we are past lunch
        if ($lunchStartAt && $lunchEndAt && $now->greaterThanOrEqualTo($lunchEndAt)) {
            $totalTime -= $lunchStartAt->diffInMinutes($lunchEndAt);
        }
        if ($totalTime < 0) {
            $totalTime = 0;
        }

        $effectiveMinutes = $attendance->effective_hours * 60;

        if ($totalTime > 0) {
            $attendance->productivity_score = ($effectiveMinutes / $totalTime) * 100;
        }

        $attendance->save();

        return response()->json([
            'status' => $attendance->status,
            'effective_hours' => number_format((float) $attendance->effective_hours, 2),
            'productivity' => number_format((float) $attendance->productivity_score, 1) . '%'
        ]);
    }
}
