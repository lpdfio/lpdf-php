<?php

declare(strict_types=1);

namespace Lpdf\Canvas;

/** @internal Use Canvas::img() to construct. */
final readonly class ImageNode extends Node
{
    public function __construct(
        private float   $x,
        private float   $y,
        private float   $w,
        private float   $h,
        private string  $name,
        private ?string $anchor = null,
    ) {}

    public function jsonSerialize(): mixed
    {
        $attrs = ['x' => (string)$this->x, 'y' => (string)$this->y, 'w' => (string)$this->w, 'h' => (string)$this->h, 'name' => $this->name];
        if ($this->anchor !== null) $attrs['anchor'] = $this->anchor;
        return ['type' => 'canvas-img', 'attrs' => (object) $attrs];
    }
}
