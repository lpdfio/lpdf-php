<?php

declare(strict_types=1);

namespace Lpdf\Kit;

/**
 * How a file the PDF carries relates to the document: source, the file the document was made from;
 * data, the data its pages show, such as the XML of an e-invoice; alternative, the same content in
 * another form; supplement, more than the pages show; or unspecified.
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
