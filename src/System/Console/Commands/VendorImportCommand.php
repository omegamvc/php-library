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
use System\Console\Style\ProgressBar;
use System\Console\Traits\PrintHelpTrait;
use System\Container\ServiceProvider\AbstractServiceProvider;

use function count;
use function System\Console\ok;

/**
 * Class VendorImportCommand
 *
 * This class provides a command for importing packages into the vendor directory.
 * It supports importing files or directories based on a specified tag or imports all available items by default.
 * Additionally, it provides a progress bar to track the status of the import process.
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
 * @property bool   $force Whether to force the import, overwriting existing files.
 * @property string $tag   The tag to identify specific commands to run.
 */
class VendorImportCommand extends Command
{
    use PrintHelpTrait;

    /**
     * Progress bar for tracking import status.
     *
     * This property holds the progress bar object that is used to display the status
     * of the import process in the console.
     *
     * @var ProgressBar
     */
    private ProgressBar $status;

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
            'pattern' => 'vendor:import',
            'fn'      => [self::class, 'main'],
            'default' => [
                'tag'   => '*',
                'force' => false,
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
                'vendor:import' => 'Import package in vendor.',
            ],
            'options'   => [
                '--tag' => 'Specify the tag to run specific commands.',
            ],
            'relation'  => [
                'vendor:import' => ['--tag', '--force'],
            ],
        ];
    }

    /**
     * Main method to execute the import command.
     *
     * This method initiates the import process by calling `importItem` with the available modules.
     * It sets up the progress bar to track the status of the import process.
     *
     * @return int
     */
    public function main(): int
    {
        $this->status = new ProgressBar();
        $this->importItem(AbstractServiceProvider::getModules());

        return 0;
    }

    /**
     * Import specified modules (files or directories).
     *
     * This method iterates over the provided modules and imports files or directories
     * based on the specified tag or all modules if the tag is '*'.
     *
     * @param array<string, array<string, string>> $modules
     * @return void
     */
    protected function importItem(array $modules): void
    {
        $this->status->maks = count($modules);
        $current            = 0;
        $added              = 0;

        foreach ($modules as $tag => $module) {
            $current++;

            if ($tag === $this->tag || $this->tag === '*') {
                foreach ($module as $from => $to) {
                    $added++;
                    if (is_dir($from)) {
                        $status = AbstractServiceProvider::importDir($from, $to, $this->force);
                        $this->status($current, $status, $from, $to);

                        continue 2;
                    }

                    $status = AbstractServiceProvider::importFile($from, $to, $this->force);
                    $this->status($current, $status, $from, $to);
                }
            }
        }

        if ($current > 0) {
            ok('Done ')->push($added)->textYellow()->push(' file/folder has been added.')->out(false);
        }
    }


    /**
     * Update the console with the progress bar status.
     *
     * This method updates the progress bar and displays the current status of the file/directory being imported.
     * It is called after each file or directory is processed.
     *
     * @param int $current Current step of the import process.
     * @param bool $success Indicates whether the import operation was successful.
     * @param string $from The source path of the file or directory being imported.
     * @param string $to The target path where the file or directory is being imported.
     * @return void
     */
    protected function status(int $current, bool $success, string $from, string $to): void
    {
        if (false === $success) {
            return;
        }

        $this->status->current = $current;
        $this->status->tickWith(':progress :percent :status', [
            'status' => fn (int $current, int $max): string => "Copying file/directory from '{$from}' to '{$to}'.",
        ]);
    }
}
