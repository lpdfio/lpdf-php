<?php

declare(strict_types=1);

namespace Lpdf\Kit;

/**
 * Design-token overrides applied to the whole document. `gradients` maps the name of each gradient to its
 * attributes: `type` (`linear` or `radial`), `from` and `to`, or `stops` as a list of `offset` and `color`, and
 * `angle` in degrees. A background or a fill uses one as `gradient:name`. `aliases` maps a name to the slot of the
 * scale it stands for: `['caption' => 'xs']` lets `fontSize: 'caption'` mean the text size `xs`, and `gap: 'caption'`
 * the space `xs`. `height` is the row of heights that `height: 'm'` names, from `xs` to `xxl`.
 */
final readonly class DocumentTokens implements \JsonSerializable
{
    /**
     * @param array<string,string>|null $colors
     * @param array<string,string>|null $space
     * @param array<string,string>|null $grid
     * @param array<string,string>|null $border
     * @param array<string,string>|null $radius
     * @param array<string,string>|null $width
     * @param array<string,string>|null $textSize
     * @param array<string,string>|null $lineHeight
     * @param array<string,array{type: string, from?: string, to?: string, angle?: int|float|string, stops?: list<array{offset: string, color: string}>}>|null $gradients
     * @param array<string,string>|null $aliases
     * @param array<string,string>|null $height
     */
    public function __construct(
        public ?array $colors = null,
        public ?array $space  = null,
        public ?array $grid   = null,
        public ?array $border = null,
        public ?array $radius = null,
        public ?array $width  = null,
        public ?array $textSize = null,
        public ?array $lineHeight = null,
        public ?array $gradients = null,
        public ?array $aliases = null,
        public ?array $height = null,
    ) {}

    public function jsonSerialize(): mixed
    {
        return array_filter(
            [
                'colors' => $this->colors,
                'space'  => $this->space,
                'grid'   => $this->grid,
                'border' => $this->border,
                'radius' => $this->radius,
                'width'  => $this->width,
                'text-size' => $this->textSize,
                'line-height' => $this->lineHeight,
                'gradients' => $this->gradients,
                'aliases' => $this->aliases,
                'height' => $this->height,
            ],
            static fn($v) => $v !== null,
        );
    }
}
