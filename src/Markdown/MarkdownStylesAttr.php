<?php

declare(strict_types=1);

namespace Lpdf\Markdown;

/**
 * How the parts of the Markdown look. A rule is named after the Markdown it styles and takes the
 * attributes of the element it becomes, so that h1 takes what a text takes. A rule here overrides
 * the same rule of the file that src names, which overrides the defaults: a style that is not set
 * stays what it was. Values are the tokens' (font-size="xxl", color="primary") or plain lengths and
 * colors, and fonts are names from assets. A built-in font needs no rule for italic and bold; an
 * embedded font names the faces: em font="body-italic", strong font="body-bold".
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class MarkdownStylesAttr
{
    public function __construct(
        /**
         * A file of rules, whose root is a markdown-styles. The rules written here override its
         * rules. The engine reads no files: the SDK, the command line or the extension reads it, as
         * it reads the src of a font or an image.
         */
        public ?string $src = null,
    ) {}
}
