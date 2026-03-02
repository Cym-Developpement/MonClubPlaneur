<?php

namespace App\Services;

use App\Models\flight;
use App\Models\transaction;
use App\Models\User;
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

        // CSV transactions et vols par utilisateur
        $users = User::orderBy('name')->get();
        foreach ($users as $user) {
            $slug = $this->userSlug($user->id, $user->name);

            $zip->addFromString(
                "transactions/{$slug}.csv",
                $this->buildTransactionsCsv($user->id)
            );

            $zip->addFromString(
                "flights/{$slug}.csv",
                $this->buildFlightsCsv($user->id)
            );
        }

        $zip->close();

        return $filename;
    }

    private function userSlug(int $id, string $name): string
    {
        $clean = preg_replace('/[^a-z0-9]+/', '_', strtolower($name));
        return "{$id}_{$clean}";
    }

    private function buildTransactionsCsv(int $userId): string
    {
        $rows = transaction::where('idUser', $userId)
            ->orderBy('time')
            ->get();

        $lines = ["id;date;libelle;montant_eur;solde_eur;valide;observation"];

        foreach ($rows as $tr) {
            $lines[] = implode(';', [
                $tr->id,
                date('Y-m-d H:i:s', $tr->time),
                $this->escapeCsv($tr->name),
                number_format($tr->value / 100, 2, '.', ''),
                number_format($tr->solde, 2, '.', ''),
                $tr->valid ? '1' : '0',
                $this->escapeCsv($tr->observation ?? ''),
            ]);
        }

        return implode("\n", $lines);
    }

    private function buildFlightsCsv(int $userId): string
    {
        $rows = flight::where('idUser', $userId)
            ->orWhere('userPayId', $userId)
            ->orderBy('flightTimestamp')
            ->get();

        $lines = ["id;date;aeronef;pilote_id;payeur_id;instructeur_id;depart;arrivee;duree_min;type_lancement;atterrissages"];

        foreach ($rows as $f) {
            $lines[] = implode(';', [
                $f->id,
                date('Y-m-d', $f->flightTimestamp),
                $this->escapeCsv($f->aircraftId),
                $f->idUser,
                $f->userPayId,
                $f->idInstructor ?? '',
                $this->escapeCsv($f->airPortStartCode ?? ''),
                $this->escapeCsv($f->airPortEndCode ?? ''),
                $f->totalTime,
                $f->startType,
                $f->landing ?? '',
            ]);
        }

        return implode("\n", $lines);
    }

    private function escapeCsv(string $value): string
    {
        if (str_contains($value, ';') || str_contains($value, '"') || str_contains($value, "\n")) {
            return '"' . str_replace('"', '""', $value) . '"';
        }
        return $value;
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
