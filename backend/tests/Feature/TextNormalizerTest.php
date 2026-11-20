<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\TextNormalizer;
use Tests\TestCase;

class TextNormalizerTest extends TestCase
{
    public function test_keeps_paragraph_breaks_from_html(): void
    {
        $html = '<p>First paragraph.</p><p>Second paragraph with <strong>bold</strong> text.</p><h3>Heading</h3><p>After heading.</p>';

        $text = app(TextNormalizer::class)->toPlainText($html);

        $this->assertSame(
            "First paragraph.\n\nSecond paragraph with bold text.\n\nHeading\n\nAfter heading.",
            $text,
        );
    }

    public function test_formats_list_items_on_separate_lines(): void
    {
        $html = '<ul><li>React</li><li>PHP</li></ul><p>Done.</p>';

        $text = app(TextNormalizer::class)->toPlainText($html);

        $this->assertSame("- React\n- PHP\n\nDone.", $text);
    }

    public function test_collapses_spaces_but_keeps_line_breaks(): void
    {
        $html = "<p>  Too    many   spaces  </p>\n\n<p>Next   line</p>";

        $text = app(TextNormalizer::class)->toPlainText($html);

        $this->assertSame("Too many spaces\n\nNext line", $text);
    }
}
