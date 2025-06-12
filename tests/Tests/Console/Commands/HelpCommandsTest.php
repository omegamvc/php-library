<?php

declare(strict_types=1);

namespace Tests\Console\Commands;

use Omega\Console\Command;
use Omega\Config\ConfigRepository;
use Omega\Console\Commands\HelpCommand;

class HelpCommandsTest extends CommandTestHelper
{
    private array $command = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->set('config', fn () => new ConfigRepository([
            'commands' => [$this->command],
        ]));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->command = [];
    }

    /**
     * Test it can call help command main.
     *
     * @return void
     */
    public function testItCanCallHelpCommandMain(): void
    {
        $this->command = [
            [
                'cmd'       => ['-h', '--help'],
                'mode'      => 'full',
                'class'     => HelpCommand::class,
                'fn'        => 'main',
            ],
        ];

        $helpCommand = new HelpCommand(['omega', '--help']);
        ob_start();
        $exit = $helpCommand->main();
        ob_get_clean();

        $this->assertSuccess($exit);
    }

    /**
     * Test it can call help command main with register another command.
     *
     * @return void
     */
    public function testItCanCallHelpCommandMainWithRegisterAnotherCommand(): void
    {
        $this->command = [
            [
                'pattern' => 'test',
                'fn'      => [RegisterHelpCommand::class, 'main'],
            ],
        ];

        $helpCommand = new HelpCommand(['omega', '--help']);

        ob_start();
        $exit = $helpCommand->main();
        $out  = ob_get_clean();

        $this->assertSuccess($exit);
        $this->assertContain('some test will appear in test', $out);
        $this->assertContain('this also will display in test', $out);
    }

    /**
     * Test it can call help command main with register another command using class.
     *
     * @return void
     */
    public function testItCanCallHelpCommandMainWithRegisterAnotherCommandUsingClass(): void
    {
        $this->command = [
            ['class' => RegisterHelpCommand::class],
        ];

        $helpCommand = new HelpCommand(['omega', '--help']);

        // use old style CommandMaps
        ob_start();
        $exit = $helpCommand->main();
        $out  = ob_get_clean();

        $this->assertSuccess($exit);
        $this->assertContain('some test will appear in test', $out);
        $this->assertContain('this also will display in test', $out);
    }

    /**
     * Test it can call help command command list.
     *
     * @return void
     */
    public function testItCanCallHelpCommandCommandList(): void
    {
        $helpCommand = new HelpCommand(['omega', '--list']);

        ob_start();
        $exit = $helpCommand->commandList();
        ob_get_clean();

        $this->assertSuccess($exit);
    }

    /**
     * Test it can call help command command list with register another command.
     *
     * @return void
     */
    public function testItCanCallHelpCommandCommandListWithRegisterAnotherCommand(): void
    {
        $this->command = [
            [
                'pattern' => 'unit:test',
                'fn'      => [RegisterHelpCommand::class, 'main'],
            ],
        ];

        $helpCommand = new HelpCommand(['omega', '--list']);

        ob_start();
        $exit = $helpCommand->commandList();
        $out  = ob_get_clean();

        $this->assertContain('unit:test', $out);
        $this->assertContain('Tests\Console\Commands\RegisterHelpCommand', $out);
        $this->assertSuccess($exit);
    }

    /**
     * Test it can call help command command help.
     *
     * @return void
     */
    public function testItCanCallHelpCommandCommandHelp(): void
    {
        $helpCommand = new HelpCommand(['omega', 'help', 'serve']);
        ob_start();
        $exit = $helpCommand->commandHelp();
        $out  = ob_get_clean();

        $this->assertSuccess($exit);
        $this->assertContain('Serve server with port number (default 8080)', $out);
    }

    /**
     * Test it can call help command command help but not found.
     *
     * @return void
     */
    public function testItCanCallHelpCommandCommandHelpButNoFound(): void
    {
        $helpCommand =  new HelpCommand(['omega', 'help', 'main']);
        ob_start();
        $exit = $helpCommand->commandHelp();
        $out  = ob_get_clean();

        $this->assertFails($exit);
        $this->assertContain('Help for `main` command not found', $out);
    }

    /**
     * Test it can call help command command help but not result.
     *
     * @return void
     */
    public function testItCanCallHelpCommandCommandHelpButNoResult(): void
    {
        $helpCommand =  new HelpCommand(['omega', 'help']);
        ob_start();
        $exit = $helpCommand->commandHelp();
        $out  = ob_get_clean();

        $this->assertFails($exit);
        $this->assertContain('php omega help <command_name>', $out);
    }
}

class RegisterHelpCommand extends Command
{
    /**
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp(): array
    {
        return [
            'commands'  => [
                'test' => 'some test will appear in test',
            ],
            'options'   => [
                '--test' => 'this also will display in test',
            ],
            'relation'  => [
                'test' => ['[unit]'],
            ],
        ];
    }
}
