<?php

declare(strict_types=1);

namespace Tests\Console;

use PHPUnit\Framework\TestCase;
use Omega\Console\Command;
use Omega\Console\Traits\CommandTrait;

class ConsoleParseTest extends TestCase
{
    /**
     * Test it can parse normal command.
     *
     * @return void
     */
    public function testItCanParseNormalCommand(): void
    {
        $command = 'php omega test --nick=john -tests';
        $argv = explode(' ', $command);
        $cli = new Command($argv, ['default' => true]);

        // parse name
        $this->assertEquals(
            'test',
            $cli->name,
            'valid parse name'
        );
        $this->assertEquals(
            'test',
            $cli->_,
            'valid parse name'
        );

        // parse long param
        $this->assertEquals(
            'john',
            $cli->nick,
            'valid parse from long param'
        );
        $this->assertNull($cli->whois, 'long param not valid');

        // parse null but have default
        $this->assertTrue($cli->default);

        // parse short param
        $this->assertTrue($cli->t, 'valid parse from short param');
        $this->assertNull($cli->n, 'short param not valid');
    }

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

        // parse name
        $this->assertEquals(
            'test',
            $cli->name,
            'valid parse name: test'
        );
        $this->assertEquals(
            'test',
            $cli->_,
            'valid parse name'
        );

        // parse short param
        $this->assertEquals(
            'john',
            $cli->n,
            'valid parse from short param with spare space: --n'
        );

        // parse short param
        $this->assertTrue($cli->t, 'valid parse from short param: -tests');
        $this->assertTrue($cli->s, 'valid parse from short param: -s');

        // parse long param
        $this->assertEquals(
            'children',
            $cli->__get('who-is'),
            'valid parse from long param: --who-is'
        );
    }

    // TODO: it_can_parse_normal_command_with_group_param

    /**
     * Test it can run main method.
     *
     * @return void
     */
    public function testItCanRunMainMethod(): void
    {
        $console = new class (['test', '--test', 'Oke']) extends Command {
            use CommandTrait;

            public function main(): void
            {
                echo $this->textGreen($this->name);
            }
        };

        ob_start();
        $console->main();
        $out = ob_get_clean();

        $this->assertEquals(sprintf('%s[32mOke%s[0m', chr(27), chr(27)), $out);
    }

    /**
     * test it can parse normal command with quote.
     *
     * @return void
     */
    public function testItCanParseNormalCommandWithQuote(): void
    {
        $command = 'php omega test --nick="john" -last=\'john\'';
        $argv = explode(' ', $command);
        $cli = new Command($argv, ['default' => true]);

        $this->assertEquals(
            'john',
            $cli->nick,
            'valid parse from long param with double quote'
        );

        $this->assertEquals(
            'john',
            $cli->last,
            'valid parse from long param with quote'
        );
    }

    /**
     * Test it can parse normal command with space and quote.
     *
     * @return void
     */
    public function testItCanParseNormalCommandWithSpaceAndQuote(): void
    {
        $command = 'php omega test --n "john" --l \'john\'';
        $argv = explode(' ', $command);
        $cli = new Command($argv);

        // parse short param
        $this->assertEquals(
            'john',
            $cli->n,
            'valid parse from short param with spare space: --n and single quote'
        );
        $this->assertEquals(
            'john',
            $cli->l,
            'valid parse from short param with spare space: --n and double quote'
        );
    }

    /**
     * test it can parse multi normal command.
     *
     * @return void
     */
    public function testItCanParseMultiNormalCommand(): void
    {
        $command = 'php app --cp /path/to/inputfile /path/to/ouputfile --dry-run';
        $argv = explode(' ', $command);
        $cli = new Command($argv);

        $this->assertEquals([
            '/path/to/inputfile',
            '/path/to/ouputfile',
        ], $cli->cp);
        $this->assertTrue($cli->__get('dry-run'));
    }

    /**
     * Test it can parse multi normal command without argument.
     *
     * @return void
     */
    public function testItCanParseMultiNormalCommandWithoutArgument(): void
    {
        $command = 'php cp /path/to/inputfile /path/to/ouputfile';
        $argv = explode(' ', $command);
        $cli = new class ($argv) extends Command {
            /**
             * @return string[]
             */
            public function getPosition(): array
            {
                return $this->optionPosition();
            }
        };

        $this->assertEquals([
            '/path/to/inputfile',
            '/path/to/ouputfile',
        ], $cli->__get(''));

        $this->assertEquals([
            '/path/to/inputfile',
            '/path/to/ouputfile',
        ], $cli->getPosition());
    }

    /**
     * Test it can parse alias.
     *
     * @return void
     */
    public function testItCanParseAlias(): void
    {
        $command = 'php app -io /path/to/inputfile /path/to/ouputfile';
        $argv = explode(' ', $command);
        $cli = new Command($argv);

        $this->assertEquals([
            '/path/to/inputfile',
            '/path/to/ouputfile',
        ], $cli->io);
        $this->assertEquals([
            '/path/to/inputfile',
            '/path/to/ouputfile',
        ], $cli->i);
        $this->assertEquals([
            '/path/to/inputfile',
            '/path/to/ouputfile',
        ], $cli->o);
    }

    /**
     * Test it can parse alias and count multi alias.
     *
     * @return void
     */
    public function testItCanParseAliasAndCountMultiAlias(): void
    {
        $command = 'php app -ab -y -tt -cd -d -vvv /path/to/inputfile /path/to/ouputfile';
        $argv = explode(' ', $command);
        $cli = new Command($argv);

        $this->assertTrue($cli->ab, 'group single dash');
        $this->assertTrue($cli->a, 'split group single dash');
        $this->assertTrue($cli->b, 'split group single dash');
        $this->assertTrue($cli->y);

        $this->assertEquals(2, $cli->t, 'count group');
        $this->assertEquals(2, $cli->d, 'count with different argument group');

        $this->assertEquals([
            '/path/to/inputfile',
            '/path/to/ouputfile',
        ], $cli->vvv);
        $this->assertEquals([
            '/path/to/inputfile',
            '/path/to/ouputfile',
        ], $cli->v);
    }
}
