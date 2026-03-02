<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'audit');

        if ($type === 'update') {
            $this->authorize('admin:super');
            $lines = $this->readSingleLog(storage_path('logs/update.log'), 'raw');
            return view('admin.audit', compact('lines', 'type'))
                ->with(['dates' => [], 'selectedDate' => null, 'search' => '']);
        }

        if ($type === 'error') {
            $this->authorize('admin:super');
            $lines = $this->readSingleLog(storage_path('logs/laravel.log'), 'monolog');
            return view('admin.audit', compact('lines', 'type'))
                ->with(['dates' => [], 'selectedDate' => null, 'search' => '']);
        }

        // type = audit (défaut)
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

        krsort($files);

        $dates        = array_keys($files);
        $selectedDate = $request->get('date', $dates[0] ?? null);
        $search       = trim($request->get('search', ''));

        $lines = [];

        if ($search !== '') {
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

        return view('admin.audit', compact('dates', 'selectedDate', 'lines', 'search', 'type'));
    }

    private function readSingleLog(string $path, string $format): array
    {
        if (! file_exists($path)) {
            return [];
        }

        $raw   = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lines = [];

        foreach (array_reverse($raw) as $line) {
            if ($format === 'monolog') {
                $lines[] = $this->parseLine($line);
            } else {
                $lines[] = ['time' => '', 'level' => 'info', 'message' => $line];
            }
        }

        return $lines;
    }

    private function parseLine(string $line, ?string $datePrefix = null): array
    {
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
