<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $apiKey;
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key', env('GEMINI_API_KEY'));
    }

    /**
     * Generate a summary of the lead's last interactions.
     */
    public function generateLeadSummary($lead, $activities)
    {
        if (empty($this->apiKey)) {
            return "AI Summary unavailable: API key not configured.";
        }

        $historyText = $activities->map(function($act) {
            return "- [{$act->created_at->format('Y-m-d')}] {$act->action}: {$act->notes}";
        })->implode("\n");

        $prompt = "You are a senior sales assistant for a high-end stock market advisory. 
        Summarize the following last 5 interactions with lead '{$lead->name}' into 3 bullet points. 
        Focus on: 1. Interest level, 2. Key concerns, 3. Next logical step.
        
        Interactions:
        $historyText
        
        Output format: Concise bullet points only.";

        return $this->callGemini($prompt);
    }

    /**
     * Provide advice on how to handle a specific objection.
     */
    public function getObjectionAdvice($lead, $objection)
    {
        $prompt = "A lead named '{$lead->name}' just raised this objection: '{$objection}'.
        In the context of stock market advisory services, give 2 quick 'Soft UI' feel rebuttals that are professional and persuasive.
        Keep it sharp and goal-oriented.";

        return $this->callGemini($prompt);
    }

    /**
     * Core API caller
     */
    protected function callGemini($prompt)
    {
        try {
            $response = Http::post("{$this->baseUrl}?key={$this->apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                return $response->json('candidates.0.content.parts.0.text') ?? "AI was unable to generate a response.";
            }

            Log::error("Gemini API Error: " . $response->body());
            return "Internal AI Error: Unable to fetch insight.";

        } catch (\Exception $e) {
            Log::error("Gemini Service Exception: " . $e->getMessage());
            return "Connection Error: AI is currently offline.";
        }
    }
}
