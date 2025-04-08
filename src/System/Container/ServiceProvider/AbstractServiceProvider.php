<?php

declare(strict_types=1);

namespace System\Container\ServiceProvider;

use System\Application\Application;

use function array_key_exists;
use function array_merge;
use function closedir;
use function copy;
use function file_exists;
use function is_dir;
use function mkdir;
use function opendir;
use function pathinfo;
use function readdir;

use const PATHINFO_DIRNAME;

abstract class AbstractServiceProvider
{
    /** @var array<int|string, class-string> Class register */
    protected array $register = [ /** Register the provider */ ];

    /** @var array<string, array<string, string>> Shared modules to import from vendor package. */
    protected static array $modules = [];

    /**
     * Create a new service provider instance.
     *
     * @param Application $app
     * @return void
     */
    public function __construct(protected Application $app)
    {
    }

    /**
     * Boot provider.
     *
     * @return void
     */
    public function boot(): void
    {
        // boot
    }

    /**
     * Register to application container before booted.
     *
     * @return void
     */
    public function register(): void
    {
        // register application container
    }

    /**
     * Import a specific file to the application.
     *
     * @param string $from
     * @param string $to
     * @param bool $overwrite
     * @return bool
     */
    public static function importFile(string $from, string $to, bool $overwrite = false): bool
    {
        $exists = file_exists($to);
        if (($exists && $overwrite) || false === $exists) {
            $path = pathinfo($to, PATHINFO_DIRNAME);
            if (false === file_exists($path)) {
                mkdir($path, 0755, true);
            }

            return copy($from, $to);
        }

        return false;
    }

    /**
     * Import a directory to the application.
     *
     * @param string $from
     * @param string $to
     * @param bool $overwrite
     * @return bool
     */
    public static function importDir(string $from, string $to, bool $overwrite = false): bool
    {
        $dir = opendir($from);
        if (false === $dir) {
            return false;
        }

        if (false === file_exists($to)) {
            mkdir($to, 0755, true);
        }

        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $src = $from . '/' . $file;
            $dst = $to . '/' . $file;

            if (is_dir($src)) {
                if (false === static::importDir($src, $dst, $overwrite)) {
                    closedir($dir);

                    return false;
                }
            } else {
                if (false === static::importFile($src, $dst, $overwrite)) {
                    closedir($dir);

                    return false;
                }
            }
        }

        closedir($dir);

        return true;
    }

    /**
     * Register a package to the module.
     *
     * @param array<string, string> $path
     * @param string                $tag
     * @return void
     */
    public static function export(array $path, string $tag = ''): void
    {
        if (false === array_key_exists($tag, static::$modules)) {
            static::$modules[$tag] = [];
        }

        static::$modules[$tag] = array_merge(static::$modules[$tag], $path);
    }

    /**
     * Get registers modules.
     *
     * @return array<string, array<string, string>>
     */
    public static function getModules(): array
    {
        return static::$modules;
    }

    /**
     * Flush shared modules.
     */
    public static function flushModule(): void
    {
        static::$modules = [];
    }
}
