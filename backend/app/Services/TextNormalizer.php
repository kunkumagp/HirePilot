<?php

declare(strict_types=1);

namespace App\Services;

final class TextNormalizer
{
    /**
     * Convert HTML into readable plain text while keeping paragraph breaks.
     */
    public function toPlainText(string $html): string
    {
        $text = preg_replace('#</(?:p|div|h[1-6]|ul|ol|table|section)>#i', "\n\n", $html) ?? $html;
        $text = preg_replace('#<(?:ul|ol)[^>]*>#i', "\n", $text) ?? $text;
        $text = preg_replace('#</li>#i', "\n", $text) ?? $text;
        $text = preg_replace('#<li[^>]*>#i', '- ', $text) ?? $text;
        $text = preg_replace('#<br\s*/?>#i', "\n", $text) ?? $text;

        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[ \t]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/^[ \t]+|[ \t]+$/m', '', $text) ?? $text;
        $text = preg_replace('/\n{3,}/', "\n\n", $text) ?? $text;

        return trim($text);
    }
}
