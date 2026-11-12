<?php

declare(strict_types=1);

namespace Lpdf\Canvas;

/**
 * A barcode at a fixed place on the page. It needs width or height, and the other follows the
 * barcode's proportions. With both, they are a maximum: the barcode takes the largest size that
 * fits in them, and its box becomes that size, so an anchor places the barcode itself. x and y
 * place its box: the top-left corner, or with anchor, an offset from that point of the page, where
 * the box's matching point goes.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class BarcodeAtAttr
{
    public function __construct(
        /**
         * The kind of barcode. qr, datamatrix and pdf417 are 2D codes; code128, code39, ean13,
         * ean8, upca and gs1-128 are linear ones. gs1-128 and gs1-datamatrix carry GS1 data,
         * written as (01)09501101530003(17)261231(10)AB12, and scanners read their fields as GS1.
         * code39 takes the digits, the capitals A to Z, the space and - . $ / + % only; for other
         * text use code128. ean13, ean8 and upca take their digits without the check digit, which
         * is added, or with it, which is checked: 12 or 13 digits for ean13, 7 or 8 for ean8, 11 or
         * 12 for upca. Data a type cannot hold draws a gray box with a red border in its place.
         */
        public string $type,
        public string $data,
        /**
         * Height of the whole barcode, with the digits of hrt inside it: they take the bottom 12pt
         * and the bars the rest. A barcode is never stretched. With only a height, the width
         * follows the barcode's proportions. With both width and height, they are a maximum: the
         * barcode takes the largest size that fits in them, and its box becomes that size. A QR
         * code is square, so it takes the smaller of the two. Without rows and columns, a
         * datamatrix with one size is the smallest square that holds its data, and a pdf417 takes
         * the columns that make it closest to three times as wide as high. Without a size, a
         * code128, code39 or gs1-128 is as wide as its bars at 1pt per module and its bars are 40pt
         * high, an ean13 or upca is the standard symbol, 0.33mm per module and bars 22.85mm high,
         * and an ean8 0.33mm per module and bars 18.23mm high. The digits of hrt add 12pt to any of
         * these. A qr, datamatrix or pdf417 needs a size.
         */
        public ?string $height = null,
        /**
         * Error correction: how much of the code can be damaged and still scan. A qr takes L, M, Q
         * or H, from 7% to 30% of the code, and M without it. A pdf417 takes a level from 0 to 8,
         * each one doubling the codewords it adds, and without it the level the standard recommends
         * for the length of the data. The other types ignore it, and so does a qr a number and a
         * pdf417 a letter.
         */
        public ?string $ec = null,
        /**
         * Shows the data in digits or letters under the bars of a linear barcode: code128, code39,
         * ean13, ean8, upca and gs1-128, the last with its fields in parentheses. The text takes
         * the bottom 12pt of the barcode's height. The 2D types ignore it.
         */
        public ?bool $hrt = null,
        /**
         * The rows of a pdf417 (3 to 90) or of a datamatrix or gs1-datamatrix (a height of the
         * standard's sizes, 8 to 144 modules). With columns, they fix the code's shape; alone, they
         * narrow the shapes the code chooses from. Data that needs more gets more: more rows for a
         * pdf417, the next size up for a Data Matrix. Without rows and columns, the code takes the
         * shape that comes out largest in its width and height. The other types ignore it.
         */
        public ?int $rows = null,
        /**
         * The columns of a pdf417, in codewords (1 to 30), or of a datamatrix or gs1-datamatrix, in
         * modules (a width of the standard's sizes, 10 to 144). With rows, they fix the code's
         * shape; alone, they narrow the shapes the code chooses from. Data that needs more gets
         * more: more columns for a pdf417 once it has 90 rows, the next size up for a Data Matrix.
         * The other types ignore it.
         */
        public ?int $columns = null,
        /**
         * Adds the mod 43 check character to a code39, as HIBC health care labels ask. Without it
         * there is none, as most specs and scanners expect: a scanner not set to check one reads it
         * as one more character of the data. The other types ignore it.
         */
        public ?bool $check = null,
        public ?string $color = null,
        public ?string $background = null,
        public ?string $x = null,
        public ?string $y = null,
        public ?string $anchor = null,
        public ?string $width = null,
        /**
         * Puts the element, and everything in it, in an optional content group, which the viewer
         * shows or hides. print: printed, and not shown on screen. screen: shown on screen, and not
         * printed. The element keeps its room either way, so nothing moves. An element with a group
         * inside an element with another shows only where both show. Acrobat, Chrome and Firefox
         * switch the groups; Preview and Safari show everything, on screen and in print. Hidden
         * content is still in the file, where it can be selected and copied: it is not a way to
         * keep anything secret. A link in print content is drawn and does nothing, and a form field
         * can't be in print content: use a frame with a border for a box filled in by hand. With
         * pdfa or pdfua on the output, everything shows. With pdfx, the file is the print version:
         * screen content stays in it, off, and a reader can turn it on in a layers panel.
         */
        public ?string $ocg = null,
    ) {}
}
