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
use System\Application\Application;
use System\Config\ConfigProviders;
use System\Config\ConfigRepository;

use function file_exists;
use function file_put_contents;
use function System\Console\fail;
use function System\Console\ok;
use function unlink;
use function var_export;

use const PHP_EOL;

/**
 * The ConfigCommand class handles the caching and clearing of application configuration.
 *
 * This command provides two main functions: caching the configuration to a file
 * (config:cache) and clearing the cached configuration (config:clear). It bootstraps the necessary
 * configuration providers, retrieves the current configuration, and writes it to a cache file.
 * Additionally, it allows the removal of the cached configuration file.
 *
 * @category   System
 * @package    Console
 * @subpackage Commands
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
class ConfigCommand extends Command
{
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
            'pattern' => 'config:cache',
            'fn'      => [self::class, 'main'],
        ], [
            'pattern' => 'config:clear',
            'fn'      => [self::class, 'clear'],
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
                'config:cache' => 'Build cache application config',
                'config:clear' => 'Remove cached application config',
            ],
            'options'   => [],
            'relation'  => [],
        ];
    }

    /**
     * Caches the application configuration.
     *
     * This method bootstraps the configuration providers, clears any existing config cache,
     * retrieves the current configuration as an array, and writes it to a cache file.
     * It returns 0 on success and 1 on failure.
     *
     * @return int Exit status code (0 for success, 1 for failure).
     * @throws NotFoundException
     * @throws DependencyException
     */
    public function main(): int
    {
        $app = Application::getInstance();
        (new ConfigProviders())->bootstrap($app);

        $this->clear();
        $config       = $app->get(ConfigRepository::class)->toArray();
        $cachedConfig = '<?php return ' . var_export($config, true) . ';' . PHP_EOL;
        if (file_put_contents($app->getApplicationCachePath() . 'config.php', $cachedConfig)) {
            ok('Config file has successfully created.')->out();

            return 0;
        }
        fail('Cant build config cache.')->out();

        return 1;
    }

    /**
     * Clears the cached configuration file.
     *
     * This method checks if the configuration cache file exists and, if so, deletes it.
     * It returns 0 if the file was successfully removed and 1 if the file did not exist.
     *
     * @return int Exit status code (0 for success, 1 for failure).
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function clear(): int
    {
        if (file_exists($file = Application::getInstance()->getApplicationCachePath() . 'config.php')) {
            @unlink($file);
            ok('Clear config file has successfully.')->out();

            return 0;
        }

        return 1;
    }
}
