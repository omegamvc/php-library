<?php

declare(strict_types=1);

namespace Tests\Console;

use PHPUnit\Framework\TestCase;
use Omega\Console\Command;

class CommandTest extends TestCase
{
    /**
     * Test it can get width.
     *
     * @return void
     */
    public function testItCanGetWidth(): void
    {
        $command = new class ([]) extends Command {
            public function width(int $min = 80, int $max = 160): int
            {
                return $this->getWidth($min, $max);
            }
        };

        $width = $command->width();
        $this->assertIsInt($width);
        $this->assertGreaterThan(79, $width);
        $this->assertLessThan(161, $width);
    }

    /**
     * test it can get width using column.
     *
     * @return void
     */
    public function testItCanGetWidthUsingColumn(): void
    {
        $_ENV['COLUMNS'] = '100';
        $command         = new class ([]) extends Command {
            public function width(int $min = 80, int $max = 160): int
            {
                return $this->getWidth($min, $max);
            }
        };

        $width = $command->width();
        $this->assertEquals(100, $width);
    }
}
