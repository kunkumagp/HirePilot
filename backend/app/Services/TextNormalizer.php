<?php

declare(strict_types=1);

namespace App\Services;

final class TextNormalizer
{
    public function toPlainText(string $html): string
    {
        $spaced = (string) preg_replace('#</?(?:h[1-6]|p|li|ul|ol|div|br|tr|td)>#i', ' ', $html);
        $decoded = html_entity_decode(strip_tags($spaced), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim((string) preg_replace('/\s+/u', ' ', $decoded));
    }
}
