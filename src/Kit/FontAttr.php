<?php

declare(strict_types=1);

namespace Lpdf\Kit;

/**
 * Declares a font that text can be set in by name, with the font attribute. A built-in PDF font is
 * named by core; any other font is a file the SDK reads from src, or one loaded on the engine under
 * ref or, with no ref, under the font's own name.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class FontAttr
{
    public function __construct(
        /**
         * The name that the font attribute of a text uses to pick this font: lowercase letters,
         * digits and -, starting with a letter.
         */
        public string $name,
        /** A built-in PDF font, which needs no file. */
        public ?string $core = null,
        /** The key the font was loaded under on the engine, when that is not its name. */
        public ?string $ref = null,
        /**
         * A path the SDK reads the font file from, when the font was not loaded on the engine.
         */
        public ?string $src = null,
    ) {}
}
