<?php

declare(strict_types=1);

namespace App\Services;

use Smalot\PdfParser\Parser;

final class PdfTextExtractor
{
    public function extract(string $absolutePath): string
    {
        $parser = new Parser;

        return trim($parser->parseFile($absolutePath)->getText());
    }
}
