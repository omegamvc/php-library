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
use System\Console\Traits\CommandTrait;
use System\Support\Facades\DB;
use System\Template\Generate;
use System\Template\Property;
use Throwable;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function mkdir;
use function now;
use function preg_replace;
use function str_replace;
use function strtolower;
use function System\Application\commands_path;
use function System\Application\config_path;
use function System\Application\controllers_path;
use function System\Application\migration_path;
use function System\Application\model_path;
use function System\Application\services_path;
use function System\Application\view_path;
use function System\Console\fail;
use function System\Console\info;
use function System\Console\ok;
use function System\Console\text;
use function System\Console\warn;
use function ucfirst;

/**
 * The make command is a powerful tool for quickly scaffolding various components of your
 * application. It simplifies the process of creating essential files like controllers, views,
 * services, models, migrations, and commands based on predefined templates. This command reduces
 * repetitive tasks by automatically generating the necessary files with a consistent structure,
 * saving time during the development process. It allows for customizable templates and provides clear
 * feedback on the success or failure of the file creation process.
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
class MakeCommand extends Command
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
            'pattern' => 'make:controller',
            'fn'      => [MakeCommand::class, 'makeController'],
        ], [
            'pattern' => 'make:view',
            'fn'      => [MakeCommand::class, 'makeView'],
        ], [
            'pattern' => 'make:services',
            'fn'      => [MakeCommand::class, 'makeServices'],
        ], [
            'pattern' => 'make:model',
            'fn'      => [MakeCommand::class, 'makeModel'],
        ], [
            'pattern' => 'make:command',
            'fn'      => [MakeCommand::class, 'makeCommand'],
        ], [
            'pattern' => 'make:migration',
            'fn'      => [MakeCommand::class, 'makeMigration'],
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
                'make:controller' => 'Generate new controller',
                'make:view'       => 'Generate new view',
                'make:service'    => 'Generate new service',
                'make:model'      => 'Generate new model',
                'make:command'    => 'Generate new command',
                'make:migration'  => 'Generate new migration file',
            ],
            'options'   => [
                '--table-name' => 'Set table column when creating model.',
                '--update'     => 'Generate migration file with alter (update).',
                '--force'      => 'Force to creating template.',
            ],
            'relation'  => [
                'make:controller' => ['[controller_name]'],
                'make:view'       => ['[view_name]'],
                'make:service'    => ['[service_name]'],
                'make:model'      => ['[model_name]', '--table-name', '--force'],
                'make:command'    => ['[command_name]'],
                'make:migration'  => ['[table_name]', '--update'],
            ],
        ];
    }

    /**
     * Creates a controller file.
     *
     * This method generates a controller file based on a template and saves it to
     * the appropriate directory. The controller's name is provided as an option
     * to the command.
     *
     * @return int Exit status code (0 for success, 1 for failure).
     * @throws DependencyException If there is an issue with dependencies.
     * @throws NotFoundException If a required resource is not found.
     */
    public function makeController(): int
    {
        info('Making controller file...')->out(false);

        $success = $this->makeTemplate($this->option[0], [
            'template_location' => __DIR__ . '/stubs/controller',
            'save_location'     => controllers_path(),
            'pattern'           => '__controller__',
            'suffix'            => 'Controller.php',
        ]);

        if ($success) {
            ok('Finish created controller')->out();

            return 0;
        }

        fail('Failed Create controller')->out();

        return 1;
    }

    /**
     * Creates a view file.
     *
     * This method generates a view file based on a template and saves it to the
     * appropriate directory. The view's name is provided as an option to the command.
     *
     * @return int Exit status code (0 for success, 1 for failure).
     * @throws DependencyException If there is an issue with dependencies.
     * @throws NotFoundException If a required resource is not found.
     */
    public function makeView(): int
    {
        info('Making view file...')->out(false);

        $success = $this->makeTemplate($this->option[0], [
            'template_location' => __DIR__ . '/stubs/view',
            'save_location'     => view_path(),
            'pattern'           => '__view__',
            'suffix'            => '.template.php',
        ]);

        if ($success) {
            ok('Finish created view file')->out();

            return 0;
        }

        fail('Failed Create view file')->out();

        return 1;
    }

    /**
     * Creates a service file.
     *
     * This method generates a service file based on a template and saves it to
     * the appropriate directory. The service's name is provided as an option
     * to the command.
     *
     * @return int Exit status code (0 for success, 1 for failure).
     * @throws DependencyException If there is an issue with dependencies.
     * @throws NotFoundException If a required resource is not found.
     */
    public function makeServices(): int
    {
        info('Making service file...')->out(false);

        $success = $this->makeTemplate($this->option[0], [
            'template_location' => __DIR__ . '/stubs/service',
            'save_location'     => services_path(),
            'pattern'           => '__service__',
            'suffix'            => 'Service.php',
        ]);

        if ($success) {
            ok('Finish created services file')->out();

            return 0;
        }

        fail('Failed Create services file')->out();

        return 1;
    }

    /**
     * Creates a model file.
     *
     * This method generates a model file based on a template, incorporating the
     * table name and column information from the database. The model's name is
     * provided as an option to the command. If the model file already exists and
     * the force option is not enabled, the method will fail.
     *
     * @return int Exit status code (0 for success, 1 for failure).
     * @throws DependencyException If there is an issue with dependencies.
     * @throws NotFoundException If a required resource is not found.
     */
    public function makeModel(): int
    {
        info('Making model file...')->out(false);
        $name          = ucfirst($this->option[0]);
        $modelLocation = model_path() . $name . '.php';

        if (file_exists($modelLocation) && false === $this->option('force', false)) {
            warn('File already exist')->out(false);
            fail('Failed Create model file')->out();

            return 1;
        }

        info('Creating Model class in ' . $modelLocation)->out(false);

        $class = new Generate($name);
        $class->customizeTemplate(
            "<?php\n\ndeclare(strict_types=1);\n{{before}}{{comment}}\n{{rule}}class\40{{head}}\n{\n{{body}}}{{end}}"
        );
        $class->tabSize(4);
        $class->tabIndent(' ');
        $class->setEndWithNewLine();
        $class->namespace('App\\Models');
        $class->uses(['System\Database\MyModel\Model']);
        $class->extend('Model');

        $primaryKey = 'id';
        $tableName  = $this->option[0];
        if ($this->option('table-name', false)) {
            $tableName = $this->option('table-name');
            info("Getting Information from table {$tableName}.")->out(false);
            try {
                foreach (DB::table($tableName)->info() as $column) {
                    $class->addComment('@property mixed $' . $column['COLUMN_NAME']);
                    if ('PRI' === $column['COLUMN_KEY']) {
                        $primaryKey = $column['COLUMN_NAME'];
                    }
                }
            } catch (Throwable $th) {
                warn($th->getMessage())->out(false);
            }
        }

        $class->addProperty(
            'tableName'
        )->visibility(
            Property::PROTECTED_
        )->dataType(
            'string'
        )->expecting(
            " = '{$tableName}'"
        );

        $class->addProperty(
            'primaryKey'
        )->visibility(
            Property::PROTECTED_
        )->dataType(
            'string'
        )->expecting(
            "= '{$primaryKey}'"
        );

        if (false === file_put_contents($modelLocation, $class->generate())) {
            fail('Failed Create model file')->out();

            return 1;
        }

        ok("Finish created model file `App\\Models\\{$name}`")->out();

        return 0;
    }

    /**
     * Replaces the template with the new class/resource.
     *
     * This method handles the process of replacing placeholders in a template
     * with the provided class or file name and saves the result to the specified
     * location.
     *
     * @param string                $argument    Name of the class or file.
     * @param array<string, string> $makeOption Configuration options for the template replacement.
     * @param string                $folder      Folder name for saving the file.
     * @return bool True if the template was successfully copied and modified.
     */
    private function makeTemplate(string $argument, array $makeOption, string $folder = ''): bool
    {
        $folder = ucfirst($folder);
        if (file_exists($fileName = $makeOption['save_location'] . $folder . $argument . $makeOption['suffix'])) {
            warn('File already exist')->out(false);

            return false;
        }

        if ('' !== $folder && !is_dir($makeOption['save_location'] . $folder)) {
            mkdir($makeOption['save_location'] . $folder);
        }

        $getTemplate = file_get_contents($makeOption['template_location']);
        $getTemplate = str_replace($makeOption['pattern'], ucfirst($argument), $getTemplate);
        $getTemplate = preg_replace('/^.+\n/', '', $getTemplate);
        $isCopied    = file_put_contents($fileName, $getTemplate);

        return !($isCopied === false);
        //return $isCopied === false ? false : true;
    }

    /**
     * Creates a command file.
     *
     * This method generates a command file based on a template and saves it to
     * the appropriate directory. The command's name is provided as an option
     * to the command.
     *
     * @return int Exit status code (0 for success, 1 for failure).
     * @throws DependencyException If there is an issue with dependencies.
     * @throws NotFoundException If a required resource is not found.
     */
    public function makeCommand(): int
    {
        info('Making command file...')->out(false);
        $name    = $this->option[0];
        $success = $this->makeTemplate($name, [
            'template_location' => __DIR__ . '/stubs/command',
            'save_location'     => commands_path(),
            'pattern'           => '__command__',
            'suf
            fix'            => 'Command.php',
        ]);

        if ($success) {
            $getContent = file_get_contents(config_path() . 'command.php');
            $getContent = str_replace(
                '// more command here',
                "// {$name} \n\t" . 'App\\Commands\\' . $name . 'Command::$' . "command\n\t// more command here",
                $getContent
            );

            file_put_contents(config_path() . 'command.php', $getContent);

            ok('Finish created command file')->out();

            return 0;
        }

        fail("\nFailed Create command file")->out();

        return 1;
    }

    /**
     * Creates a migration file.
     *
     * This method generates a migration file based on a template. The table name
     * is provided as an option, and the migration is created accordingly. If the
     * table name is not provided, it will prompt the user to enter it.
     *
     * @return int Exit status code (0 for success, 1 for failure).
     * @throws DependencyException If there is an issue with dependencies.
     * @throws NotFoundException If a required resource is not found.
     * @throws Exception If there is an error during the migration file creation.
     */
    public function makeMigration(): int
    {
        info('Making migration')->out(false);

        $name = $this->option[0] ?? false;
        if (false === $name) {
            warn('Table name cant be empty.')->out(false);
            do {
                $name = text('Fill the table name?', static fn ($text) => $text);
            } while ($name === '' || $name === false);
        }

        $name       = strtolower($name);
        $pathToFile = migration_path();
        $bath       = now()->format('Y_m_d_His');
        $fileName   = "{$pathToFile}{$bath}_{$name}.php";

        $use      = $this->update ? 'migration_update' : 'migration';
        $template = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . $use);
        $template = str_replace('__table__', $name, $template);

        if (false === file_exists($pathToFile) || false === file_put_contents($fileName, $template)) {
            fail('Can\'t create migration file.')->out();

            return 1;
        }
        ok('Success create migration file.')->out();

        return 0;
    }
}
