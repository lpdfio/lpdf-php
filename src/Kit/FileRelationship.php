<?php

declare(strict_types=1);

namespace Lpdf\Kit;

/**
 * The values of the `relationship` attribute of a file the PDF carries: how it relates to the
 * document.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final class FileRelationship
{
    public const string Source = 'source';
    public const string Data = 'data';
    public const string Alternative = 'alternative';
    public const string Supplement = 'supplement';
    public const string Unspecified = 'unspecified';
}
