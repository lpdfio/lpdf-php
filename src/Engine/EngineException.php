<?php

declare(strict_types=1);

namespace Lpdf\Engine;

/**
 * Thrown when the engine returns a layout or parse error, a render or the reading of an invoice runs past its timeout
 * or its memory cap, or the engine crashes.
 */
final class EngineException extends \RuntimeException
{
    /** The message of a render that crashes the engine, the same in every SDK of Lpdf. */
    public const CRASHED = 'The engine crashed during the render. This is a bug in Lpdf, or the render ran out of '
        . 'memory before its cap stopped it: please report it with the document at https://github.com/lpdfio/lpdf/issues.';

    /**
     * The error of a request that ran past its timeout of `$seconds`, the same through either runner, named by what
     * its `$method` runs: the reading of the invoice for `extract_invoice_data`, and the render for any other.
     */
    public static function timedOut(int $seconds, string $method): self
    {
        $work = $method === 'extract_invoice_data' ? 'The reading of the invoice' : 'The render';
        return new self("$work timed out after " . ($seconds === 1 ? '1 second' : "$seconds seconds") . '.');
    }
}
