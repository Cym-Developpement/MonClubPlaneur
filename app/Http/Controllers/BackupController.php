<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class BackupController extends Controller
{
    private string $backupPath;

    public function __construct()
    {
        $this->backupPath = storage_path('backups');
    }

    public function index()
    {
        $files = [];

        if (is_dir($this->backupPath)) {
            foreach (scandir($this->backupPath) as $item) {
                if (!str_ends_with($item, '.zip')) {
                    continue;
                }
                $path = $this->backupPath . '/' . $item;
                $bytes = filesize($path);
                if ($bytes >= 1073741824) {
                    $size = number_format($bytes / 1073741824, 2) . ' Go';
                } elseif ($bytes >= 1048576) {
                    $size = number_format($bytes / 1048576, 2) . ' Mo';
                } elseif ($bytes >= 1024) {
                    $size = number_format($bytes / 1024, 2) . ' Ko';
                } else {
                    $size = $bytes . ' o';
                }
                $files[] = [
                    'name' => $item,
                    'size' => $size,
                    'mtime' => filemtime($path),
                ];
            }
        }

        usort($files, fn($a, $b) => $b['mtime'] - $a['mtime']);

        return view('admin.backups', compact('files'));
    }

    public function create()
    {
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }

        $filename = 'sauvegarde_' . date('Y-m-d_H-i-s') . '.zip';
        $zipPath  = $this->backupPath . '/' . $filename;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            return back()->with('error', 'Impossible de créer le fichier de sauvegarde.');
        }

        // Contenu du storage applicatif (hors dossier backups)
        $this->addDirectoryToZip($zip, storage_path('app'), 'storage/app');

        // Base de données SQLite (fichier brut)
        $dbPath = database_path('database.sqlite');
        if (file_exists($dbPath)) {
            $zip->addFile($dbPath, 'database/database.sqlite');
        }

        $zip->close();

        return back()->with('success', "Sauvegarde créée : {$filename}");
    }

    public function download(string $filename)
    {
        $filename = basename($filename);
        $path = $this->backupPath . '/' . $filename;

        if (!file_exists($path) || !str_ends_with($filename, '.zip')) {
            abort(404);
        }

        return response()->download($path);
    }

    public function destroy(string $filename)
    {
        $filename = basename($filename);
        $path = $this->backupPath . '/' . $filename;

        if (!file_exists($path) || !str_ends_with($filename, '.zip')) {
            abort(404);
        }

        unlink($path);

        return back()->with('success', "Sauvegarde supprimée : {$filename}");
    }

    private function addDirectoryToZip(ZipArchive $zip, string $dirPath, string $zipBasePath): void
    {
        if (!is_dir($dirPath)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $realPath     = $file->getRealPath();
            $relativePath = $zipBasePath . '/' . substr($realPath, strlen($dirPath) + 1);
            $zip->addFile($realPath, $relativePath);
        }
    }
}
