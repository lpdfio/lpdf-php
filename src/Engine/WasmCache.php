<?php

declare(strict_types=1);

namespace Lpdf\Engine;

/**
 * The folder where the `wasmtime` program keeps the engine compiled to native code, so that a render after the first
 * doesn't compile it again. It holds `config.toml`, which `wasmtime run -C cache-config=…` reads, and beside it
 * `cache`, which the file names as wasmtime's cache directory: wasmtime's cleanup deletes the files it doesn't know in
 * that directory, so the file can't be in it.
 *
 * wasmtime runs what its cache holds without checking it, so the folder is used only where no one but PHP's user can
 * put a file in it: a directory, not a link, that PHP's user owns and group and others can't open.
 */
final readonly class WasmCache
{
    /** The name of the folder in the temp directory when `wasmCacheDir` is left out, before PHP's user id. */
    public const DEFAULT_FOLDER = 'lpdf-wasmtime-';

    /** Why no folder is used where PHP has no POSIX extension, which alone tells the user id that owns a folder. */
    public const NO_POSIX = "PHP's POSIX extension is not loaded, and the SDK needs it to tell who owns the folder";

    private function __construct(
        /** The `config.toml` that names the cache, for `-C cache-config=`. */
        public string $configFile,
    ) {}

    /**
     * The cache in `$folder`, taken from PHP's working directory where it is relative, or in `lpdf-wasmtime-<uid>` in
     * the temp directory when it is null, with the folder made with mode 0700 where it is missing.
     *
     * @return self|string The cache, or why the folder isn't used, as a clause that names it.
     */
    public static function open(?string $folder): self|string
    {
        if (!\function_exists('posix_geteuid')) {
            return self::NO_POSIX;
        }
        $uid = posix_geteuid();
        $folder ??= rtrim(sys_get_temp_dir(), '/') . '/' . self::DEFAULT_FOLDER . $uid;
        // wasmtime takes an absolute cache directory only. It starts in PHP's working directory, as the checks below do.
        if (!str_starts_with($folder, '/')) {
            $workingDirectory = getcwd();
            if ($workingDirectory === false) {
                return "$folder is relative, and PHP can't tell its working directory";
            }
            $folder = "$workingDirectory/$folder";
        }
        // config.toml is TOML, which is UTF-8.
        if (preg_match('//u', $folder) !== 1) {
            return "the path of $folder isn't UTF-8, which config.toml needs";
        }
        if (str_contains($folder, ',')) {
            return "the path of $folder has a comma, which wasmtime's -C option splits on";
        }
        return self::refusal($folder, $uid) ?? self::configure($folder);
    }

    /** Why `$folder`, made if it is missing, isn't one that only the user `$uid` can put files in; null when it is. */
    private static function refusal(string $folder, int $uid): ?string
    {
        clearstatcache(true, $folder);
        error_clear_last();
        // Another worker may make it at the same moment, which is as good.
        if (@lstat($folder) === false && !@mkdir($folder, 0700, true) && !is_dir($folder)) {
            return "$folder can't be made (" . self::phpError() . ')';
        }
        clearstatcache(true, $folder);
        $stat = @lstat($folder);
        if ($stat === false) {
            return "$folder can't be made (" . self::phpError() . ')';
        }
        $type = $stat['mode'] & 0170000;
        if ($type === 0120000) {
            return "$folder is a link";
        }
        if ($type !== 0040000) {
            return "$folder is not a directory";
        }
        if ($stat['uid'] !== $uid) {
            return "$folder belongs to another user";
        }
        if (($stat['mode'] & 0077) !== 0) {
            return sprintf('%s lets group or others in, with mode %04o', $folder, $stat['mode'] & 0777);
        }
        return null;
    }

    /** The cache in `$folder`, with its `config.toml` written where it doesn't hold what it should. */
    private static function configure(string $folder): self|string
    {
        $file = "$folder/config.toml";
        // A TOML basic string takes JSON's escapes, but for an escaped slash.
        $directory = json_encode("$folder/cache", JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $config = "[cache]\ndirectory = $directory\n";
        if (@file_get_contents($file) === $config) {
            return new self($file);
        }
        // Written whole under a name of its own, then renamed: a wasmtime that another worker starts meanwhile reads
        // the old file or the new one, never half of one.
        error_clear_last();
        $temporary = "$file." . bin2hex(random_bytes(6));
        if (@file_put_contents($temporary, $config) !== \strlen($config) || !@rename($temporary, $file)) {
            $why = self::phpError();
            @unlink($temporary);
            return "$file can't be written ($why)";
        }
        return new self($file);
    }

    /** What PHP said of the last call that failed, without the function's name. */
    private static function phpError(): string
    {
        $message = error_get_last()['message'] ?? 'PHP gave no reason';
        return (string) preg_replace('/^\w+\(.*?\): /', '', $message);
    }
}
