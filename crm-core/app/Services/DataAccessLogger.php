<?php

namespace App\Services;

use App\Models\DataAccessLog;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;

class DataAccessLogger
{
    public static function log(User $user, Lead $lead, string $field, string $action, Request $request, array $context = []): void
    {
        DataAccessLog::create([
            'user_id' => $user->id,
            'lead_id' => $lead->id,
            'field' => $field,
            'action' => $action,
            'context' => $context,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);
    }
}
