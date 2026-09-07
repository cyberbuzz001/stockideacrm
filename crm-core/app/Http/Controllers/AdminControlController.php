<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminControlController extends Controller
{
    private const ALLOWED_SETTING_KEYS = [
        'heartbeat_idle_minutes',
        'heartbeat_inactive_minutes',
        'auto_checkout_enabled',
        'shift_end_time',
        'lunch_start',
        'lunch_end',
        'assignment_mode',
        'npc_recycle_limit',
        'trading_segments',
        'min_registration_fee',
        'kyc_mandatory',
        'data_masking_enabled',
        'ticker_message',
        'ticker_urgency',
        'company_name',
        'calling_provider',
        'calling_api_key',
        'calling_api_secret',
        'sms_provider',
        'sms_api_key',
        'sms_api_secret',
        'whatsapp_provider',
        'whatsapp_api_key',
        'whatsapp_api_phone_id',
        'whatsapp_evolution_url',
        'whatsapp_evolution_instance',
        'whatsapp_webhook_verify_token',
        'whatsapp_meta_app_secret',
        'ai_provider',
        'ai_api_key',
    ];

    public function index()
    {
        if (!auth()->user()->hasPermission('system', 'control_center')) {
            abort(403);
        }

        $settings = SystemSetting::allSettings();
        $employees = User::where('role', '!=', 'Admin')->get();

        return view('admin.control-center', compact('settings', 'employees'));
    }

    /**
     * AJAX update any setting
     */
    public function update(Request $request)
    {
        if (!auth()->user()->hasPermission('system', 'control_center')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'key' => ['required', 'string', Rule::in(self::ALLOWED_SETTING_KEYS)],
            'value' => 'nullable|string',
        ]);

        $value = $this->normalizeSettingValue($request->key, $request->value);
        SystemSetting::set($request->key, $value);
        SystemSetting::flushCache();

        return response()->json(['success' => true, 'key' => $request->key, 'value' => $value]);
    }

    /**
     * Update Company Branding (Logo & Name form)
     */
    public function updateBranding(Request $request)
    {
        if (!auth()->user()->hasPermission('system', 'control_center')) {
            abort(403);
        }

        $request->validate([
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('company_logo')) {
            $path = $request->file('company_logo')->store('branding', 'public');
            SystemSetting::set('company_logo', $path);
            SystemSetting::flushCache();
        }

        return back()->with('success', 'Branding updated successfully.');
    }

    /**
     * Override attendance (Leave/Half-Day)
     */
    public function overrideAttendance(Request $request)
    {
        if (!auth()->user()->hasPermission('system', 'control_center')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:Leave,Half Day',
        ]);

        $attendance = Attendance::updateOrCreate(
            ['user_id' => $request->user_id, 'date' => now()->toDateString()],
            [
                'attendance_type' => $request->type,
                'status' => $request->type,
                'admin_remarks' => 'Admin override via Control Center by ' . auth()->user()->name,
            ]
        );

        $user = User::select('name')->find($request->user_id);
        return response()->json(['success' => true, 'message' => ($user ? $user->name : 'User') . ' marked as ' . $request->type]);
    }

    /**
     * Update the Global Ticker
     */
    public function updateTicker(Request $request)
    {
        if (!auth()->user()->hasPermission('system', 'control_center')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'message' => 'nullable|string|max:500',
            'urgency' => 'required|in:normal,urgent,emergency',
        ]);

        $safeMessage = $this->sanitizeText($request->message ?? '');
        SystemSetting::set('ticker_message', $safeMessage);
        SystemSetting::set('ticker_urgency', $request->urgency);
        SystemSetting::flushCache();

        return response()->json(['success' => true, 'message' => 'Ticker updated']);
    }

    private function sanitizeText(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        // Basic XSS hardening before storage
        return strip_tags($value);
    }

    private function normalizeSettingValue(string $key, ?string $value): string
    {
        $value = $value ?? '';

        // Normalize numeric switches and limits
        if (in_array($key, ['heartbeat_idle_minutes', 'heartbeat_inactive_minutes', 'npc_recycle_limit', 'min_registration_fee'], true)) {
            $intValue = (int) $value;
            if ($intValue < 0) {
                $intValue = 0;
            }
            return (string) $intValue;
        }

        if (in_array($key, ['auto_checkout_enabled', 'kyc_mandatory', 'data_masking_enabled'], true)) {
            return ($value === '1' || $value === 'true') ? '1' : '0';
        }

        // Normalize time fields to HH:MM if possible
        if (in_array($key, ['shift_end_time', 'lunch_start', 'lunch_end'], true)) {
            if (preg_match('/^\d{1,2}:\d{2}$/', $value)) {
                [$h, $m] = explode(':', $value, 2);
                $h = str_pad((string) ((int) $h), 2, '0', STR_PAD_LEFT);
                $m = str_pad((string) ((int) $m), 2, '0', STR_PAD_LEFT);
                return $h . ':' . $m;
            }
            return $value;
        }

        if ($key === 'ticker_message') {
            return $this->sanitizeText($value);
        }

        return $value;
    }

    public function getWhatsAppQRCode()
    {
        if (!auth()->user()->hasPermission('system', 'control_center')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $urlBase = SystemSetting::get('whatsapp_evolution_url', 'http://localhost:8080');
        $instance = SystemSetting::get('whatsapp_evolution_instance', 'shreesvarn');
        $token = SystemSetting::get('whatsapp_api_key');

        if (!$token || !$instance) {
            return response()->json(['error' => 'Evolution API key or Instance name is missing in settings.'], 400);
        }

        $url = rtrim($urlBase, '/') . "/instance/connect/{$instance}";

        $qrCode = null;
        $state = null;
        $lastError = null;
        $statusCode = 500;
        $attempts = 3;

        for ($i = 0; $i < $attempts; $i++) {
            try {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'apikey' => $token
                ])->timeout(15)->get($url);

                $statusCode = $response->status();

                if ($response->successful()) {
                    $data = $response->json();
                    $state = $data['instance']['state'] ?? null;
                    $qrCode = $data['hash']['qrcode'] ?? null;

                    if ($state === 'open' || $qrCode) {
                        break;
                    }

                    $lastError = 'Evolution API session is initializing. Please wait a moment and try again.';
                } else {
                    $lastError = 'Evolution API Error: ' . ($response->json('message') ?? $response->body());
                }
            } catch (\Exception $e) {
                $lastError = 'Failed to connect to Evolution API: ' . $e->getMessage();
                $statusCode = 500;
            }

            if ($i < $attempts - 1) {
                sleep(2); // Wait 2 seconds before retrying
            }
        }

        if ($state === 'open') {
            return response()->json(['status' => 'connected', 'message' => 'WhatsApp is already connected!']);
        }

        if ($qrCode) {
            return response()->json(['status' => 'qrcode', 'qrcode' => $qrCode]);
        }

        return response()->json(['error' => $lastError ?: 'Failed to retrieve QR code from Evolution API.'], $statusCode);
    }
}

