<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExternalLeadController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $token = config('services.external_leads.token');
        if (!$token) {
            return response()->json(['success' => false, 'message' => 'External lead token not configured'], 503);
        }
        $authHeader = (string) $request->header('Authorization', '');
        $bearer = Str::startsWith($authHeader, 'Bearer ') ? substr($authHeader, 7) : '';
        $provided = $request->header('X-Lead-Token', $bearer);
        if (!hash_equals($token, (string) $provided)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:15',
            'email' => 'nullable|email',
            'source' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:2000',
        ]);

        $sanitize = function ($value) {
            return is_string($value) ? trim(strip_tags($value)) : $value;
        };

        $lead = \App\Models\Lead::create([
            'name' => $sanitize($validated['name']),
            'mobile' => $sanitize($validated['mobile']),
            'email' => isset($validated['email']) ? $sanitize($validated['email']) : null,
            'source' => isset($validated['source']) ? $sanitize($validated['source']) : 'External API',
            'location' => isset($validated['location']) ? $sanitize($validated['location']) : 'Unknown',
            'remarks' => isset($validated['remarks']) ? $sanitize($validated['remarks']) : 'Lead received via Webhook.',
            'status' => 'Cold Lead',
            'lead_score' => 50, // Default mid-score
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lead created successfully',
            'lead_id' => $lead->id
        ], 201);
    }
}
