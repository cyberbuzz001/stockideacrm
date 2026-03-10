<?php
// Simple MySQL dump backup script. Configure creds via env or defaults.

$host = getenv('CRM_DB_HOST') ?: 'localhost';
$user = getenv('CRM_DB_USER') ?: 'root';
$pass = getenv('CRM_DB_PASS') ?: '';
$name = getenv('CRM_DB_NAME') ?: 'crm';

$backupDir = __DIR__ . '/data';
if (!is_dir($backupDir)) {
    @mkdir($backupDir, 0775, true);
}

$filename = $backupDir . '/crm_backup_' . date('Ymd_His') . '.sql';

$cmd = sprintf(
    'mysqldump -h%s -u%s %s %s > %s',
    escapeshellarg($host),
    escapeshellarg($user),
    $pass !== '' ? ('-p' . escapeshellarg($pass)) : '',
    escapeshellarg($name),
    escapeshellarg($filename)
);

// Run the command
@exec($cmd, $out, $ret);
if ($ret !== 0) {
    echo "Backup failed\n";
    exit(1);
}
echo "Backup created: " . $filename . "\n";
?>


