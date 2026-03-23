<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $matrix = [
            'leads' => [
                'view_all'    => ['Admin', 'Manager'],
                'view_team'   => ['Team Leader'],
                'view_own'    => ['SBA', 'BA'],
                'create'      => ['Admin', 'Manager', 'Team Leader', 'SBA', 'BA'],
                'edit_any'    => ['Admin', 'Manager'],
                'delete'      => ['Admin'],
                'export'      => ['Admin'], // Specific restriction
                'reassign'    => ['Admin', 'Manager', 'Team Leader'],
            ],
            'clients' => [
                'view_all'    => ['Admin', 'Manager'],
                'view_team'   => ['Team Leader'],
                'view_own'    => ['SBA', 'BA'],
                'edit'        => ['Admin', 'Manager', 'Team Leader'],
                'payments'    => ['Admin', 'Manager'],
                'kyc'         => ['Admin', 'Manager', 'Team Leader'],
            ],
            'market_calls' => [
                'publish'     => ['Admin', 'Manager'],
                'edit_delete' => ['Admin', 'Manager'],
                'view_all'    => ['Admin', 'Manager', 'Team Leader', 'SBA', 'BA'],
                'send_trial'  => ['Admin', 'Manager', 'Team Leader', 'SBA', 'BA'],
            ],
            'performance' => [
                'view_company' => ['Admin'],
                'view_team'    => ['Manager', 'Team Leader'],
                'view_own'     => ['SBA', 'BA'],
                'download'     => ['Admin', 'Manager'],
            ],
            'team' => [
                'manage'      => ['Admin', 'Manager'],
                'view_att'    => ['Admin', 'Manager', 'Team Leader'],
            ],
            'compliance' => [
                'audit'       => ['Admin'],
                'view_kyc'    => ['Admin', 'Manager', 'Team Leader'],
                'complete_step' => ['Admin', 'Manager'],
            ],
            'ai_analysis' => [
                'view'    => ['Admin', 'Manager', 'Team Leader', 'SBA', 'BA'],
                'execute' => ['Admin', 'Manager', 'Team Leader', 'SBA', 'BA'],
            ],
            'system' => [
                'control_center' => ['Admin'],
                'logs'           => ['Admin'],
                'training_mgmt'  => ['Admin'],
            ]
        ];

        SystemSetting::set('role_access_matrix', json_encode($matrix));
    }
}
