<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class HelpSystem
{
    /**
     * Derive a safe file key from a URL path.
     * e.g. "admin/parametres" → "admin_parametres"
     */
    public static function keyFromPath(string $path): string
    {
        $key = str_replace('/', '_', $path);
        $key = preg_replace('/[^a-zA-Z0-9_-]/', '_', $key);
        $key = trim($key, '_');
        return $key ?: 'home';
    }

    /**
     * Absolute path to the markdown file for a given key.
     */
    public static function filePath(string $key): string
    {
        return base_path('Aide/' . $key . '.md');
    }

    /**
     * Create the file (empty) if it does not exist yet.
     */
    public static function ensureFile(string $key): void
    {
        $path = self::filePath($key);
        if (!file_exists($path)) {
            file_put_contents($path, '');
        }
    }

    /**
     * Return true if the file exists and has non-empty content.
     */
    public static function hasContent(string $key): bool
    {
        $path = self::filePath($key);
        return file_exists($path) && trim((string) file_get_contents($path)) !== '';
    }

    /**
     * Convert the markdown file to HTML.
     * Returns empty string if no content.
     */
    public static function toHtml(string $key): string
    {
        if (!self::hasContent($key)) {
            return '';
        }
        $md = (string) file_get_contents(self::filePath($key));
        return Str::markdown($md, [
            'html_input'         => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
}
