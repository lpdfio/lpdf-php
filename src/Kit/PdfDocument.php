<?php

declare(strict_types=1);

namespace Lpdf\Kit;

/**
 * Root document node — the return type of {@see \Lpdf\Pdf::document()}.
 *
 * @internal Construct via Pdf::document().
 */
final readonly class PdfDocument implements \JsonSerializable
{
    /**
     * @param array<string,mixed> $attrs  Flat attrs plus optional 'tokens' and 'meta' sub-objects.
     * @param SectionNode[]       $nodes
     */
    public function __construct(
        private array $attrs,
        private array $nodes,
    ) {}

    public function jsonSerialize(): mixed
    {
        return [
            'version' => 1,
            'type'    => 'document',
            'attrs'   => (object) $this->attrs,
            'nodes'   => $this->nodes,
        ];
    }
}
