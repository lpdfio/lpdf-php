<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * A barcode leaf node.
 *
 * @internal Use Layout::barcode() to construct.
 */
final readonly class BarcodeNode extends Node
{
    /** @param array<string,string|int|float|bool> $attrs */
    public function __construct(private array $attrs) {}

    public function jsonSerialize(): mixed
    {
        return [
            'type'  => 'barcode',
            'attrs' => (object) $this->attrs,
        ];
    }
}
