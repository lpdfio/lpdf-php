<?php

declare(strict_types=1);

namespace Lpdf\Layout;

/**
 * Pins content to the top or bottom edge of pages, outside the normal flow, for headers and
 * footers. It reserves that space on every page it appears on. Only allowed directly inside layout.
 * Where it is written among the flow does not matter: pin chooses the edge, and regions on one edge
 * stack in the order written. With pin set to inset, it is a block at a spot inside the section's
 * columns instead, such as a picture or a box across two columns of a newsletter: top and height
 * place it, col and colspan say which columns it covers, and those columns flow around it, filling
 * above it and carrying on below it.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class RegionAttr
{
    public function __construct(
        /**
         * Which edge the region sticks to. top and bottom work. left and right pass validation but
         * are not implemented yet. inset places the region inside the columns, where top, height,
         * col and colspan say.
         */
        public string $pin,
        /**
         * Which pages show the region: each, first, last, odd, even, or a range such as 2-last. odd
         * and even follow the number printed on the page, so in a section that starts on page 4
         * even shows on its first page. first, last and ranges count the section's own pages,
         * whatever their numbers. Defaults to each. An inset on last goes on the last page of the
         * flow filled without it; if it then pushes text on to a new page, it stays where it is,
         * one page before the end.
         */
        public ?string $page = null,
        public ?string $w = null,
        public ?bool $debug = null,
        /**
         * For pin set to inset: the column the inset starts in, counted from 1 at the left.
         * Defaults to 1. With colspan, it may not run past the section's columns. An error on any
         * other pin.
         */
        public ?int $col = null,
        /**
         * For pin set to inset: how many columns the inset covers, from col. It is as wide as those
         * columns and the gaps between them. Defaults to 1. An error on any other pin.
         */
        public ?int $colspan = null,
        /**
         * For pin set to inset, and required with it: how far below the top of the columns the
         * inset starts, under the headers. An error on any other pin.
         */
        public ?string $top = null,
        /**
         * For pin set to inset, and required with it: how tall the inset is. Content taller than it
         * is clipped at its foot, and the inset is never cut across pages: one whose top and height
         * reach below the room the headers and footers leave, on a page it is on, is an error.
         * Footnotes give way to it. An error on any other pin.
         */
        public ?string $height = null,
    ) {}
}
