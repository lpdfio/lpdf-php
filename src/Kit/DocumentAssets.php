<?php

declare(strict_types=1);

namespace Lpdf\Kit;

use Lpdf\Shared\AttrsHelper;

/**
 * The fonts and images the document declares, as the `assets` element of the XML does.
 *
 * A font or image is picked by its `name`: text sets `font` to a font's name, an `img` sets `name`
 * to an image's.
 */
final readonly class DocumentAssets implements \JsonSerializable
{
    use AttrsHelper;

    /**
     * @param list<FontAttr>|null  $fonts
     * @param list<ImageAttr>|null $images
     */
    public function __construct(
        public ?array $fonts  = null,
        public ?array $images = null,
    ) {}

    public function jsonSerialize(): mixed
    {
        $declared = array_filter(
            ['fonts' => $this->fonts, 'images' => $this->images],
            static fn(?array $items): bool => $items !== null && $items !== [],
        );

        return array_map(
            static fn(array $items): array => array_map(
                static fn(object $item): array => self::optionsToAttrs($item),
                array_values($items),
            ),
            $declared,
        );
    }
}
