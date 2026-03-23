<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LogViewerController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user || !$user->hasPermission('system', 'logs')) {
            abort(403);
        }

        $logFile = storage_path('logs/laravel.log');
        $logs = [];

        if (file_exists($logFile)) {
            // Read last 2 MB of log file to avoid memory issues
            $content = file_get_contents($logFile, false, null, max(0, filesize($logFile) - 2000000));

            // Split by regex looking for standard Laravel log dates
            preg_match_all('/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\].*/', $content, $matches);

            // Reverse to show newest first
            $logs = array_reverse($matches[0]);

            // Limit to last 100 entries
            $logs = array_slice($logs, 0, 100);
        }

        return view('admin.logs', compact('logs'));
    }

    public function clear()
    {
        $user = auth()->user();
        if (!$user || !$user->hasPermission('system', 'logs')) {
            abort(403);
        }

        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            file_put_contents($logFile, '');
        }
        return redirect()->back()->with('success', 'Logs cleared successfully.');
    }
}
