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
            $rawContent = $this->readRaw(storage_path('logs/update.log'));
            return view('admin.audit', compact('type', 'rawContent'))
                ->with(['dates' => [], 'selectedDate' => null, 'search' => '', 'lines' => []]);
        }

        if ($type === 'error') {
            $this->authorize('admin:super');
            $rawContent = $this->readRaw(storage_path('logs/laravel.log'));
            return view('admin.audit', compact('type', 'rawContent'))
                ->with(['dates' => [], 'selectedDate' => null, 'search' => '', 'lines' => []]);
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

    private function readRaw(string $path): string
    {
        if (! file_exists($path)) {
            return '';
        }

        return file_get_contents($path) ?: '';
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
