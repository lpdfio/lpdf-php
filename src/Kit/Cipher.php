<?php

declare(strict_types=1);

namespace Lpdf\Kit;

/**
 * The values of the `encrypt` attribute of the output: the cipher the PDF is encrypted with.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final class Cipher
{
    public const Aes256 = 'aes-256';
    public const Rc4128 = 'rc4-128';
}
