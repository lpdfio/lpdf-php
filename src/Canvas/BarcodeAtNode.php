<?php

declare(strict_types=1);

namespace Lpdf\Canvas;

/**
 * A `barcode-at` in a layer.
 *
 * @internal Use L::barcodeAt() to construct.
 */
final readonly class BarcodeAtNode extends Node
{
    /** @param array<string,string|int|float|bool> $attrs */
    public function __construct(private array $attrs) {}

    public function jsonSerialize(): mixed
    {
        return ['type' => 'barcode-at', 'attrs' => (object) $this->attrs];
    }
}
