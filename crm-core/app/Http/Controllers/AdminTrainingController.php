<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminTrainingController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'Admin') {
            abort(403);
        }

        $modules = \App\Models\TrainingModule::latest()->get();
        $teamCounts = \App\Models\User::select('role', DB::raw('COUNT(*) as total'))
            ->whereIn('role', ['BA', 'SBA'])
            ->groupBy('role')
            ->pluck('total', 'role');
        $allCount = (int) ($teamCounts['BA'] ?? 0) + (int) ($teamCounts['SBA'] ?? 0);

        $completedCounts = \App\Models\TrainingLog::select('module_id', DB::raw('COUNT(*) as total'))
            ->where('completion_status', 'completed')
            ->groupBy('module_id')
            ->pluck('total', 'module_id');

        // Also get completion stats
        foreach ($modules as $module) {
            $totalAssigned = 0;
            if ($module->target_team === 'All') {
                $totalAssigned = $allCount;
            } else {
                $totalAssigned = (int) ($teamCounts[$module->target_team] ?? 0);
            }

            $completed = (int) ($completedCounts[$module->id] ?? 0);

            $module->completion_percentage = $totalAssigned > 0 ? round(($completed / $totalAssigned) * 100) : 0;
            $module->total_assigned = $totalAssigned;
            $module->completed_count = $completed;
        }

        return view('admin.training.index', compact('modules'));
    }

    public function upload(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'Admin') {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,mp4,webm,mov|max:50000', // 50MB max
            'target_team' => 'required|in:All,SBA,BA',
            'schedule_type' => 'required|in:Weekly,Monthly',
            'deadline_date' => 'nullable|date',
        ]);

        $file = $request->file('file');
        $safeBase = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $ext = strtolower($file->getClientOriginalExtension());
        $fileName = time() . '_' . ($safeBase !== '' ? $safeBase : 'training') . '.' . $ext;
        $filePath = $file->storeAs('training_modules', $fileName, 'public');

        $extension = $ext;
        $fileType = 'unknown';
        if (in_array($extension, ['pdf'])) {
            $fileType = 'pdf';
        } elseif (in_array($extension, ['jpg', 'jpeg', 'png'])) {
            $fileType = 'image';
        } elseif (in_array($extension, ['mp4', 'webm', 'mov'])) {
            $fileType = 'video';
        }

        \App\Models\TrainingModule::create([
            'title' => $request->title,
            'file_path' => $filePath,
            'file_type' => $fileType,
            'target_team' => $request->target_team,
            'schedule_type' => $request->schedule_type,
            'deadline_date' => $request->deadline_date,
        ]);

        return redirect()->back()->with('success', 'Training module uploaded successfully.');
    }
    
    public function destroy(\App\Models\TrainingModule $module)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'Admin') {
            abort(403);
        }

        if (Storage::disk('public')->exists($module->file_path)) {
            Storage::disk('public')->delete($module->file_path);
        }
        $module->delete();
        return redirect()->back()->with('success', 'Training module deleted successfully.');
    }
}
