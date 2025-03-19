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

namespace System\Console;

use System\Application\Application;
use System\Config\ConfigRepository;

/**
 * Util class.
 *
 * The `Util` class is responsible for mapping and loading console commands from the
 * application's configuration. It retrieves the list of commands stored in the configuration
 * repository and converts them into an array of CommandMap instances, making them accessible
 * for execution within the console environment.
 *
 * @category  System
 * @package   Console
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html  GPL V3.0+
 * @version   2.0.0
 */
class Util
{
    /**
     * Load and map console commands from configuration.
     *
     * This method retrieves the list of commands stored in the configuration
     * and converts them into an array of `CommandMap` objects.
     *
     * @param Application $app Holds the application instance containing the configuration repository.
     * @return CommandMap[] Return an array of mapped console commands.
     */
    public static function loadCommandFromConfig(Application $app): array
    {
        $commandMap = [];

        foreach ($app[ConfigRepository::class]->get('commands', []) as $commands) {
            foreach ($commands as $command) {
                $commandMap[] = new CommandMap($command);
            }
        }

        return $commandMap;
    }
}
