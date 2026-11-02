<?php

declare(strict_types=1);

namespace Lpdf\Engine;

use Lpdf\Engine\EngineException;

final readonly class WasmRunner
{
    /** The seconds a render may run when `timeout` is left out. A timeout of 0 means no limit. */
    public const DEFAULT_TIMEOUT = 5;

    public function __construct(
        private readonly string $wasmBinary,
        private readonly string $wasmRunner = 'wasmtime',
        private readonly int $timeout = self::DEFAULT_TIMEOUT,
    ) {}

    /**
     * @param  array<string, mixed> $payload  Already-built request array.
     * @return array<string, mixed>            Decoded response.
     * @throws EngineException On process or render error, or when the process runs past the timeout.
     */
    public function invoke(array $payload): array
    {
        $proc = proc_open(
            [$this->wasmRunner, 'run', $this->wasmBinary],
            [
                0 => ['pipe', 'r'],   // stdin
                1 => ['pipe', 'w'],   // stdout
                2 => ['pipe', 'w'],   // stderr
            ],
            $pipes,
        );

        if ($proc === false) {
            throw new EngineException('Failed to start WASI process.');
        }

        fwrite($pipes[0], json_encode($payload, JSON_THROW_ON_ERROR));
        fclose($pipes[0]);

        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $out     = '';
        $err     = '';
        // A timeout of 0 sets no deadline: the wait for output then has no limit either.
        $deadline = $this->timeout > 0 ? microtime(true) + $this->timeout : null;

        while (!feof($pipes[1]) || !feof($pipes[2])) {
            $read    = array_filter([$pipes[1], $pipes[2]], fn($p) => !feof($p));
            $write   = null;
            $except  = null;
            $sec     = null;
            $usec    = null;
            if ($deadline !== null) {
                $remaining = $deadline - microtime(true);
                if ($remaining <= 0) {
                    proc_terminate($proc);
                    fclose($pipes[1]);
                    fclose($pipes[2]);
                    proc_close($proc);
                    throw new EngineException('The render timed out after ' . self::seconds($this->timeout) . '.');
                }
                $sec  = (int) $remaining;
                $usec = (int)(($remaining - $sec) * 1_000_000);
            }
            if (stream_select($read, $write, $except, $sec, $usec) === false) {
                break;
            }
            foreach ($read as $r) {
                $chunk = fread($r, 8192);
                if ($chunk !== false) {
                    if ($r === $pipes[1]) { $out .= $chunk; }
                    else                  { $err .= $chunk; }
                }
            }
        }

        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($proc);

        if ($out === '') {
            throw new EngineException("WASI process produced no output. Stderr: $err");
        }

        $response = json_decode($out, true, 512, JSON_THROW_ON_ERROR);

        if (isset($response['error'])) {
            throw new EngineException("lpdf render error: {$response['error']}");
        }

        return $response;
    }

    private static function seconds(int $count): string
    {
        return $count === 1 ? '1 second' : "$count seconds";
    }
}
