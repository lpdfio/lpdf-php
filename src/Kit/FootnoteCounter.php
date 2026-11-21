<?php

declare(strict_types=1);

namespace Lpdf\Kit;

/**
 * Where the numbers of the footnotes start over: on every page, in every section, or never.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final class FootnoteCounter
{
    public const Page = 'page';
    public const Section = 'section';
    public const Document = 'document';
}
