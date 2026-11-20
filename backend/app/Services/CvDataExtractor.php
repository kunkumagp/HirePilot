<?php

declare(strict_types=1);

namespace App\Services;

final class CvDataExtractor
{
    /** @var array<string, string> */
    private const HEADINGS = [
        'summary' => '/^(professional\s+)?(summary|profile|about\s+me|objective|overview)\b/i',
        'skills' => '/^(technical\s+|core\s+)?(skills|competencies|expertise|technologies)\b/i',
        'experience' => '/^(professional\s+|work\s+)?(experience|employment\s+history|career\s+history)\b/i',
        'education' => '/^(education|academic\s+(background|qualifications))\b/i',
        'projects' => '/^(projects|selected\s+projects)\b/i',
        'certifications' => '/^(certifications?|licenses?)\b/i',
        'languages' => '/^(languages?)\b/i',
        'awards' => '/^(awards?|honou?rs?)\b/i',
    ];

    /**
     * Rule-based extraction of common CV sections from plain text.
     *
     * @return array<string, mixed>
     */
    public function extract(string $text): array
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $lines = array_values(array_filter(
            array_map(fn (string $line): string => trim($line), explode("\n", $text)),
            fn (string $line): bool => $line !== '',
        ));

        $result = [
            'name' => null,
            'email' => null,
            'phone' => null,
            'summary' => '',
            'skills' => [],
            'experience' => '',
            'education' => '',
            'projects' => '',
            'certifications' => '',
            'languages' => '',
            'awards' => '',
        ];

        if (preg_match('/[\w.+-]+@[\w.-]+\.[a-z]{2,}/i', $text, $matches)) {
            $result['email'] = $matches[0];
        }

        if (preg_match('/(?:\+?\d[\d\s().-]{7,}\d)/', $text, $matches)) {
            $result['phone'] = trim($matches[0]);
        }

        $current = null;

        foreach ($lines as $line) {
            $matched = null;

            foreach (self::HEADINGS as $key => $pattern) {
                if (preg_match($pattern, $line)) {
                    $matched = $key;
                    break;
                }
            }

            if ($matched !== null) {
                $current = $matched;

                continue;
            }

            if ($result['name'] === null && $this->looksLikeName($line)) {
                $result['name'] = $line;

                continue;
            }

            if ($current === null) {
                continue;
            }

            if ($current === 'skills') {
                $parts = preg_split('/[,;|•·]+/', $line) ?: [];

                foreach ($parts as $part) {
                    $part = trim($part);

                    if ($part !== '') {
                        $result['skills'][] = $part;
                    }
                }

                continue;
            }

            $result[$current] = $result[$current] === ''
                ? $line
                : $result[$current]."\n".$line;
        }

        $result['skills'] = array_values(array_unique($result['skills']));

        return $result;
    }

    private function looksLikeName(string $line): bool
    {
        if (str_contains($line, '@') || preg_match('/\d/', $line)) {
            return false;
        }

        $words = preg_split('/\s+/', $line) ?: [];

        return count($words) >= 2 && count($words) <= 5;
    }
}
