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

use Exception;
use System\Cache\CacheManager;
use System\Console\Command;
use System\Console\Traits\CommandTrait;
use System\Application\Application;

use function array_keys;
use function is_array;
use function System\Console\fail;
use function System\Console\info;
use function System\Console\ok;

/**
 * Class ClearCacheCommand
 *
 * This command is responsible for clearing the cache of the application.
 * It allows clearing the default cache driver, specific drivers, or all registered cache drivers.
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
 * @property bool $update
 * @property bool $force
 */
class ClearCacheCommand extends Command
{
    use CommandTrait;

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
            'pattern' => 'cache:clear',
            'fn'      => [ClearCacheCommand::class, 'clear'],
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
                'cache:clear' => 'Clear cache (default drive)',
            ],
            'options'   => [
                '--all'       => 'Clear all registered cache driver.',
                '--drivers'   => 'Clear specific driver name.',
            ],
            'relation'  => [
                'cache:clear' => ['--all', '--drivers'],
            ],
        ];
    }

    /**
     * Executes the cache clearing process.
     *
     * @param Application $app The application instance.
     * @return int Returns 0 on success, 1 on failure.
     * @throws Exception If an error occurs during cache clearing.
     */
    public function clear(Application $app): int
    {
        if (false === $app->has('cache')) {
            fail('Cache is not set yet.')->out();

            return 1;
        }

        /** @var CacheManager|null $cache */
        $cache = $app['cache'];

        /** @var string[]|null $drivers */
        $drivers = null;

        /** @var string[]|string|bool $userDriver */
        $userDrivers = $this->option('drivers', false);

        if ($this->option('all', false) && false === $userDrivers) {
            $drivers = array_keys(
                (fn (): array|string|bool|int => $this->{'driver'})->call($cache)
            );
        }

        if ($userDrivers) {
            $drivers = is_array($userDrivers) ? $userDrivers : [$userDrivers];
        }

        if (null === $drivers) {
            $cache->driver()->clear();
            ok('Done default cache driver has been clear.')->out(false);

            return 0;
        }

        foreach ($drivers as $driver) {
            $cache->driver($driver)->clear();
            info("clear '{$driver}' driver.")->out(false);
        }

        return 0;
    }
}
