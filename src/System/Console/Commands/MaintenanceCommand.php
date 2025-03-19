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
use System\Console\Traits\PrintHelpTrait;

use function System\Application\app;
use function System\Application\storage_path;
use function System\Console\info;
use function System\Console\ok;
use function System\Console\warn;

/**
 * The MaintenanceCommand class provides commands to manage the application's
 * maintenance mode. It allows the application to be put into or taken out of
 * maintenance mode by creating or removing specific files in the storage path.
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
class MaintenanceCommand extends Command
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
            'pattern' => 'up',
            'fn'      => [self::class, 'up'],
        ], [
            'pattern' => 'down',
            'fn'      => [self::class, 'down'],
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
                'down' => 'Activate maintenance mode',
                'up'   => 'Deactivate maintenance mode',
            ],
            'options'   => [],
            'relation'  => [],
        ];
    }

    /**
     * Puts the application into maintenance mode.
     *
     * This method checks if the application is already in maintenance mode.
     * If not, it creates the necessary files to put the application into
     * maintenance mode. It creates a `down` file and a `maintenance.php` file
     * in the storage path to signal that the application is under maintenance.
     *
     * @return int Exit status code (0 for success, 1 for failure).
     * @throws DependencyException If there is an issue with the dependencies.
     * @throws NotFoundException If a required resource is not found.
     */
    public function down(): int
    {
        if (app()->isDownMaintenanceMode()) {
            warn('Application is already under maintenance mode.')->out();

            return 1;
        }

        if (false === file_exists($down = storage_path() . 'app' . DIRECTORY_SEPARATOR . 'down')) {
            file_put_contents(
                $down,
                file_get_contents(
                    __DIR__
                    . DIRECTORY_SEPARATOR
                    . 'stubs'
                    . DIRECTORY_SEPARATOR
                    . 'down'
                )
            );
        }

        file_put_contents(
            storage_path()
            . 'app'
            . DIRECTORY_SEPARATOR
            . 'maintenance.php',
            file_get_contents(
                __DIR__
                . DIRECTORY_SEPARATOR
                . 'stubs'
                . DIRECTORY_SEPARATOR
                . 'maintenance'
            )
        );

        ok('Successfully, your application now in under maintenance.')->out();

        return 0;
    }


    /**
     * Takes the application out of maintenance mode.
     *
     * This method checks if the application is currently in maintenance mode.
     * If the application is in maintenance mode, it removes the necessary
     * files to bring the application back to normal operation. It deletes
     * the `maintenance.php` file and returns the application to a live state.
     *
     * @return int Exit status code (0 for success, 1 for failure).
     * @throws DependencyException If there is an issue with the dependencies.
     * @throws NotFoundException If a required resource is not found.
     */
    public function up(): int
    {
        if (false === app()->isDownMaintenanceMode()) {
            warn('Application is not in maintenance mode.')->out();

            return 1;
        }

        if (false === unlink($up = storage_path() . 'app' . DIRECTORY_SEPARATOR . 'maintenance.php')) {
            warn('Application still maintenance mode.')->out(false);
            info("Remove manually maintenance file in `{$up}`.")->out();

            return 1;
        }

        ok('Successfully, your application now live.')->out();

        return 0;
    }
}
