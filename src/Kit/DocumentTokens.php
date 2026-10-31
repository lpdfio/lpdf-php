<?php

declare(strict_types=1);

namespace Lpdf\Kit;

/**
 * Design-token overrides applied to the whole document. `gradients` maps the name of each gradient to a `Gradient`:
 * its `type` (`linear` or `radial`), `from` and `to`, or `stops` as a list of `GradientStop`, and `angle` in degrees.
 * A background or a fill uses one as `gradient:name`. `lineHeight` is the row of factors, each a number above 0. `aliases` maps a name to the slot of the
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
     * @param array<string,int|float>|null $lineHeight
     * @param array<string,Gradient>|null $gradients
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
                'gradients' => $this->gradients === null ? null : array_map(self::gradient(...), $this->gradients),
                'aliases' => $this->aliases,
                'height' => $this->height,
            ],
            static fn($v) => $v !== null,
        );
    }

    /**
     * A gradient as the document tree takes it: its attributes that are set, and its stops.
     *
     * @return array<string,mixed>
     */
    private static function gradient(Gradient $gradient): array
    {
        $stops = array_map(
            static fn(GradientStop $stop) => ['offset' => $stop->offset, 'color' => $stop->color],
            $gradient->stops,
        );
        return array_filter(
            [
                'type' => $gradient->type,
                'from' => $gradient->from,
                'to' => $gradient->to,
                'angle' => $gradient->angle,
                'stops' => $stops === [] ? null : $stops,
            ],
            static fn($v) => $v !== null,
        );
    }
}
