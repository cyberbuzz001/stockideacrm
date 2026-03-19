<?php

namespace App\Http\Controllers;

use App\Models\InternalMail;
use App\Models\MailReadReceipt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InternalMailController extends Controller
{
    /**
     * Display inbox
     */
    public function index(Request $request)
    {
        $currentUser = auth()->user();
        if (!$currentUser) {
            abort(403);
        }
        $category = $request->get('category');

        // Get mails where user is recipient
        $mails = InternalMail::where(function ($q) use ($currentUser) {
            $q->whereJsonContains('recipient_ids', (string) $currentUser->id)
                ->orWhereJsonContains('recipient_ids', 'all');
        })
            ->when($category, function ($q) use ($category) {
                $q->where('category', $category);
            })
            ->with(['sender', 'readReceipts'])
            ->latest()
            ->paginate(20);

        // Add isRead flag for current user
        $mails->getCollection()->transform(function ($mail) use ($currentUser) {
            $mail->isReadByCurrentUser = $mail->isReadBy($currentUser->id);
            return $mail;
        });

        $categories = ['Compliance Alert', 'Target Updates', 'Market Holidays', 'General'];

        // Get unread count
        $unreadCount = InternalMail::where(function ($q) use ($currentUser) {
            $q->whereJsonContains('recipient_ids', (string) $currentUser->id)
                ->orWhereJsonContains('recipient_ids', 'all');
        })
            ->whereDoesntHave('readReceipts', function ($q) use ($currentUser) {
                $q->where('user_id', $currentUser->id);
            })
            ->count();

        return view('internal-mails.index', compact('mails', 'categories', 'category', 'unreadCount'));
    }

    /**
     * Show compose form
     */
    public function create()
    {
        // Permission check
        if (!in_array(auth()->user()->role, ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized: Only Admins and Managers can send internal mails.');
        }

        $employees = User::where('id', '!=', auth()->id())
            ->select('id', 'name', 'role')
            ->orderBy('name')
            ->get();

        $categories = ['Compliance Alert', 'Target Updates', 'Market Holidays', 'General'];

        return view('internal-mails.create', compact('employees', 'categories'));
    }

    /**
     * Store new mail
     */
    public function store(Request $request)
    {
        // Permission check
        if (!in_array(auth()->user()->role, ['Admin', 'Manager'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'recipients' => 'required', // 'all' or comma-separated IDs
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:5000',
            'category' => 'required|string|in:Compliance Alert,Target Updates,Market Holidays,General',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:10240', // 10MB max
        ]);

        if ($validated['recipients'] === 'all') {
            $recipientIds = ['all'];
        } else {
            $recipientIds = array_values(array_filter(array_map('trim', explode(',', $validated['recipients']))));
            $recipientIds = array_unique($recipientIds);
        }

        if (empty($recipientIds)) {
            return back()->withErrors(['recipients' => 'Select at least one recipient.']);
        }

        if ($recipientIds !== ['all']) {
            $validIds = User::whereIn('id', $recipientIds)->pluck('id')->map(fn($id) => (string) $id)->all();
            if (count($validIds) !== count($recipientIds)) {
                return back()->withErrors(['recipients' => 'Invalid recipients selected.']);
            }
            $recipientIds = $validIds;
        }

        $mailData = [
            'sender_id' => auth()->id(),
            'subject' => $this->sanitizeText($validated['subject']),
            'body' => $this->sanitizeText($validated['body']),
            'category' => $validated['category'],
            'recipient_ids' => $recipientIds,
        ];

        // Handle file attachment
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $safeBase = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $ext = strtolower($file->getClientOriginalExtension());
            $fileName = time() . '_' . ($safeBase !== '' ? $safeBase : 'attachment') . '.' . $ext;
            $path = $file->storeAs('mail-attachments', $fileName, 'public');
            $mailData['attachment_path'] = $path;
        }

        $mail = InternalMail::create($mailData);

        // TODO: Broadcast notification to recipients
        // broadcast(new InternalMailSent($mail, $recipientIds));

        return redirect()->route('internal-mails.index')
            ->with('success', 'Mail sent successfully to ' .
                ($validated['recipients'] === 'all' ? 'all employees' : count($recipientIds) . ' recipients'));
    }

    /**
     * Display specific mail
     */
    public function show($id)
    {
        $currentUser = auth()->user();
        if (!$currentUser) {
            abort(403);
        }
        $mail = InternalMail::with(['sender', 'readReceipts.user'])->findOrFail($id);

        // Check if user is recipient
        if (
            !in_array('all', $mail->recipient_ids) &&
            !in_array((string) $currentUser->id, $mail->recipient_ids)
        ) {
            abort(403, 'Unauthorized');
        }

        // Create read receipt if not exists
        if (!$mail->isReadBy($currentUser->id)) {
            MailReadReceipt::create([
                'mail_id' => $mail->id,
                'user_id' => $currentUser->id,
                'read_at' => now(),
            ]);
        }

        return view('internal-mails.show', compact('mail'));
    }

    /**
     * Delete mail
     */
    public function destroy($id)
    {
        $currentUser = auth()->user();
        if (!$currentUser) {
            abort(403);
        }
        $mail = InternalMail::findOrFail($id);

        // Only sender or admin can delete
        if ($mail->sender_id !== $currentUser->id && $currentUser->role !== 'Admin') {
            abort(403, 'Unauthorized');
        }

        // Delete attachment if exists
        if ($mail->attachment_path) {
            Storage::disk('public')->delete($mail->attachment_path);
        }

        $mail->delete();

        return redirect()->route('internal-mails.index')
            ->with('success', 'Mail deleted successfully');
    }

    private function sanitizeText(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        return strip_tags($value);
    }
}
