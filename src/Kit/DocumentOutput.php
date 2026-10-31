<?php

declare(strict_types=1);

namespace Lpdf\Kit;

/**
 * How the PDF file is written, as the `output` element of the XML is: whether it is encrypted, and what a reader
 * may do with it. The passwords are not here: they come with the render call, on `RenderOptions`.
 */
final readonly class DocumentOutput implements \JsonSerializable
{
    public function __construct(
        /** The cipher the PDF is encrypted with, `aes-256` or `rc4-128` (`Cipher`). Without it the PDF is not encrypted. */
        public ?string $encrypt = null,
        /**
         * What a reader may do without the owner password, separated by spaces (`Permission`): `print`, `modify`,
         * `copy`, `annotate`, `fill-forms`, `accessibility`, `assemble`. Without it everything is allowed; `''`
         * allows nothing. It needs `encrypt`.
         */
        public ?string $allow = null,
    ) {}

    public function jsonSerialize(): mixed
    {
        return array_filter(
            [
                'encrypt' => $this->encrypt,
                'allow'   => $this->allow,
            ],
            static fn($v) => $v !== null,
        );
    }
}
