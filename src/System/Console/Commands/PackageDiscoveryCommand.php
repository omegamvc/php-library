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

use System\Console\Command;
use System\Console\Style\Style;
use System\Application\Application;
use System\Application\PackageManifest;
use Throwable;

use function array_keys;
use function strlen;
use function System\Console\fail;
use function System\Console\info;

/**
 * Class PackageDiscoveryCommand
 *
 * This class handles the discovery of packages in a Composer environment. It defines the command
 * `package:discovery` and provides functionality for generating a package manifest cache file.
 * The command builds a cache of available packages and displays their status.
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
class PackageDiscoveryCommand extends Command
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
            'pattern' => 'package:discovery',
            'fn'      => [self::class, 'discovery'],
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
                'package:discovery' => 'Discovery package in composer',
            ],
            'options'   => [],
            'relation'  => [],
        ];
    }

    /**
     * Discovers and builds the package manifest cache.
     *
     * This method attempts to build the package cache by invoking the build method on the package manifest.
     * It then retrieves the list of packages and displays their statuses as "DONE" if successful. In case of
     * an error, it returns a failure status.
     *
     * @param Application $app The application instance.
     * @return int Returns 0 if the discovery and cache creation is successful, or 1 if an error occurs.
     */
    public function discovery(Application $app): int
    {
        $package = $app[PackageManifest::class];
        info('Trying build package cache.')->out(false);
        try {
            $package->build();

            $packages = (fn () => $this->{'getPackageManifest'}())->call($package) ?? [];
            $style    = new Style();
            foreach (array_keys($packages) as $name) {
                $length = $this->getWidth(40, 60) - strlen($name) - 4;
                $style->push($name)->repeat('.', $length)->textDim()->push('DONE')->textGreen()->newLines();
            }
            $style->out(false);
        } catch (Throwable $th) {
            fail($th->getMessage())->out(false);
            fail('Can\'t create package manifest cache file.')->out();

            return 1;
        }

        return 0;
    }
}
