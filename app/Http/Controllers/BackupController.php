<?php

namespace App\Http\Controllers;

use App\Services\BackupService;

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
                if (! str_ends_with($item, '.zip')) {
                    continue;
                }
                $path  = $this->backupPath . '/' . $item;
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
                    'name'  => $item,
                    'size'  => $size,
                    'mtime' => filemtime($path),
                ];
            }
        }

        usort($files, fn($a, $b) => $b['mtime'] - $a['mtime']);

        return view('admin.backups', compact('files'));
    }

    public function create()
    {
        try {
            $filename = (new BackupService())->create();
            return back()->with('success', "Sauvegarde créée : {$filename}");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function download(string $filename)
    {
        $filename = basename($filename);
        $path     = $this->backupPath . '/' . $filename;

        if (! file_exists($path) || ! str_ends_with($filename, '.zip')) {
            abort(404);
        }

        return response()->download($path);
    }

    public function destroy(string $filename)
    {
        $filename = basename($filename);
        $path     = $this->backupPath . '/' . $filename;

        if (! file_exists($path) || ! str_ends_with($filename, '.zip')) {
            abort(404);
        }

        unlink($path);

        return back()->with('success', "Sauvegarde supprimée : {$filename}");
    }
}
