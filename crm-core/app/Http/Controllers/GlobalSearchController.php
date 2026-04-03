<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['Admin', 'Manager', 'SBA', 'BA'])) {
            abort(403);
        }

        $query = trim((string) $request->input('q', ''));
        if ($query === '') {
            return response()->json([]);
        }

        $results = [];
        $queryLower = strtolower($query);

        // 1. Static Pages & Quick Actions
        $pages = [
            ['title' => 'Dashboard', 'url' => route('dashboard'), 'type' => 'Page', 'icon' => 'home'],
            ['title' => 'Leads Management', 'url' => route('leads.index'), 'type' => 'Page', 'icon' => 'users'],
            ['title' => 'Create New Lead', 'url' => route('leads.index') . '?action=create', 'type' => 'Action', 'icon' => 'plus-circle'],
            ['title' => 'Client Directory', 'url' => route('clients.index'), 'type' => 'Page', 'icon' => 'briefcase'],
            ['title' => 'Revenue Dashboard', 'url' => route('payments.index'), 'type' => 'Page', 'icon' => 'currency-rupee'],
            ['title' => 'Sales Ledger', 'url' => route('payments.sales-orders'), 'type' => 'Page', 'icon' => 'document-chart-bar'],
            ['title' => 'Reports & Analytics', 'url' => route('reports.index'), 'type' => 'Page', 'icon' => 'chart-bar'],
            ['title' => 'Market Calls', 'url' => route('advisory-calls.index'), 'type' => 'Page', 'icon' => 'phone'],
        ];

        foreach ($pages as $page) {
            if (str_contains(strtolower($page['title']), $queryLower)) {
                $results[] = [
                    'title'    => $page['title'],
                    'subtitle' => 'System ' . $page['type'],
                    'url'      => $page['url'],
                    'type'     => 'page',
                    'icon'     => $page['icon']
                ];
            }
        }

        // 2. Leads & Clients (Database Search)
        $leads = Lead::where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('mobile', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->when($user->role === 'BA', function ($q) use ($user) {
                $q->where('assigned_to', $user->id);
            })
            ->when(in_array($user->role, ['SBA', 'Manager'], true), function ($q) use ($user) {
                $q->whereIn('assigned_to', $user->getAllTeamIds());
            })
            ->with('assignee:id,name')
            ->limit(8)
            ->get(['id', 'name', 'mobile', 'email', 'status', 'assigned_to']);

        foreach ($leads as $lead) {
            $isClient   = $lead->status === 'Paid Client';
            $agentName  = $lead->assignee?->name ?? 'Unassigned';
            $statusLabel = $lead->status ?? 'Cold Lead';
            $typeLabel   = $isClient ? 'Client' : 'Lead';

            $results[] = [
                'title'    => $lead->name,
                'subtitle' => "{$typeLabel} · {$lead->mobile} · {$statusLabel} · Agent: {$agentName}",
                'url'      => $isClient ? route('clients.show', $lead->id) : route('leads.show', $lead->id),
                'type'     => $isClient ? 'client' : 'lead',
                'icon'     => $isClient ? 'star' : 'user',
                'status'   => $statusLabel,
                'agent'    => $agentName,
                'mobile'   => $lead->mobile,
            ];
        }

        return response()->json($results);
    }
}
