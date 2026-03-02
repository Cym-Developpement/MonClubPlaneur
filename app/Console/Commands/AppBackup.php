<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class AppBackup extends Command
{
    protected $signature   = 'app:backup';
    protected $description = 'Crée une sauvegarde (BDD + storage + logs)';

    public function handle()
    {
        $this->info('=== Sauvegarde — ' . now()->format('Y-m-d H:i:s') . ' ===');

        try {
            $filename = (new BackupService())->create();
            $this->info("✓ Sauvegarde créée : {$filename}");
            return 0;
        } catch (\RuntimeException $e) {
            $this->error('✗ ' . $e->getMessage());
            return 1;
        }
    }
}
