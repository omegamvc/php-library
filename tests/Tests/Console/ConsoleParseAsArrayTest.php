<?php

declare(strict_types=1);

namespace Tests\Console;

use PHPUnit\Framework\TestCase;
use Omega\Console\Command;
use Throwable;

class ConsoleParseAsArrayTest extends TestCase
{
    /**
     * Test it can parse normal command with space.
     *
     * @return void
     */
    public function testItCanParseNormalCommandWithSpace(): void
    {
        $command = 'php omega test --n john -tests -s --who-is children';
        $argv = explode(' ', $command);
        $cli = new Command($argv);

        $this->assertEquals(
            'test',
            $cli['name'],
            'valid parse name: test'
        );

        $this->assertEquals(
            'john',
            $cli['n'],
            'valid parse from short param with spare space: --n'
        );

        $this->assertTrue(
            isset($cli['who-is']),
            'valid parse from long param: --who-is'
        );
    }

    /**
     * Test it will throw exception when change command.
     *
     * @return void
     */
    public function testItWillTrowExceptionWhenChangeCommand(): void
    {
        $command = 'php omega test --n john -tests -s --who-is children';
        $argv = explode(' ', $command);
        $cli = new Command($argv);

        try {
            $cli['name'] = 'taylor';
        } catch (Throwable $th) {
            $this->assertEquals('Command cant be modify', $th->getMessage());
        }
    }

    /**
     * Test it will throw exception when unset command.
     *
     * @return void
     */
    public function testItWillThrowExceptionWhenUnsetCommand(): void
    {
        $command = 'php omega test --n john -tests -s --who-is children';
        $argv = explode(' ', $command);
        $cli = new Command($argv);

        try {
            unset($cli['name']);
        } catch (Throwable $th) {
            $this->assertEquals('Command cant be modify', $th->getMessage());
        }
    }

    /**
     * Test it can check option has exit or not.
     *
     * @return void
     */
    public function testItCanCheckOptionHasExitOrNot(): void
    {
        $command = 'php omega test --true="false"';
        $argv = explode(' ', $command);
        $cli = new Command($argv);

        $this->assertTrue((fn() => $this->{'hasOption'}('true'))->call($cli));
        $this->assertFalse((fn() => $this->{'hasOption'}('not-exist'))->call($cli));
    }
}
