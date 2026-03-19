<?php

namespace App\Http\Controllers;

use App\Models\MessageTemplate;
use Illuminate\Http\Request;

class MessageTemplateController extends Controller
{
    /**
     * Display a listing of the resource (Admin View).
     */
    public function index()
    {
        if (!auth()->user() || auth()->user()->role !== 'Admin') {
            abort(403);
        }

        $templates = MessageTemplate::latest()->get();
        return view('admin.templates.index', compact('templates'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user() || auth()->user()->role !== 'Admin') {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:sms,email',
            'subject' => 'nullable|required_if:type,email|string|max:255',
            'body' => 'required|string',
        ]);

        $validated['created_by'] = auth()->id();

        MessageTemplate::create($validated);

        return back()->with('success', 'Template created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MessageTemplate $messageTemplate)
    {
        if (!auth()->user() || auth()->user()->role !== 'Admin') {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:sms,email',
            'subject' => 'nullable|required_if:type,email|string|max:255',
            'body' => 'required|string',
        ]);

        $messageTemplate->update($validated);

        return back()->with('success', 'Template updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MessageTemplate $messageTemplate)
    {
        if (!auth()->user() || auth()->user()->role !== 'Admin') {
            abort(403);
        }

        $messageTemplate->delete();

        return back()->with('success', 'Template deleted successfully.');
    }

    /**
     * JSON list for agents to use in Lead Detail.
     */
    public function list(Request $request)
    {
        $type = $request->query('type', 'sms');
        $templates = MessageTemplate::where('type', $type)
            ->latest()
            ->get(['id', 'name', 'subject', 'body']);

        return response()->json($templates);
    }
}
