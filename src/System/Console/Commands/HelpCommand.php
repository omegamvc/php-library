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
use System\Console\Traits\PrintHelpTrait;
use System\Application\Application;
use System\Console\Util;
use System\Console\CommandMap;
use System\Text\Str;

use function array_merge;
use function class_exists;
use function explode;
use function implode;
use function in_array;
use function method_exists;
use function System\Application\app;
use function System\Console\info;
use function System\Console\style;
use function System\Console\warn;
use function ucfirst;

/**
 * The HelpCommand class provides help documentation for the CLI commands.
 * It allows users to view general command usage, list all registered commands,
 * and get specific help for individual commands.
 *
 * This class also includes a banner display and dynamically retrieves help
 * information from registered command classes.
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
class HelpCommand extends Command
{
    use PrintHelpTrait;

    /** @var string[] Register the command namespace.*/
    protected array $classNamespace = [
        // Register namespace commands
    ];

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
            'pattern' => ['-h', '--help'],
            'fn'      => [self::class, 'main'],
        ], [
            'pattern' => '--list',
            'fn'      => [self::class, 'commandList'],
        ], [
            'pattern' => 'help',
            'fn'      => [self::class, 'commandHelp'],
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
                'help' => 'Get help for available command',
            ],
            'options'   => [],
            'relation'  => [
                'help' => ['[command_name]'],
            ],
        ];
    }

    /** @var string ASCII banner displayed in help output. */
    protected string $banner = '
     _              _ _
 ___| |_ ___    ___| |_|
| . |   | . |  |  _| | |
|  _|_|_|  _|  |___|_|_|
|_|     |_|             ';

    /**
     * Displays general help information.
     *
     * This method outputs usage instructions, available flags, commands,
     * and options. It dynamically gathers command descriptions from registered
     * command classes.
     *
     * @return int Exit status code (0 for success).
     */
    public function main(): int
    {
        $hasVisited      = [];
        $this->printHelp = [
            'margin-left'         => 8,
            'column-1-min-length' => 16,
        ];

        foreach ($this->getCommandMaps() as $command) {
            $class = $command->class();
            if (!in_array($class, $hasVisited)) {
                $hasVisited[] = $class;

                if (class_exists($class)) {
                    $class = new $class([], $command->defaultOption());

                    if (!method_exists($class, 'printHelp')) {
                        continue;
                    }

                    $help = app()->call([$class, 'printHelp']) ?? [];

                    //if (isset($help['commands']) && $help['commands'] !== null) {
                    if (isset($help['commands'])) {
                        foreach ($help['commands'] as $command => $desc) {
                            $this->commandDescribes[$command] = $desc;
                        }
                    }

                    //if (isset($help['options']) && $help['options'] !== null) {
                    if (isset($help['options'])) {
                        foreach ($help['options'] as $option => $desc) {
                            $this->optionDescribes[$option] = $desc;
                        }
                    }

                    if (isset($help['relation']) && $help['relation'] != null) {
                        foreach ($help['relation'] as $option => $desc) {
                            $this->commandRelation[$option] = $desc;
                        }
                    }
                }
            }
        }

        $printer = new Style();
        $printer->push($this->banner)->textGreen();
        $printer
            ->newLines(2)
            ->push('Usage:')
            ->newLines(2)->tabs()
            ->push('php')->textGreen()
            ->push(' cli [flag]')
            ->newLines()->tabs()
            ->push('php')->textGreen()
            ->push(' cli [command] ')
            ->push('[option]')->textDim()
            ->newLines(2)

            ->push('Available flag:')
            ->newLines(2)->tabs()
            ->push('--help')->textDim()
            ->tabs(3)
            ->push('Get all help commands')
            ->newLines()->tabs()
            ->push('--list')->textDim()
            ->tabs(3)
            ->push('Get list of commands registered (class & function)')
            ->newLines(2)
        ;

        $printer->push('Available command:')->newLines(2);
        $printer = $this->printCommands($printer)->newLines();

        $printer->push('Available options:')->newLines();
        $printer = $this->printOptions($printer);

        $printer->out();

        return 0;
    }

    /**
     * Displays a list of all registered commands.
     *
     * This method retrieves all command mappings and formats them in a
     * structured output, showing their associated class and function.
     *
     * @return int Exit status code (0 for success).
     */
    public function commandList(): int
    {
        style('List of all command registered:')->out();

        $maks1    = 0;
        $maks2    = 0;
        $commands = $this->getCommandMaps();
        foreach ($commands as $command) {
            $option = array_merge($command->cmd(), $command->patterns());
            $length = Str::length(implode(', ', $option));

            if ($length > $maks1) {
                $maks1 = $length;
            }

            $length = Str::length($command->class());
            if ($length > $maks2) {
                $maks2 = $length;
            }
        }

        foreach ($commands as $command) {
            $option = array_merge($command->cmd(), $command->patterns());
            style(implode(', ', $option))->textLightYellow()->out(false);

            $length1 = Str::length(implode(', ', $option));
            $length2 = Str::length($command->class());
            style('')
                ->repeat(' ', $maks1 - $length1 + 4)
                ->push($command->class())->textGreen()
                ->repeat('.', $maks2 - $length2 + 8)->textDim()
                ->push($command->method())
                ->out();
        }

        return 0;
    }

    /**
     * Displays help for a specific command.
     *
     * If no command name is provided, a usage hint is displayed. Otherwise,
     * the method searches for the command class, retrieves its help information,
     * and displays available commands and options.
     *
     * @return int Exit status code (0 for success, 1 if command not found).
     */
    public function commandHelp(): int
    {
        if (!isset($this->option[0])) {
            style('')
                ->tap(info('To see help command, place provide command_name'))
                ->textYellow()
                ->push('php cli help <command_name>')->textDim()
                ->newLines()
                ->push('              ^^^^^^^^^^^^')->textRed()
                ->out()
            ;

            return 1;
        }

        $className = $this->option[0];
        if (Str::contains(':', $className)) {
            $className = explode(':', $className);
            $className = $className[0];
        }

        $className .= 'Command';
        $className  = ucfirst($className);
        $namespaces = array_merge(
            $this->classNamespace,
            [
                'App\\Commands\\',
                'System\\Integrate\\Console\\',
            ]
        );

        foreach ($namespaces as $namespace) {
            $classNames = $namespace . $className;
            if (class_exists($classNames)) {
                $class = new $classNames([]);

                $help = app()->call([$class, 'printHelp']) ?? [];

                if (isset($help['commands']) && $help['commands'] != null) {
                    $this->commandDescribes = $help['commands'];
                }

                if (isset($help['options']) && $help['options'] != null) {
                    $this->optionDescribes = $help['options'];
                }

                if (isset($help['relation']) && $help['relation'] != null) {
                    $this->commandRelation = $help['relation'];
                }

                style('Available command:')->newLines()->out();
                $this->printCommands(new Style())->out();

                style('Available options:')->newLines()->out();
                $this->printOptions(new Style())->out();

                return 0;
            }
        }

        warn("Help for `{$this->option[0]}` command not found")->out(false);

        return 1;
    }

    /**
     * Retrieves the registered command mappings.
     *
     * @return CommandMap[]
     */
    private function getCommandMaps(): array
    {
        return Util::loadCommandFromConfig(Application::getInstance());
    }
}
