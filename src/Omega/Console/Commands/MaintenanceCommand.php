<?php

declare(strict_types=1);

namespace Omega\Console\Commands;

use Omega\Console\Command;
use Omega\Console\Traits\PrintHelpTrait;

use function Omega\Console\info;
use function Omega\Console\ok;
use function Omega\Console\warn;

class MaintenanceCommand extends Command
{
    use PrintHelpTrait;

    /**
     * Register command.
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
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp(): array
    {
        return [
            'commands'  => [
                'down' => 'Active maintenance mode',
                'up'   => 'Deactivate maintenance mode',
            ],
            'options'   => [],
            'relation'  => [],
        ];
    }

    public function down(): int
    {
        if (app()->isDownMaintenanceMode()) {
            warn('Application is alredy under maintenance mode.')->out();

            return 1;
        }

        if (false === file_exists($down = storage_path() . 'app' . DIRECTORY_SEPARATOR . 'down')) {
            file_put_contents($down, file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'down'));
        }

        file_put_contents(storage_path() . 'app' . DIRECTORY_SEPARATOR . 'maintenance.php', file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'maintenance'));
        ok('Successfull, your apllication now in under maintenance.')->out();

        return 0;
    }

    public function up(): int
    {
        if (false === app()->isDownMaintenanceMode()) {
            warn('Application is not in maintenance mode.')->out();

            return 1;
        }

        if (false === unlink($up = storage_path() . 'app' . DIRECTORY_SEPARATOR . 'maintenance.php')) {
            warn('Application stil maintenance mode.')->out(false);
            info("Remove manualy mantenance file in `{$up}`.")->out();

            return 1;
        }

        ok('Successfull, your apllication now live.')->out();

        return 0;
    }
}
