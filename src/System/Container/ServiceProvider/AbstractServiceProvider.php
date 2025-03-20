<?php

/**
 * Part of Omega - Container Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

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

/**
 * Base class for service providers in the application.
 *
 * This abstract class defines the structure for service providers, which are responsible
 * for registering and bootstrapping services within the application. It also provides
 * utility methods for importing files and directories, as well as managing shared modules.
 *
 * @category   System
 * @package    Container
 * @subpackage ServiceProvider
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
abstract class AbstractServiceProvider
{
    /**
     * @var array<int|string, class-string> List of service classes to be registered.
     *
     * This array holds the class names of services that should be registered
     * when the service provider is loaded.
     */
    protected array $register = [];

    /**
     * @var array<string, array<string, string>> Shared modules imported from vendor packages.
     *
     * This static array stores paths to shared modules that can be imported
     * into the application from external packages.
     */
    protected static array $modules = [];

    /**
     * Create a new service provider instance.
     *
     * @param Application $app The application instance where the provider is registered.
     * @return void
     */
    public function __construct(protected Application $app)
    {
    }

    /**
     * Bootstraps the service provider.
     *
     * This method is called after all services have been registered.
     * It allows the provider to perform any necessary setup.
     *
     * @return void
     */
    public function boot(): void
    {
    }

    /**
     * Registers the service provider into the application container.
     *
     * This method should define all bindings and dependencies before the application is booted.
     *
     * @return void
     */
    public function register(): void
    {
    }

    /**
     * Imports a file into the application.
     *
     * This method copies a file from a given source path to a target location.
     * If the destination file already exists, it will only be overwritten if `$overwrite` is set to `true`.
     *
     * @param string $from      The source file path.
     * @param string $to        The destination file path.
     * @param bool   $overwrite Whether to overwrite the file if it already exists.
     * @return bool Returns `true` if the file was successfully copied, `false` otherwise.
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
     * Imports a directory into the application.
     *
     * This method copies an entire directory from a source location to a target location.
     * If a file or directory already exists in the destination, it will only be overwritten
     * if `$overwrite` is set to `true`.
     *
     * @param string $from      The source directory path.
     * @param string $to        The destination directory path.
     * @param bool   $overwrite Whether to overwrite existing files and directories.
     * @return bool Returns `true` if the directory was successfully copied, `false` otherwise.
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
     * Registers a package to be included in the shared modules.
     *
     * This method stores a set of file paths associated with a specific package tag,
     * allowing them to be imported into the application later.
     *
     * @param array<string, string> $path The file paths to be registered.
     * @param string                $tag  An optional tag to group the paths.
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
     * Retrieves the registered shared modules.
     *
     * @return array<string, array<string, string>> An array containing registered module paths grouped by tag.
     */
    public static function getModules(): array
    {
        return static::$modules;
    }

    /**
     * Clears all registered shared modules.
     *
     * This method removes all previously registered module paths, resetting the shared modules registry.
     *
     * @return void
     */
    public static function flushModule(): void
    {
        static::$modules = [];
    }
}
