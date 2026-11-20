<?php

declare(strict_types=1);

namespace App\Services;

use Dompdf\Dompdf;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

/**
 * Generates a CV in the user's preferred format:
 * 0.5" margins, 16pt bold name, 12pt bold uppercase headings, 10pt body.
 */
final class AtsCvGenerator
{
    /** @var list<string> */
    private const SECTIONS = [
        'summary' => 'SUMMARY',
        'skills' => 'CORE TECHNICAL SKILLS',
        'experience' => 'PROFESSIONAL EXPERIENCE',
        'education' => 'EDUCATION & CERTIFICATIONS',
        'projects' => 'PROJECTS',
        'languages' => 'LANGUAGES',
        'awards' => 'AWARDS & HONOURS',
    ];

    /**
     * @param  array<string, mixed>  $data
     */
    public function toDocx(array $data): string
    {
        $word = new PhpWord;
        $section = $word->addSection([
            'marginTop' => 720,
            'marginBottom' => 720,
            'marginLeft' => 720,
            'marginRight' => 720,
        ]);

        $section->addText(
            mb_strtoupper((string) ($data['name'] ?? 'Your Name')),
            ['bold' => true, 'size' => 16],
        );

        foreach ([$data['tagline'] ?? '', $data['location'] ?? ''] as $line) {
            if ($this->stringValue($line) !== '') {
                $section->addText($this->stringValue($line), ['size' => 10]);
            }
        }

        $contact = $this->contactLine($data);

        if ($contact !== '') {
            $section->addText($contact, ['size' => 10]);
        }

        foreach (self::SECTIONS as $key => $heading) {
            $value = $this->stringValue($data[$key] ?? '');

            if ($value === '') {
                continue;
            }

            $section->addText($heading, ['bold' => true, 'size' => 12]);

            foreach (explode("\n", $value) as $line) {
                if (trim($line) !== '') {
                    $section->addText(trim($line), ['size' => 10]);
                }
            }
        }

        $path = (string) tempnam(sys_get_temp_dir(), 'hirepilot_ats_');
        IOFactory::createWriter($word, 'Word2007')->save($path);

        return $path;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function toPdf(array $data): string
    {
        $html = '<html><head><meta charset="utf-8"><style>
            @page { margin: 0.5in; }
            body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; line-height: 1.15; color: #111; }
            h1 { font-size: 16pt; font-weight: bold; margin: 0 0 2pt; }
            .line { margin: 0 0 2pt; }
            h2 { font-size: 12pt; font-weight: bold; margin: 10pt 0 3pt; }
            p { margin: 0 0 3pt; }
        </style></head><body>';

        $html .= '<h1>'.e(mb_strtoupper((string) ($data['name'] ?? 'Your Name'))).'</h1>';

        foreach ([$data['tagline'] ?? '', $data['location'] ?? ''] as $line) {
            if ($this->stringValue($line) !== '') {
                $html .= '<div class="line">'.e($this->stringValue($line)).'</div>';
            }
        }

        $contact = $this->contactLine($data);

        if ($contact !== '') {
            $html .= '<div class="line">'.e($contact).'</div>';
        }

        foreach (self::SECTIONS as $key => $heading) {
            $value = $this->stringValue($data[$key] ?? '');

            if ($value === '') {
                continue;
            }

            $html .= '<h2>'.e($heading).'</h2>';
            $html .= '<p>'.e($value).'</p>';
        }

        $html .= '</body></html>';

        $dompdf = new Dompdf;
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $path = (string) tempnam(sys_get_temp_dir(), 'hirepilot_ats_');
        file_put_contents($path, $dompdf->output());

        return $path;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function contactLine(array $data): string
    {
        $parts = [];

        foreach (['phone' => 'Phone', 'email' => 'Email', 'linkedin' => 'LinkedIn', 'github' => 'Github'] as $key => $label) {
            $value = $this->stringValue($data[$key] ?? '');

            if ($value !== '') {
                $parts[] = $label.': '.$value;
            }
        }

        return implode(' | ', $parts);
    }

    private function stringValue(mixed $value): string
    {
        if (is_array($value)) {
            return trim(implode(', ', array_map('strval', $value)));
        }

        return trim((string) $value);
    }
}
