<?php

declare(strict_types=1);

namespace System\Console\Commands;

use System\Console\Command;
use System\Console\Style\Style;
use System\Console\Traits\PrintHelpTrait;
use System\Application\Application;
use System\Console\CommandMap;
use System\Console\Util;
use System\Text\Str;

use function array_merge;
use function class_exists;
use function explode;
use function implode;
use function in_array;
use function method_exists;

use function System\Console\info;
use function System\Console\style;
use function System\Console\warn;
use function ucfirst;

class HelpCommand extends Command
{
    use PrintHelpTrait;

    /**
     * @var string[]
     */
    protected array $classNamespace = [
        // register namespace commands
    ];

    /**
     * Register command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'pattern' => ['-h', '--help'],
            'fn'      => [HelpCommand::class, 'main'],
        ], [
            'pattern' => '--list',
            'fn'      => [HelpCommand::class, 'commandList'],
        ], [
            'pattern' => 'help',
            'fn'      => [HelpCommand::class, 'commandHelp'],
        ],
    ];

    /**
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp(): array
    {
        return [
            'commands'  => [
                'help' => 'Get help for available command',
            ],
            'options'   => [],
            'relation' => [
                'help' => ['[command_name]'],
            ],
        ];
    }

    protected string $banner ='
     _              _ _
 ___| |_ ___    ___| |_|
| . |   | . |  |  _| | |
|  _|_|_|  _|  |___|_|_|
|_|     |_|             ';

    /**
     * Use for print --help.
     */
    public function main(): int
    {
        $hasVisited      = [];
        $this->printHelp = [
            'margin-left'         => 8,
            'column-1-min-length' => 16,
        ];

        foreach ($this->commandMaps() as $command) {
            $class = $command->class();
            if (!in_array($class, $hasVisited)) {
                $hasVisited[] = $class;

                if (class_exists($class)) {
                    $class = new $class([], $command->defaultOption());

                    if (!method_exists($class, 'printHelp')) {
                        continue;
                    }

                    $help = app()->call([$class, 'printHelp']) ?? [];

                    if (isset($help['commands']) && $help['commands'] !== null) {
                        foreach ($help['commands'] as $command => $desc) {
                            $this->commandDescribes[$command] = $desc;
                        }
                    }

                    if (isset($help['options']) && $help['options'] !== null) {
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

    public function commandList(): int
    {
        style('List of all command registered:')->out();

        $maks1    = 0;
        $maks2    = 0;
        $commands = $this->commandMaps();
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

    public function commandHelp(): int
    {
        if (!isset($this->OPTION[0])) {
            style('')
                ->tap(info('To see help command, place provide command_name'))
                ->textYellow()
                ->push('php cli help <command_nama>')->textDim()
                ->newLines()
                ->push('              ^^^^^^^^^^^^')->textRed()
                ->out()
            ;

            return 1;
        }

        $className = $this->OPTION[0];
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
                'System\\Console\\',
            ]
        );

        foreach ($namespaces as $namespace) {
            $classCame = $namespace . $className;
            if (class_exists($classCame)) {
                $class = new $classCame([]);

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

        warn("Help for `{$this->OPTION[0]}` command not found")->out(false);

        return 1;
    }

    /**
     * Transform commands map array to CommandMap.
     *
     * @return CommandMap[]
     */
    private function commandMaps(): array
    {
        return Util::loadCommandFromConfig(Application::getInstance());
    }
}
