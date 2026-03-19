<?php

namespace App\Http\Controllers;

use App\Models\ClientConsent;
use Illuminate\Http\Request;

class ConsentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['Admin', 'Manager'], true)) {
            abort(403);
        }

        $query = ClientConsent::query()->with(['lead', 'user']);

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }
        if ($request->filled('purpose')) {
            $query->where('purpose', 'like', '%' . $request->purpose . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $consents = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('admin.consents', compact('consents'));
    }
}
