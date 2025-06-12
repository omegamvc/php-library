<?php

declare(strict_types=1);

namespace Tests\Console;

use PHPUnit\Framework\TestCase;
use Omega\Console\Command;
use Omega\Console\Style\Color\ForegroundColor;
use Omega\Console\Traits\CommandTrait;

class TraitCommandTest extends TestCase
{
    private $command;

    protected function setUp(): void
    {
        $this->command = new class (['omega', '--test']) extends Command {
            use CommandTrait;

            public function __call($name, $arguments)
            {
                if ($name === 'echoTextRed') {
                    echo $this->textRed('Color');
                }
                if ($name === 'echoTextYellow') {
                    echo $this->textYellow('Color');
                }
                if ($name === 'echoTextGreen') {
                    echo $this->textGreen('Color');
                }
                if ($name === 'textColor') {
                    echo $this->textColor($arguments[0], 'Color');
                }
            }
        };
    }

    /**
     * Test it can make text red.
     *
     * @return void
     */
    public function testItCanMakeTextRed(): void
    {
        ob_start();
        $this->command->echoTextRed();
        $out = ob_get_clean();

        $this->assertEquals(sprintf('%s[31mColor%s[0m', chr(27), chr(27)), $out);
    }

    /**
     * Test it can make text yellow.
     *
     * @return void
     */
    public function testItCanMakeTextYellow(): void
    {
        ob_start();
        $this->command->echoTextYellow();
        $out = ob_get_clean();

        $this->assertEquals(sprintf('%s[33mColor%s[0m', chr(27), chr(27)), $out);
    }

    /**
     * Test it can make text green.
     *
     * @return void
     */
    public function testItCanMakeTextGreen(): void
    {
        ob_start();
        $this->command->echoTextGreen();
        $out = ob_get_clean();

        $this->assertEquals(sprintf('%s[32mColor%s[0m', chr(27), chr(27)), $out);
    }

    /**
     * Test it can make text color.
     *
     * @return void
     */
    public function testItCanMakeTextColor(): void
    {
        $color = new ForegroundColor([38, 2, 0, 0, 0]);
        ob_start();
        $this->command->textColor($color, 'Color');
        $out = ob_get_clean();

        $this->assertEquals(sprintf('%s[38;2;0;0;0mColor%s[0m', chr(27), chr(27)), $out);
    }
}
