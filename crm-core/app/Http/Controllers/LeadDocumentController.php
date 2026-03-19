<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadDocument;
use App\Models\SystemSetting;
use App\Services\DocumentWatermarkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LeadDocumentController extends Controller
{
    private DocumentWatermarkService $watermark;

    public function __construct(DocumentWatermarkService $watermark)
    {
        $this->watermark = $watermark;
    }

    private function authorizeLead(Lead $lead, $user): void
    {
        if (!$user) {
            abort(401);
        }
        if ($user->role === 'Admin') {
            return;
        }
        if ($user->role === 'Manager' || $user->role === 'SBA') {
            $teamIds = $user->getAllTeamIds();
            if (!in_array($lead->assigned_to, $teamIds, true)) {
                abort(403);
            }
            return;
        }
        if ($lead->assigned_to !== $user->id) {
            abort(403);
        }
    }

    public function upload(Request $request, Lead $lead)
    {
        $user = auth()->user();
        $this->authorizeLead($lead, $user);

        $validated = $request->validate([
            'doc_type' => 'required|string|max:50',
            'document' => [
                'required',
                'file',
                'max:10240',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'], true)) {
                        $fail('Unsupported file type.');
                    }
                }
            ],
        ]);

        $file = $request->file('document');
        $path = $file->store('lead_docs', ['disk' => 'local']);
        $hash = hash_file('sha256', $file->getPathname());
        $ttlMinutes = (int) SystemSetting::get('doc_access_minutes', '1440');

        LeadDocument::create([
            'lead_id' => $lead->id,
            'uploaded_by' => $user->id,
            'doc_type' => $validated['doc_type'],
            'storage_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'sha256' => $hash,
            'watermark_text' => 'CONFIDENTIAL - ' . $user->name,
            'access_token' => Str::random(64),
            'access_expires_at' => now()->addMinutes($ttlMinutes),
        ]);

        return back()->with('success', 'Document uploaded securely.');
    }

    public function download(Request $request, string $token)
    {
        $user = auth()->user();
        if (!$user) {
            abort(401);
        }

        $doc = LeadDocument::where('access_token', $token)->firstOrFail();
        if ($doc->access_expires_at && $doc->access_expires_at->isPast()) {
            abort(403);
        }

        $this->authorizeLead($doc->lead, $user);

        if (!Storage::disk('local')->exists($doc->storage_path)) {
            abort(404);
        }

        $sourcePath = Storage::disk('local')->path($doc->storage_path);
        $ext = strtolower(pathinfo($doc->original_name, PATHINFO_EXTENSION));

        if ($ext === 'pdf' && $doc->watermark_text) {
            $tmpDir = storage_path('app/tmp');
            if (!is_dir($tmpDir)) {
                mkdir($tmpDir, 0755, true);
            }
            $tmpFile = $tmpDir . '/wm_' . Str::random(16) . '.pdf';
            $this->watermark->watermarkPdf($sourcePath, $tmpFile, $doc->watermark_text);
            return response()->download($tmpFile, $doc->original_name)->deleteFileAfterSend(true);
        }

        return Storage::disk('local')->download($doc->storage_path, $doc->original_name);
    }
}
