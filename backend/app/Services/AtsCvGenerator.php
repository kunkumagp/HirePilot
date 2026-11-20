<?php

declare(strict_types=1);

namespace App\Services;

use Dompdf\Dompdf;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

final class AtsCvGenerator
{
    /** @var list<string> */
    private const SECTIONS = [
        'summary',
        'skills',
        'experience',
        'education',
        'projects',
        'certifications',
        'languages',
        'awards',
    ];

    /**
     * @param  array<string, mixed>  $data
     */
    public function toDocx(array $data): string
    {
        $word = new PhpWord;
        $section = $word->addSection();

        $section->addText((string) ($data['name'] ?? 'Your Name'), ['bold' => true, 'size' => 20]);

        $contact = array_filter([
            $data['email'] ?? null,
            $data['phone'] ?? null,
        ]);

        if ($contact !== []) {
            $section->addText(implode(' | ', $contact), ['size' => 10]);
        }

        foreach (self::SECTIONS as $key) {
            $value = $this->stringValue($data[$key] ?? '');

            if ($value === '') {
                continue;
            }

            $section->addText(strtoupper($key), ['bold' => true, 'size' => 12]);

            foreach (explode("\n", $value) as $line) {
                if (trim($line) !== '') {
                    $section->addText(trim($line));
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
            @page { margin: 1.5cm; }
            body { font-family: DejaVu Sans, sans-serif; font-size: 11px; line-height: 1.4; color: #111; }
            h1 { font-size: 20px; margin: 0 0 4px; }
            .contact { font-size: 10px; margin-bottom: 14px; }
            h2 { font-size: 12px; text-transform: uppercase; margin: 14px 0 4px; border-bottom: 1px solid #999; }
            p { margin: 0 0 4px; white-space: pre-line; }
        </style></head><body>';

        $html .= '<h1>'.e((string) ($data['name'] ?? 'Your Name')).'</h1>';

        $contact = array_filter([
            $data['email'] ?? null,
            $data['phone'] ?? null,
        ]);

        if ($contact !== []) {
            $html .= '<div class="contact">'.e(implode(' | ', $contact)).'</div>';
        }

        foreach (self::SECTIONS as $key) {
            $value = $this->stringValue($data[$key] ?? '');

            if ($value === '') {
                continue;
            }

            $html .= '<h2>'.e(strtoupper($key)).'</h2>';
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

    private function stringValue(mixed $value): string
    {
        if (is_array($value)) {
            return trim(implode(', ', array_map('strval', $value)));
        }

        return trim((string) $value);
    }
}
