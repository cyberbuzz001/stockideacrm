<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

App\Models\SystemSetting::set('kyc_mandatory', '0');
App\Models\SystemSetting::flushCache();

echo "KYC Mandatory set to 0. \n";
$val = App\Models\SystemSetting::get('kyc_mandatory', 'N/A');
echo "Current value: " . $val . "\n";
