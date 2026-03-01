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
                // audit-YYYY-MM-DD.log
                if (preg_match('/audit-(\d{4}-\d{2}-\d{2})\.log$/', $filename, $m)) {
                    $files[$m[1]] = $path;
                }
            }
        }

        krsort($files); // plus récent en premier

        $dates = array_keys($files);
        $selectedDate = $request->get('date', $dates[0] ?? null);

        $lines = [];
        if ($selectedDate && isset($files[$selectedDate])) {
            $raw = file($files[$selectedDate], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach (array_reverse($raw) as $line) {
                // [2026-03-01 14:23:45] local.INFO: message [] []
                if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.(\w+): (.+?)(?:\s+\[\]\s*\[\])?$/', $line, $m)) {
                    $lines[] = [
                        'time'    => $m[1],
                        'level'   => strtolower($m[2]),
                        'message' => $m[3],
                    ];
                } else {
                    $lines[] = ['time' => '', 'level' => 'info', 'message' => $line];
                }
            }
        }

        return view('admin.audit', compact('dates', 'selectedDate', 'lines'));
    }
}
