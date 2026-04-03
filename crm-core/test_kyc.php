<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$kycMandatory = App\Models\SystemSetting::get('kyc_mandatory', '0');
$p = App\Models\Payment::find(4);
$lead = $p->lead;

echo "KYC Mandatory? " . $kycMandatory . "\n";
echo "Lead ID: " . $lead->id . "\n";
echo "Lead is_kyc_completed: " . $lead->is_kyc_completed . "\n";
echo "Lead is_rpm_completed: " . $lead->is_rpm_completed . "\n";

if ($kycMandatory === '1' && (!$lead->is_kyc_completed || !$lead->is_rpm_completed)) {
    echo "BLOCKED BY KYC/RPM!\n";
} else {
    echo "VERIFY WILL PROCEED.\n";
}
