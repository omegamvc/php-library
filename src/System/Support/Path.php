<?php

/**
 * Part of Omega MVC - Support Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace System\Support;

use InvalidArgumentException;

use function array_map;
use function implode;
use function ltrim;
use function pathinfo;
use function rtrim;
use function str_replace;

/**
 * Path class.
 *
 * This class provides utility method getPath for managing paths within the Omega MVC framework.
 * It allows the initialization of a base path for the project and provides a method to
 * generate full paths by joining the base path with other directory or file names.
 *
 * The base path can be set using the `init()` method and is used globally throughout
 * the application. The `getPath()` method can then be used to generate paths for various
 * resources (e.g., configuration files, database directories) relative to the base path.
 *
 * @category  System
 * @package   Support
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class Path
{
    /**
     * Directory separator alias.
     *
     * This constant provides an alias for `DIRECTORY_SEPARATOR`, ensuring code readability and maintaining
     * compliance with coding standards by reducing line length.
     *
     * @var string
     */
    private const DS = DIRECTORY_SEPARATOR;

    /**
     * The base path of the project.
     * This is used as the starting point for generating other paths within the project.
     * It is initialized via the `init()` method and is used globally by the `getPath()` method.
     *
     * @var string
     */
    private static string $basePath = '';

    /**
     * Initialize the base path.
     *
     * This method sets the base path for the project. If no path is provided, the default value is an empty string.
     * This base path will be used by the `getPath()` method to build full paths for different resources.
     *
     * Example usage:
     * ```php
     * Path::init('/var/www/project');
     * ```
     *
     * @param string|null $basePath The base path of the project.
     */
    public static function init(?string $basePath = null): void
    {
        self::$basePath = rtrim($basePath ?? '', static::DS);
    }

    /**
     * Get an array of full paths by mapping each path through `getPath()`.
     *
     * This method takes an array of relative paths and converts each of them
     * into a full path using `getPath()`. It ensures that all paths are correctly
     * resolved based on the initialized base path.
     *
     * Example usage:
     * ```php
     * Path::init('/var/www/project');
     *
     * $paths = ['resources/views', 'storage/logs'];
     * print_r(Path::getPaths($paths));
     * // Output:
     * // [
     * //     "/var/www/project/resources/views",
     * //     "/var/www/project/storage/logs"
     * // ]
     * ```
     *
     * @param array $paths An array of relative paths to resolve.
     * @return array An array of fully resolved paths.
     */
    public static function getPaths(array $paths): array
    {
        return array_map(fn ($path) => self::getPath($path), $paths);
    }

    /**
     * Get the full path by joining the base path with optional subdirectories or a file.
     *
     * - If `$fullPath` contains dots (`.`), it is converted into a directory structure.
     * - `$subPath`, if provided, must be a filename with an extension; otherwise, an exception is thrown.
     *
     * Example usage:
     * ```php
     * // Assuming base path is '/var/www/project'
     * Path::init('/var/www/project');
     *
     * echo Path::getPath('bootstrap.cache');
     * // Output: '/var/www/project/bootstrap/cache'
     *
     * echo Path::getPath('database.sqlite', 'sqlite.sqlite');
     * // Output: '/var/www/project/database/sqlite/sqlite.sqlite'
     * ```
     *
     * @param string|null $fullPath The main directory path, which can use `.` notation to indicate subdirectories.
     * @param string|null $fileName The optional filename (must include an extension).
     * @return string The fully resolved path.
     * @throws InvalidArgumentException If `$subPath` is provided but does not have a valid extension.
     */
    public static function getPath(?string $fullPath = null, ?string $fileName = null): string
    {
        if (empty($fullPath)) {
            return self::$basePath . static::DS;
        }

        $fullPath = str_replace('.', static::DS, $fullPath);
        $fullPath = rtrim($fullPath, static::DS) . static::DS;

        if ($fileName !== null) {
            $fileName = ltrim($fileName, '/\\:*?"<>|');
            if (pathinfo($fileName, PATHINFO_EXTENSION) === '') {
                throw new InvalidArgumentException("Second parameter must be a filename with an extension.");
            }
        }

        return self::joinPaths(self::$basePath, $fullPath, $fileName);
    }

    /**
     * Join the given paths.
     *
     * This method takes a base path and additional paths as arguments, then joins them together, ensuring
     * proper directory separators between each path component. It trims any extra separators from the start
     * and end of each path before joining them.
     *
     * @param string      $basePath The base path.
     * @param string|null ...$paths Additional paths.
     * @return string The joined path.
     */
    private static function joinPaths(string $basePath, ?string ...$paths): string
    {
        foreach ($paths as $index => $path) {
            if (empty($path)) {
                unset($paths[$index]);
            } else {
                $paths[$index] = static::DS . ltrim($path, static::DS);
            }
        }

        return rtrim($basePath, static::DS) . implode('', $paths);
    }
}
