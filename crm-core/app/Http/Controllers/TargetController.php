<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserTarget;
use Illuminate\Http\Request;

class TargetController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['Admin', 'Manager'])) {
            abort(403);
        }

        $users = User::where('role', '!=', 'Admin')->get();
        $monthYear = now()->format('Y-m');

        return view('targets.index', compact('users', 'monthYear'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['Admin', 'Manager'])) {
            abort(403);
        }

        $request->validate([
            'targets' => 'required|array',
            'targets.*.user_id' => 'required|exists:users,id',
            'targets.*.amount' => 'required|numeric|min:0',
            'month_year' => 'required|string',
        ]);

        foreach ($request->targets as $targetData) {
            UserTarget::updateOrCreate(
                [
                    'user_id' => $targetData['user_id'],
                    'month_year' => $request->month_year,
                ],
                ['amount' => $targetData['amount']]
            );
        }

        return back()->with('success', 'Targets updated successfully!');
    }
}
