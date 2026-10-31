<?php

declare(strict_types=1);

namespace Lpdf\Engine;

final readonly class RenderOptions
{
    public function __construct(
        public ?string     $createdOn = null,  // ISO 8601 for PDF /CreationDate; required with the output's pdfa
        public mixed       $data      = null,  // data object for data-* bindings (XML only)
        /**
         * The password a reader asks for before showing the PDF, for a document that asks for encryption in its
         * `output`. Leave it out, or pass `''`, for a PDF anyone can open, which only the `allow` of the
         * `output` restricts.
         */
        public ?string     $userPassword = null,
        /**
         * The password that opens the PDF with every permission. A document that asks for encryption in its
         * `output` renders only with it, or with `skipEncryption`; a document that doesn't ask for encryption
         * refuses it, so a file never goes out unencrypted while you believe it is locked.
         */
        public ?string     $ownerPassword = null,
        /**
         * Renders a document that asks for encryption unencrypted, for a render that cannot have the passwords,
         * such as a preview. Not together with the passwords.
         */
        public bool        $skipEncryption = false,
    ) {}
}
