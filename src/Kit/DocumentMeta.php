<?php

declare(strict_types=1);

namespace Lpdf\Kit;

/** PDF document metadata written into the output file. */
final readonly class DocumentMeta implements \JsonSerializable
{
    public function __construct(
        public ?string $title    = null,
        public ?string $author   = null,
        public ?string $subject  = null,
        public ?string $keywords = null,
        public ?string $creator  = null,
        /** The language the document is written in, as a language tag such as `en-US`. A PDF/UA document needs it. */
        public ?string $lang     = null,
    ) {}

    public function jsonSerialize(): mixed
    {
        return array_filter(
            [
                'title'    => $this->title,
                'author'   => $this->author,
                'subject'  => $this->subject,
                'keywords' => $this->keywords,
                'creator'  => $this->creator,
                'lang'     => $this->lang,
            ],
            static fn($v) => $v !== null,
        );
    }
}
