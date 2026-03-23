<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    public function index()
    {
        $matrix = json_decode(SystemSetting::get('role_access_matrix', '{}'), true);
        
        $roles = ['Admin', 'Manager', 'Team Leader', 'SBA', 'BA', 'Client'];
        $modules = [
            'Leads' => [
                'view_all' => 'View all leads (company-wide)',
                'view_team' => 'View team leads only',
                'view_own' => 'View own leads only',
                'create' => 'Create new leads',
                'edit_any' => 'Edit any lead',
                'delete' => 'Delete leads',
                'export' => 'Export leads to CSV',
                'reassign' => 'Reassign leads',
            ],
            'Clients' => [
                'view_all' => 'View all clients',
                'view_team' => 'View team clients',
                'view_own' => 'View own clients only',
                'edit' => 'Edit client details',
                'payments' => 'View payment history',
                'kyc' => 'Download KYC documents',
            ],
            'Market Calls' => [
                'publish' => 'Publish market calls',
                'edit_delete' => 'Edit/delete calls',
                'view_all' => 'View all calls',
                'send_trial' => 'Send trial call to lead',
            ],
            'Performance' => [
                'view_company' => 'View company-wide reports',
                'view_team' => 'View team reports',
                'view_own' => 'View own stats only',
                'download' => 'Download reports',
            ],
            'Team' => [
                'manage' => 'Add/remove members',
                'view_att' => 'View team attendance',
            ],
            'Compliance' => [
                'audit' => 'Access audit trail',
                'view_kyc' => 'View KYC documents',
            ]
        ];

        return view('admin.roles.index', compact('matrix', 'roles', 'modules'));
    }

    public function update(Request $request)
    {
        $matrix = $request->input('matrix', []);
        SystemSetting::set('role_access_matrix', json_encode($matrix));

        return redirect()->back()->with('success', 'Permissions matrix updated successfully.');
    }
}
