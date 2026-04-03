<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;

class AiAnalysisController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user || !$user->hasPermission('ai_analysis', 'view')) {
            abort(403);
        }

        // Pass empty collection so blade $history never throws "Undefined variable"
        $history = collect([]);

        return view('ai.analysis', compact('history'));
    }

    public function analyze(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->hasPermission('ai_analysis', 'execute')) {
            abort(403);
        }

        $request->validate([
            'audio_file' => 'required|mimes:mp3,wav,m4a|max:10240',
        ]);

        $mockResult = [
            'fileName'        => $request->file('audio_file')->getClientOriginalName(),
            'sentiment'       => 'Positive',
            'sentiment_score' => 85,
            'confidence'      => 'High',
            'keywords'        => ['Pricing', 'Subscription', 'Interested', 'Follow-up'],
            'summary'         => 'The customer expressed strong interest in the annual plan but had questions about the cancellation policy. The agent handled the objection well.',
            'transcript'      => "Agent: Hello, how can I help you?\nCustomer: Hi, I'm interested in your premium plan.\nAgent: That's great! It includes...",
        ];

        return redirect()->route('ai.index')->with('analysis_result_obj', $mockResult);
    }
}
