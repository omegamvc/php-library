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
use Exception;
use System\Console\Command;
use System\Console\Prompt;
use System\Console\Traits\PrintHelpTrait;
use System\Template\Generate;
use System\Template\Method;
use Throwable;

use function class_exists;
use function file_exists;
use function file_put_contents;
use function System\Application\app;
use function System\Application\seeder_path;
use function System\Console\fail;
use function System\Console\info;
use function System\Console\ok;
use function System\Console\style;
use function System\Console\warn;

/**
 * Class SeedCommand
 *
 * This class provides commands for working with database seeders in the application.
 * It allows users to run existing seeders (`db:seed`) and to create new seeder classes (`make:seed`).
 * The `db:seed` command runs the seeding process, while the `make:seed` command generates a new seeder class.
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
 * @property string|null $class
 * @property bool|null   $force
 */
class SeedCommand extends Command
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
            'pattern' => 'db:seed',
            'fn'     => [self::class, 'main'],
        ], [
            'pattern' => 'make:seed',
            'fn'      => [self::class, 'make'],
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
                'db:seed'   => 'Run seeding',
                'make:seed' => 'Create new seeder class',
            ],
            'options'   => [
                '--class'      => 'Target class (will add `Database\\Seeders\\`)',
                '--name-space' => 'Target class with full namespace',
            ],
            'relation'  => [
                'db:seed' => ['--class', '--name-space'],
            ],
        ];
    }

    /**
     * Prompts the user for confirmation to run the seeder in a production environment.
     *
     * This method checks if the application is in development mode or if the user forces the seeding action.
     * If the application is not in development, the user is asked if they want to run the seeder in production.
     *
     * @return bool Returns true if seeding is allowed in production, false otherwise.
     * @throws DependencyException
     * @throws NotFoundException
     * @throws Exception
     */
    private function runInDev(): bool
    {
        if (app()->isDev() || $this->force) {
            return true;
        }

        /* @var bool */
        return (new Prompt(style('Running seeder in production?')->textRed(), [
            'yes' => fn () => true,
            'no'  => fn () => false,
        ], 'no'))
            ->selection([
                style('yes')->textDim(),
                ' no',
            ])
            ->option();
    }


    /**
     * Runs the seeding process.
     *
     * This method handles the execution of the seeder by determining the appropriate class or namespace to use.
     * It will run the seeder class specified by the user or use the default `DatabaseSeeder` class.
     *
     * @return int Returns 0 on success, 1 on failure, 2 if seeding is not allowed in production.
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function main(): int
    {
        $class     = $this->class;
        $namespace = $this->option('name-space');
        $exit      = 0;

        if (false === $this->runInDev()) {
            return 2;
        }

        if (null !== $class && null !== $namespace) {
            warn('Use only one class or namespace, be specific.')->out(false);

            return 1;
        }

        if (null === $class && null !== $namespace) {
            $class = $namespace;
        }

        if ($class !== null && null === $namespace) {
            $class = 'Database\\Seeders\\' . $class;
        }

        if (null === $class && null === $namespace) {
            $class = 'Database\\Seeders\\DatabaseSeeder';
        }

        if (false === class_exists($class)) {
            warn("Class '{$class}::class' doest exist.")->out(false);

            return 1;
        }

        info('Running seeders...')->out(false);
        try {
            app()->call([$class, 'run']);

            ok('Success run seeder ' . $class)->out(false);
        } catch (Throwable $th) {
            warn($th->getMessage())->out(false);
            $exit = 1;
        }

        return $exit;
    }

    /**
     * Creates a new seeder class.
     *
     * This method generates a new seeder class file with the specified class name. It also ensures that the file
     * does not already exist unless the `--force` option is provided to overwrite it.
     *
     * @return int Returns 0 on success, 1 on failure.
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function make(): int
    {
        $class = $this->option[0] ?? null;

        if (null === $class) {
            warn('command make:seed require class name')->out(false);

            return 1;
        }

        if (file_exists(seeder_path() . $class . '.php') && !$this->force) {
            warn("Class '{$class}::class' already exist.")->out(false);

            return 1;
        }

        $make = new Generate($class);
        $make->tabIndent(' ');
        $make->tabSize(4);
        $make->namespace('Database\Seeders');
        $make->use('System\Database\Seeder\Seeder');
        $make->extend('Seeder');
        $make->setEndWithNewLine();
        $make->addMethod('run')
            //->visibility(Method::PUBLIC_)
            ->visibility(Method::PUBLIC_)
            ->setReturnType('void')
            ->body('// run some insert db');

        if (file_put_contents(seeder_path() . $class . '.php', $make->__toString())) {
            ok('Success create seeder')->out();

            return 0;
        }

        fail('Fail to create seeder')->out();

        return 1;
    }
}
