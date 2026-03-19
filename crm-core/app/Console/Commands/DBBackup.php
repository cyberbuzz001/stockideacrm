<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DBBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup the SQLite database to storage/app/backups';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = 'backup-' . now()->format('Y-m-d-H-i-s') . '.sqlite';
        $sourcePath = database_path('database.sqlite');
        $backupDir = storage_path('app/backups');
        $destPath = $backupDir . '/' . $filename;

        // Ensure backup directory exists
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        if (!file_exists($sourcePath)) {
            $this->error("Database file not found at: {$sourcePath}");
            return;
        }

        $this->info("Backing up database...");

        if (copy($sourcePath, $destPath)) {
            $this->info("Backup created successfully: {$filename}");

            // Prune old backups (Keep last 7)
            $files = glob($backupDir . '/*.sqlite');
            if (count($files) > 7) {
                // Sort by modified time (oldest first)
                array_multisort(array_map('filemtime', $files), SORT_ASC, $files);

                $filesToDelete = array_slice($files, 0, count($files) - 7);
                foreach ($filesToDelete as $file) {
                    unlink($file);
                    $this->info("Pruned old backup: " . basename($file));
                }
            }

        } else {
            $this->error("Failed to copy database file.");
        }
    }
}
