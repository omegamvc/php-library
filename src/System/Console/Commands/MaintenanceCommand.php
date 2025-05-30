<?php

declare(strict_types=1);

namespace System\Console\Commands;

use System\Console\Command;
use System\Console\Traits\PrintHelpTrait;

use function System\Console\info;
use function System\Console\ok;
use function System\Console\warn;

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
            'fn' => [MaintenanceCommand::class, 'up'],
        ], [
            'pattern' => 'down',
            'fn' => [MaintenanceCommand::class, 'down'],
        ],
    ];

    /**
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

    public function down(): int
    {
        if (app()->isDownMaintenanceMode()) {
            warn('Application is already under maintenance mode.')->out();

            return 1;
        }

        if (false === file_exists($down = get_storage_path('app/down'))) {
            file_put_contents($down, file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'down'));
        }

        file_put_contents(get_storage_path('app/maintenance.php'), file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'maintenance'));
        ok('Successfully, your application now in under maintenance.')->out();

        return 0;
    }

    public function up(): int
    {
        if (false === app()->isDownMaintenanceMode()) {
            warn('Application is not in maintenance mode.')->out();

            return 1;
        }

        if (false === unlink($up = get_storage_path('app/maintenance.php'))) {
            warn('Application still maintenance mode.')->out(false);
            info("Remove manually maintenance file in `{$up}`.")->out();

            return 1;
        }

        ok('Successfully, your apllication now live.')->out();

        return 0;
    }
}
