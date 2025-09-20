<?php

namespace App\Support;

/**
 * Lightweight .env loader with getenv() fallback.
 */
class Env
{
    /** @var array<string,string> */
    private static array $cache = [];

    /**
     * Load key=>value pairs from project .env file (one level up from pages/).
     * Caches results after first read.
     *
     * @return array<string,string>
     */
    public static function load(): array
    {
        if (!empty(self::$cache)) {
            return self::$cache;
        }

        $root = self::projectRoot();
        $envFile = $root . DIRECTORY_SEPARATOR . '.env';
        $vars = [];

        if (is_file($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }
                if (!str_contains($line, '=')) {
                    continue;
                }
                [$k, $v] = explode('=', $line, 2);
                $k = trim($k);
                $v = trim($v, " \"'\t\r\n");
                if ($k !== '') {
                    $vars[$k] = $v;
                }
            }
        }

        self::$cache = $vars;
        return $vars;
    }

    /**
     * Get env value by name checking getenv() first then .env cache.
     */
    public static function get(string $name, ?string $default = null): ?string
    {
        $fromOs = getenv($name);
        if ($fromOs !== false && $fromOs !== null) {
            return $fromOs;
        }
        $vars = self::load();
        return $vars[$name] ?? $default;
    }

    /**
     * Resolve project root by walking up from this file to repo root.
     */
    public static function projectRoot(): string
    {
        // app/Support/Env.php => project root is three levels up from this file
        return dirname(__DIR__, 2);
    }
}
