<?php

/**
 * Part of Omega - Console Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace System\Console\Commands;

use DI\DependencyException;
use DI\NotFoundException;
use System\Console\Command;
use System\Console\Style\ProgressBar;
use System\Console\Traits\PrintHelpTrait;
use System\Text\Str;
use System\View\Templator;

use function array_key_exists;
use function count;
use function glob;
use function is_file;
use function microtime;
use function round;
use function System\Application\compiled_view_path;
use function System\Application\view_path;
use function System\Console\info;
use function System\Console\ok;
use function System\Console\warn;
use function unlink;

/**
 * Class ViewCommand
 *
 * This class provides commands for managing the application's view cache.
 * It includes functionality to optimize templates by caching them and to clear
 * cached view files. The commands are `view:cache` for building the view cache
 * and `view:clear` for clearing the cached files.
 *
 * @category   System
 * @package    Console
 * @subpackage Commands
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 *
 * @property string|null $prefix
 */
class ViewCommand extends Command
{
    use PrintHelpTrait;

    /**
     * Command registration details.
     *
     * This array defines the commands available for managing the application's
     * maintenance mode. Each command is associated with a pattern and a function
     * that handles the command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'pattern' => 'view:cache',
            'fn'      => [ViewCommand::class, 'cache'],
            'default' => [
                'prefix' => '*.php',
            ],
        ], [
            'pattern' => 'view:clear',
            'fn'      => [ViewCommand::class, 'clear'],
            'default' => [
                'prefix' => '*.php',
            ],
        ],
    ];

    /**
     * Provides help documentation for the command.
     *
     * This method returns an array with information about available commands
     * and options. It describes the two main commands (`down` and `up`) for
     * managing maintenance mode.
     *
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp(): array
    {
        return [
            'commands'  => [
                'view:cache' => 'Create all templator template (optimize)',
                'view:clear' => 'Clear all cached view file',
            ],
            'options'   => [
                '--prefix' => 'Finding file by pattern given',
            ],
            'relation'  => [
                'view:cache' => ['--prefix'],
                'view:clear' => ['--prefix'],
            ],
        ];
    }

    /**
     * Compiles and caches the view templates.
     *
     * This method retrieves all the view files matching the specified prefix
     * and compiles them into cache. It also tracks progress and displays
     * the time taken to compile the files.
     *
     * @param Templator $templator The templator instance used for compiling the view files.
     * @return int Returns 0 on success, 1 on failure.
     * @throws DependencyException If a required dependency is missing.
     * @throws NotFoundException If the required files are not found.
     */
    public function cache(Templator $templator): int
    {
        $files = glob(view_path() . $this->prefix);
        if (false === $files) {
            return 1;
        }
        info('build compiler cache')->out(false);
        $count     = 0;
        $progress = new ProgressBar(':progress :percent - :current', [
            ':current' => fn ($current, $max): string => array_key_exists($current, $files)
                ? Str::replace($files[$current], view_path(), '')
                : '',
        ]);

        $progress->maks = count($files);
        $watchStart     = microtime(true);
        foreach ($files as $file) {
            if (is_file($file)) {
                $filename = Str::replace($file, view_path(), '');
                $templator->compile($filename);
                $count++;
            }
            $progress->current++;
            $time                = round(microtime(true) - $watchStart, 3) * 1000;
            $progress->complete = static fn (): string => (string) ok(
                "Success, {$count} file compiled ({$time} ms)."
            );
            $progress->tick();
        }

        return 0;
    }

    /**
     * Clears all cached view files.
     *
     * This method finds and deletes the cached view files based on the specified
     * prefix. It also tracks progress and displays the time taken to clear the cache.
     *
     * @return int Returns 0 on success, 1 on failure.
     * @throws DependencyException If a required dependency is missing.
     * @throws NotFoundException If the cache files are not found.
     */
    public function clear(): int
    {
        warn('Clear cache file in ' . compiled_view_path())->out(false);
        $files = glob(compiled_view_path() . DIRECTORY_SEPARATOR . $this->prefix);

        if (false === $files || 0 === count($files)) {
            warn('No file cache clear.')->out();

            return 1;
        }

        $count     = 0;
        $progress = new ProgressBar(':progress :percent - :current', [
            ':current' => fn ($current, $max): string => array_key_exists($current, $files)
                ? Str::replace($files[$current], view_path(), '')
                : '',
        ]);

        $progress->maks = count($files);
        $watch_start    = microtime(true);
        foreach ($files as $file) {
            if (is_file($file)) {
                $count += unlink($file) ? 1 : 0;
            }
            $progress->current++;
            $time                = round(microtime(true) - $watch_start, 3) * 1000;
            $progress->complete = static fn (): string => (string) ok("Success, {$count} cache clear ({$time} ms).");
            $progress->tick();
        }

        return 0;
    }
}
