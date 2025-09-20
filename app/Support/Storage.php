<?php

namespace App\Support;

class Storage
{
    public static function storagePath(string $relative = ''): string
    {
        $base = Env::projectRoot() . DIRECTORY_SEPARATOR . 'storage';
        if ($relative === '') return $base;
        return $base . DIRECTORY_SEPARATOR . ltrim($relative, '\\/');
    }

    /**
     * Read JSON file into array; returns [] on missing/invalid.
     * @return array<mixed>
     */
    public static function readJson(string $relativeFile): array
    {
        $path = self::storagePath($relativeFile);
        if (!is_file($path)) return [];
        $raw = file_get_contents($path);
        $data = json_decode($raw ?: '[]', true);
        return is_array($data) ? $data : [];
    }

    /**
     * Write array to JSON file (pretty, unescaped unicode). Ensures directory exists.
     */
    public static function writeJson(string $relativeFile, array $data): void
    {
        $path = self::storagePath($relativeFile);
        $dir = dirname($path);
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
