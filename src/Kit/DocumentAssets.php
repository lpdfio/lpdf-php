<?php

declare(strict_types=1);

namespace Lpdf\Kit;

use Lpdf\Shared\AttrsHelper;

/**
 * The fonts, images and color profiles the document declares, as the `assets` element of the XML does.
 *
 * A font or image is picked by its `name`: text sets `font` to a font's name, an `img` sets `name`
 * to an image's, and the output's `profile` names a profile.
 */
final readonly class DocumentAssets implements \JsonSerializable
{
    use AttrsHelper;

    /**
     * @param list<FontAttr>|null    $fonts
     * @param list<ImageAttr>|null   $images
     * @param list<ProfileAttr>|null $profiles ICC color profiles: the profile of the printing press a PDF/X document
     *                                         is made for, which the printer gives out. Each is read from its `src`,
     *                                         or loaded with `loadProfile` under its `ref` or name.
     */
    public function __construct(
        public ?array $fonts    = null,
        public ?array $images   = null,
        public ?array $profiles = null,
    ) {}

    public function jsonSerialize(): mixed
    {
        $declared = array_filter(
            ['fonts' => $this->fonts, 'images' => $this->images, 'profiles' => $this->profiles],
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
