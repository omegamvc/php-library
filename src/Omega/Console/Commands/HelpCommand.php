<?php

declare(strict_types=1);

namespace Omega\Console\Commands;

use Omega\Console\Command;
use Omega\Console\Style\Style;
use Omega\Console\Traits\PrintHelpTrait;
use Omega\Console\Util;
use Omega\Integrate\Application;
use Omega\Console\CommandMap;
use Omega\Text\Str;

use function Omega\Console\info;
use function Omega\Console\style;
use function Omega\Console\warn;

class HelpCommand extends Command
{
    use PrintHelpTrait;

    /**
     * @var string[]
     */
    protected array $class_namespace = [
        // register namesapce commands
    ];

    /**
     * Register command.
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
            'fn'      => [self::class, 'commandhelp'],
        ],
    ];

    /**
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp()
    {
        return [
            'commands'  => [
                'help' => 'Get help for avilable command',
            ],
            'options'   => [],
            'relation'  => [
                'help' => ['[command_name]'],
            ],
        ];
    }

    protected string $banner = '
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
        $has_visited      = [];
        $this->print_help = [
            'margin-left'         => 8,
            'column-1-min-length' => 16,
        ];

        foreach ($this->commandMaps() as $command) {
            $class = $command->class();
            if (!in_array($class, $has_visited)) {
                $has_visited[] = $class;

                if (class_exists($class)) {
                    $class = new $class([], $command->defaultOption());

                    if (!method_exists($class, 'printHelp')) {
                        continue;
                    }

                    $help = app()->call([$class, 'printHelp']) ?? [];

                    if (isset($help['commands']) && $help['commands'] !== null) {
                        foreach ($help['commands'] as $command => $desc) {
                            $this->command_describes[$command] = $desc;
                        }
                    }

                    if (isset($help['options']) && $help['options'] !== null) {
                        foreach ($help['options'] as $option => $desc) {
                            $this->option_describes[$option] = $desc;
                        }
                    }

                    if (isset($help['relation']) && $help['relation'] != null) {
                        foreach ($help['relation'] as $option => $desc) {
                            $this->command_relation[$option] = $desc;
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
            ->push(' oemga [flag]')
            ->newLines()->tabs()
            ->push('php')->textGreen()
            ->push(' omega [command] ')
            ->push('[option]')->textDim()
            ->newLines(2)

            ->push('Avilable flag:')
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

        $printer->push('Avilabe command:')->newLines(2);
        $printer = $this->printCommands($printer)->newLines();

        $printer->push('Avilabe options:')->newLines();
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
                ->push('php omega help <command_name>')->textDim()
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
            $this->class_namespace,
            [
                'App\\Commands\\',
                'Omega\\Console\\Commands\\',
            ]
        );

        foreach ($namespaces as $namespace) {
            $class_name = $namespace . $className;
            if (class_exists($class_name)) {
                $class = new $class_name([]);

                $help = app()->call([$class, 'printHelp']) ?? [];

                if (isset($help['commands']) && $help['commands'] != null) {
                    $this->command_describes = $help['commands'];
                }

                if (isset($help['options']) && $help['options'] != null) {
                    $this->option_describes = $help['options'];
                }

                if (isset($help['relation']) && $help['relation'] != null) {
                    $this->command_relation = $help['relation'];
                }

                style('Avilabe command:')->newLines()->out();
                $this->printCommands(new Style())->out();

                style('Avilable options:')->newLines()->out();
                $this->printOptions(new Style())->out();

                return 0;
            }
        }

        warn("Help for `{$this->OPTION[0]}` command not found")->out(false);

        return 1;
    }

    /**
     * Transform commandsmap array to CommandMap.
     *
     * @return CommandMap[]
     */
    private function commandMaps()
    {
        return Util::loadCommandFromConfig(Application::getIntance());
    }
}
