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
    public const Source = 'source';
    public const Data = 'data';
    public const Alternative = 'alternative';
    public const Supplement = 'supplement';
    public const Unspecified = 'unspecified';
}
