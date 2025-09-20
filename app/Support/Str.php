<?php

namespace App\Support;

class Str
{
    public static function slug(string $text): string
    {
        $text = preg_replace('~[^\\pL\\d]+~u', '-', $text) ?? $text;
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        if ($converted !== false) {
            $text = $converted;
        }
        $text = preg_replace('~[^-\\w]+~', '', $text) ?? $text;
        $text = trim($text, '-');
        $text = strtolower($text);
        if ($text === '') {
            $text = 'category-' . substr(md5(uniqid('', true)), 0, 8);
        }
        return $text;
    }
}
