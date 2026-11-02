<?php

declare(strict_types=1);

namespace Lpdf\Engine;

/**
 * Thrown when the engine returns a layout or parse error, a render runs past its timeout or its memory cap, or the
 * engine crashes.
 */
final class EngineException extends \RuntimeException
{
    /** The message of a render that crashes the engine, the same in every SDK of Lpdf. */
    public const CRASHED = 'The engine crashed during the render. This is a bug in Lpdf, or the render ran out of '
        . 'memory before its cap stopped it: please report it with the document at https://github.com/lpdfio/lpdf/issues.';
}
