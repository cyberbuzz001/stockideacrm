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
        $mode = 'global'; // global, action (?), nav (>), doc (/)

        if (str_starts_with($query, '?')) {
            $mode = 'action';
            $queryValue = trim(substr($query, 1));
        } elseif (str_starts_with($query, '>')) {
            $mode = 'nav';
            $queryValue = trim(substr($query, 1));
        } elseif (str_starts_with($query, '/')) {
            $mode = 'doc';
            $queryValue = trim(substr($query, 1));
        } else {
            $queryValue = $query;
        }

        $queryValueLower = strtolower($queryValue);

        // 1. Navigation & Quick Actions (Mode: nav or global)
        if ($mode === 'global' || $mode === 'nav') {
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
                if ($queryValue === '' || str_contains(strtolower($page['title']), $queryValueLower)) {
                    $results[] = [
                        'title'    => $page['title'],
                        'subtitle' => 'System ' . $page['type'],
                        'url'      => $page['url'],
                        'type'     => 'page',
                        'icon'     => $page['icon']
                    ];
                }
            }
        }

        // 2. Leads & Clients (Mode: action or global)
        if ($mode === 'global' || $mode === 'action') {
            if ($queryValue !== '') {
                $leads = Lead::where(function ($q) use ($queryValue) {
                        $q->where('name', 'like', "%{$queryValue}%")
                          ->orWhere('mobile', 'like', "%{$queryValue}%");
                    })
                    ->when($user->role === 'BA', function ($q) use ($user) {
                        $q->where('assigned_to', $user->id);
                    })
                    ->with('assignee:id,name')
                    ->limit($mode === 'action' ? 5 : 8)
                    ->get(['id', 'name', 'mobile', 'status', 'assigned_to']);

                foreach ($leads as $lead) {
                    $isClient   = $lead->status === 'Paid Client';
                    $agentName  = $lead->assignee?->name ?? 'Unassigned';
                    
                    if ($mode === 'action') {
                        // WhatsApp Action
                        $results[] = [
                            'title'    => "WhatsApp " . $lead->name,
                            'subtitle' => "Action: Open Chat (91{$lead->mobile})",
                            'url'      => "https://wa.me/91" . preg_replace('/[^0-9]/', '', $lead->mobile),
                            'type'     => 'action',
                            'icon'     => 'phone',
                            'is_wa'    => true
                        ];
                        // Call Action
                        $results[] = [
                            'title'    => "Call " . $lead->name,
                            'subtitle' => "Action: Trigger Dialler ({$lead->mobile})",
                            'url'      => "tel:" . $lead->mobile,
                            'type'     => 'action',
                            'icon'     => 'phone'
                        ];
                    } else {
                        $results[] = [
                            'title'    => $lead->name,
                            'subtitle' => "{$lead->mobile} · {$lead->status} · Agent: {$agentName}",
                            'url'      => $isClient ? route('clients.show', $lead->id) : route('leads.show', $lead->id),
                            'type'     => $isClient ? 'client' : 'lead',
                            'icon'     => $isClient ? 'star' : 'user',
                            'status'   => $lead->status,
                            'agent'    => $agentName,
                            'mobile'   => $lead->mobile,
                        ];
                    }
                }
            }
        }

        // 3. Documents (Mode: doc)
        if ($mode === 'doc' && $queryValue !== '') {
            $docs = \App\Models\LeadDocument::where('original_name', 'like', "%{$queryValue}%")
                ->with('lead:id,name')
                ->limit(10)
                ->get();

            foreach ($docs as $doc) {
                $results[] = [
                    'title'    => $doc->original_name,
                    'subtitle' => "Document for Lead: " . ($doc->lead->name ?? 'Unknown'),
                    'url'      => route('leads.documents.download', [$doc->lead_id, $doc->id]),
                    'type'     => 'page',
                    'icon'     => 'document-chart-bar'
                ];
            }
        }

        return response()->json($results);
    }
}
