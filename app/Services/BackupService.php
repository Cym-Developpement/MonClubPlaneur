<?php

namespace App\Services;

use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class BackupService
{
    private string $backupPath;

    public function __construct()
    {
        $this->backupPath = storage_path('backups');
    }

    public function create(): string
    {
        if (! is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }

        $filename = 'sauvegarde_' . date('Y-m-d_H-i-s') . '.zip';
        $zipPath  = $this->backupPath . '/' . $filename;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            throw new \RuntimeException('Impossible de créer le fichier ZIP : ' . $zipPath);
        }

        // Storage applicatif (hors backups)
        $this->addDirectoryToZip($zip, storage_path('app'), 'storage/app');

        // Logs d'audit
        $this->addDirectoryToZip($zip, storage_path('logs/audit'), 'storage/logs/audit');

        // Log de mise à jour
        $updateLog = storage_path('logs/update.log');
        if (file_exists($updateLog)) {
            $zip->addFile($updateLog, 'storage/logs/update.log');
        }

        // Base de données SQLite
        $dbPath = database_path('database.sqlite');
        if (file_exists($dbPath)) {
            $zip->addFile($dbPath, 'database/database.sqlite');
        }

        $zip->close();

        return $filename;
    }

    private function addDirectoryToZip(ZipArchive $zip, string $dirPath, string $zipBasePath): void
    {
        if (! is_dir($dirPath)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }
            $realPath     = $file->getRealPath();
            $relativePath = $zipBasePath . '/' . substr($realPath, strlen($dirPath) + 1);
            $zip->addFile($realPath, $relativePath);
        }
    }
}
