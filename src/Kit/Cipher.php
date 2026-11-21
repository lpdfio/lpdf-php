<?php

declare(strict_types=1);

namespace Lpdf\Kit;

/**
 * The cipher a PDF is encrypted with: AES-256, or RC4-128 for readers that cannot open AES-256. PDF
 * 2.0 deprecates RC4-128.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final class Cipher
{
    public const Aes256 = 'aes-256';
    public const Rc4128 = 'rc4-128';
}
