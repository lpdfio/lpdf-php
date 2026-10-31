<?php

declare(strict_types=1);

namespace Lpdf\Kit;

/**
 * A file the PDF carries, under the name a reader shows. The SDK reads it from src, or it was
 * loaded on the engine under ref or, with no ref, under its own name. A file with no bytes is an
 * error, and so are two files with the same name.
 *
 * Generated from lpdf.xsd by scripts/gen-sdk-api.mjs.
 * Do not edit: change the schema and run `make gen-sdk-api`.
 */
final readonly class FileAttr
{
    public function __construct(
        /**
         * The name a reader shows the file under and saves it as, such as invoice.xml. Each file of
         * the document has a name of its own.
         */
        public string $name,
        /** A path the SDK reads the file from, when the file was not loaded on the engine. */
        public ?string $src = null,
        /** The key the file was loaded under on the engine, when that is not its name. */
        public ?string $ref = null,
        /**
         * The type of the file, such as text/xml, application/pdf or text/csv. Defaults to
         * application/octet-stream, which says only that it is a file.
         */
        public ?string $mime = null,
        /**
         * How the file relates to the document: source, data, alternative, supplement or
         * unspecified, the default. An e-invoice's XML is data.
         */
        public ?string $relationship = null,
        /** A line about the file, which a reader shows beside its name. */
        public ?string $description = null,
    ) {}
}
