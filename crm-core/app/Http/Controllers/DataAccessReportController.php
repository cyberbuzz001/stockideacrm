<?php

namespace App\Http\Controllers;

use App\Models\DataAccessLog;
use App\Models\User;
use Illuminate\Http\Request;

class DataAccessReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['Admin', 'Manager'], true)) {
            abort(403);
        }

        $query = DataAccessLog::query()->with(['user', 'lead']);

        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->user_id);
        }
        if ($request->filled('field')) {
            $query->where('field', $request->field);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $users = User::orderBy('name')->get(['id', 'name', 'role']);

        return view('admin.data-access', compact('logs', 'users'));
    }
}
