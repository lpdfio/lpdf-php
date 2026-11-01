<?php

declare(strict_types=1);

namespace Lpdf\Kit;

/**
 * How the PDF file is written, as the `output` element of the XML is: whether it is encrypted, and what a reader
 * may do with it, and the standard it meets. The passwords are not here: they come with the render call, on
 * `RenderOptions`.
 */
final readonly class DocumentOutput implements \JsonSerializable
{
    public function __construct(
        /**
         * The cipher the PDF is encrypted with, `aes-256` or `rc4-128` (`Cipher`). Without it the PDF is not
         * encrypted.
         */
        public ?string $encrypt = null,
        /**
         * What a reader may do without the owner password, separated by spaces (`Permission`): `all`, or any of
         * `print`, `modify`, `copy`, `annotate`, `fill-forms`, `accessibility`, `assemble`. A permission comes only
         * from naming it: without `allow`, or with `''`, nothing is allowed, and `all` allows everything. It needs
         * `encrypt`.
         */
        public ?string $allow = null,
        /**
         * Writes the PDF as PDF/A, the standard for files kept for the long term: `2b`, or `2u` when its text must
         * also be searchable. The render needs `createdOn`, and every text a font of the assets. What PDF/A cannot
         * hold is an error that says what to change: a built-in font, a CMYK color, a form field, a barcode's
         * digits, `encrypt`.
         */
        public ?string $pdfa = null,
        /**
         * Writes the PDF as PDF/UA-1, a tagged PDF a screen reader can read: `1`. It combines with `pdfa`. The
         * render needs `createdOn`, the meta a `title` and a `lang`, every image and barcode of the layout an `alt`,
         * and every text a font of the assets. What PDF/UA cannot hold is an error that says what to change: headings
         * that don't start at h1 or skip a level, a font whose licence forbids embedding, a character the font has no
         * glyph for, a barcode's digits, a form field, and `encrypt` with an `allow` that leaves out `accessibility`.
         */
        public ?string $pdfua = null,
        /**
         * Writes the PDF as PDF/X-4, for a commercial printer: `4`. It needs `profile`, and combines with `pdfua`.
         * Every page gets a TrimBox and a BleedBox, RGB is tied to sRGB, and links print as text, as PDF/X allows
         * nothing clickable. The render needs `createdOn`, the meta a `title`, and every text a font of the assets.
         * What PDF/X cannot hold is an error that says what to change: a profile that is not a printer profile, a
         * built-in font, a form field, a CMYK color with a profile that is not a CMYK one, `encrypt`, `pdfa`,
         * attachments.
         */
        public ?string $pdfx = null,
        /** The profile of the assets that a PDF/X file is made for: the ICC profile of the printing press. It needs `pdfx`. */
        public ?string $profile = null,
    ) {}

    public function jsonSerialize(): mixed
    {
        return array_filter(
            [
                'encrypt' => $this->encrypt,
                'allow'   => $this->allow,
                'pdfa'    => $this->pdfa,
                'pdfua'   => $this->pdfua,
                'pdfx'    => $this->pdfx,
                'profile' => $this->profile,
            ],
            static fn($v) => $v !== null,
        );
    }
}
