<?php

declare(strict_types=1);

namespace Lpdf\Kit;

/**
 * Declares an image that an img refers to by name. The SDK reads the file from src, or the image
 * was loaded on the engine under ref or, with no ref, under its own name.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class ImageAttr
{
    public function __construct(
        /**
         * The name that the name attribute of an img uses to pick this image: lowercase letters,
         * digits and -, starting with a letter.
         */
        public string $name,
        /** The key the image was loaded under on the engine, when that is not its name. */
        public ?string $ref = null,
        /**
         * A path the SDK reads the image file from, when the image was not loaded on the engine.
         */
        public ?string $src = null,
    ) {}
}
