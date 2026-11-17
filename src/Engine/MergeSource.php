<?php

declare(strict_types=1);

namespace Lpdf\Engine;

/**
 * One file for `PdfEngine::merge`: its name, which every error about the file names, and its bytes.
 */
final readonly class MergeSource
{
    public function __construct(
        /** The file's name, such as `contract-b.pdf`. Every error about the file names it. */
        public string $name,
        /** The PDF's bytes. */
        public string $pdf,
    ) {}
}
