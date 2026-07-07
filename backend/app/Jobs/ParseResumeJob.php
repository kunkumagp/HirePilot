<?php

namespace App\Jobs;

use App\Contracts\ResumeVersionRepositoryInterface;
use App\Models\ResumeVersion;
use App\Services\AI\AIProviderInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ParseResumeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        private readonly ResumeVersion $version,
    ) {}

    public function handle(
        ResumeVersionRepositoryInterface $versionRepository,
        AIProviderInterface $ai,
    ): void {
        $this->version->update([
            'parse_status' => 'processing',
            'parse_attempts' => $this->version->parse_attempts + 1,
        ]);

        try {
            $filePath = Storage::disk('local')->path($this->version->file_path);

            if (!file_exists($filePath)) {
                throw new \RuntimeException('File not found at path: ' . $filePath);
            }

            $extension = $this->version->file_type;
            $rawText = match ($extension) {
                'txt' => file_get_contents($filePath),
                'pdf' => $this->parsePdf($filePath),
                'docx' => $this->parseDocx($filePath),
                default => throw new \RuntimeException("Unsupported file type: {$extension}"),
            };

            $parsedContent = $this->extractSections($rawText, $ai);

            $versionRepository->update($this->version, [
                'raw_text' => $rawText,
                'parsed_content' => $parsedContent,
                'parse_status' => 'completed',
            ]);
        } catch (\Throwable $e) {
            $failed = $this->version->parse_attempts >= $this->tries;

            $versionRepository->update($this->version, [
                'parse_status' => $failed ? 'failed' : 'pending',
                'parse_error' => $e->getMessage(),
            ]);

            if ($failed) {
                $this->fail($e);
            } else {
                throw $e;
            }
        }
    }

    private function parsePdf(string $filePath): string
    {
        $text = '';
        if (class_exists(\Smalot\PdfParser\Parser::class)) {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();
        }

        if (empty($text)) {
            $command = escapeshellcmd("pdftotext " . escapeshellarg($filePath) . " -");
            $text = shell_exec($command) ?? '';
        }

        return $text ?: file_get_contents($filePath);
    }

    private function parseDocx(string $filePath): string
    {
        if (!class_exists(\PhpOffice\PhpWord\IOFactory::class)) {
            $zip = new \ZipArchive();
            if ($zip->open($filePath) === true) {
                $content = $zip->getFromName('word/document.xml');
                $zip->close();
                if ($content) {
                    $content = strip_tags($content);
                    return trim(preg_replace('/\s+/', ' ', $content));
                }
            }
        }

        $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath);
        $text = '';
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText() . "\n";
                }
            }
        }

        return $text;
    }

    private function extractSections(string $text, AIProviderInterface $ai): array
    {
        $aiKey = config('ai.providers.' . config('ai.default') . '.api_key');

        if (!empty($aiKey)) {
            try {
                return $this->extractSectionsWithAi($text, $ai);
            } catch (\Throwable $e) {
                Log::warning('AI section extraction failed, falling back to basic parsing', [
                    'error' => $e->getMessage(),
                    'version_id' => $this->version->id,
                ]);
            }
        }

        return $this->extractSectionsBasic($text);
    }

    private function extractSectionsWithAi(string $text, AIProviderInterface $ai): array
    {
        $prompt = <<<PROMPT
Parse the following resume text into structured sections. Identify common resume sections such as:
- summary / objective / profile
- experience / work experience / employment
- education / academic background
- skills / technical skills / core competencies
- projects
- certifications / certificates
- languages
- publications
- references

Return a JSON object where keys are the section names (normalized to lowercase, underscore_separated) and values are the text content for each section.

Resume text:
{$text}
PROMPT;

        return $ai->generateJson($prompt, [
            'temperature' => 0.1,
            'system_prompt' => 'You are a resume parsing assistant. Extract and structure resume content into sections. Respond only with valid JSON.',
        ]);
    }

    private function extractSectionsBasic(string $text): array
    {
        $sections = [];
        $lines = explode("\n", $text);
        $currentSection = 'summary';
        $currentContent = [];

        $sectionHeaders = [
            'experience', 'work experience', 'employment', 'work history',
            'education', 'academic background',
            'skills', 'technical skills', 'core competencies',
            'projects', 'personal projects',
            'certifications', 'certificates',
            'languages',
            'publications',
            'references',
        ];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed)) {
                continue;
            }

            $lower = strtolower($trimmed);
            $isHeader = false;

            foreach ($sectionHeaders as $header) {
                if (str_starts_with($lower, $header) || $lower === $header) {
                    if (!empty($currentContent)) {
                        $sections[$currentSection] = implode("\n", $currentContent);
                    }
                    $currentSection = $header;
                    $currentContent = [];
                    $isHeader = true;
                    break;
                }
            }

            if (!$isHeader) {
                $currentContent[] = $trimmed;
            }
        }

        if (!empty($currentContent)) {
            $sections[$currentSection] = implode("\n", $currentContent);
        }

        return $sections;
    }
}
