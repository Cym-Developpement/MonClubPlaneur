<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $dir = storage_path('logs/audit');

        $files = [];
        if (is_dir($dir)) {
            foreach (glob($dir . '/audit-*.log') as $path) {
                $filename = basename($path);
                if (preg_match('/audit-(\d{4}-\d{2}-\d{2})\.log$/', $filename, $m)) {
                    $files[$m[1]] = $path;
                }
            }
        }

        krsort($files); // plus récent en premier

        $dates        = array_keys($files);
        $selectedDate = $request->get('date', $dates[0] ?? null);
        $search       = trim($request->get('search', ''));

        $lines = [];

        if ($search !== '') {
            // Recherche dans tous les fichiers, du plus récent au plus ancien
            foreach ($files as $date => $path) {
                $raw = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach (array_reverse($raw) as $line) {
                    if (stripos($line, $search) === false) {
                        continue;
                    }
                    $lines[] = $this->parseLine($line, $date);
                }
            }
        } elseif ($selectedDate && isset($files[$selectedDate])) {
            $raw = file($files[$selectedDate], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach (array_reverse($raw) as $line) {
                $lines[] = $this->parseLine($line);
            }
        }

        return view('admin.audit', compact('dates', 'selectedDate', 'lines', 'search'));
    }

    private function parseLine(string $line, ?string $datePrefix = null): array
    {
        // [2026-03-01 14:23:45] local.INFO: message [] []
        if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.(\w+): (.+?)(?:\s+\[\]\s*\[\])?$/', $line, $m)) {
            return [
                'time'    => $m[1],
                'level'   => strtolower($m[2]),
                'message' => $m[3],
            ];
        }

        return [
            'time'    => $datePrefix ?? '',
            'level'   => 'info',
            'message' => $line,
        ];
    }
}
