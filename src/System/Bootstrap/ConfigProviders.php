<?php

/**
 * Part of Omega - Bootstrap Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace System\Bootstrap;

use DI\DependencyException;
use DI\NotFoundException;
use System\Application\Application;
use System\Config\ConfigRepository;

use function array_merge;
use function date_default_timezone_set;
use function file_exists;
use function glob;

/**
 * Loads and merges application configuration settings, including cached configurations,
 * and sets the default timezone.
 *
 * @category  System
 * @package   Bootstrap
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class ConfigProviders
{
    /**
     * Loads configuration settings from the cache if available.
     *
     * If no cache is found, it scans the configuration directory for `.config.php`
     * files and merges them into the application’s configuration. Loads the final
     * configuration into the application and sets the default timezone.
     *
     * @param Application $app Holds the current Application instance.
     * @return void
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found
     */
    public function bootstrap(Application $app): void
    {
        $configPath = $app->getConfigPath();
        $config     = $app->defaultConfig();
        $hasCache   = false;

        if (file_exists($file = $app->getApplicationCachePath() . 'config.php')) {
            $config   = array_merge($config, require $file);
            $hasCache = true;
        }

        if (false === $hasCache) {
            foreach (glob("{$configPath}*.php") as $path) {
                foreach (include $path as $key => $value) {
                    $config[$key] = $value;
                }
            }
        }

        $app->loadConfig(new ConfigRepository($config));

        date_default_timezone_set($config['time_zone'] ?? 'UTC');
    }
}
