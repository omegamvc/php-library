<?php

declare(strict_types=1);

namespace Tests\Console\Style;

use PHPUnit\Framework\TestCase;
use Omega\Console\Style\Colors;
use Throwable;

class ColorsTest extends TestCase
{
    /**
     * Test it can convert hex text to terminal color code.
     *
     * @return void
     */
    public function testItCanConvertHexTextToTerminalColorCode(): void
    {
        $this->assertEquals('38;2;255;255;255', Colors::hexText('#ffffff')->raw());
        $this->assertEquals('38;2;255;255;255', Colors::hexText('#FFFFFF')->raw());

        try {
            $this->assertEquals('38;5;231', Colors::hexText('ffffff'));
        } catch (Throwable $th) {
            $this->assertEquals('Hex code not found.', $th->getMessage());
        }

        try {
            $this->assertEquals('38;5;231', Colors::hexText('#badas'));
        } catch (Throwable $th) {
            $this->assertEquals('Hex code not found.', $th->getMessage());
        }
    }

    /**
     * Test it can convert hex bg to terminal color code.
     *
     * @return void
     */
    public function testItCanConvertHexBgToTerminalColorCode(): void
    {
        $this->assertEquals('48;2;255;255;255', Colors::hexBg('#ffffff')->raw());
        $this->assertEquals('48;2;255;255;255', Colors::hexBg('#FFFFFF')->raw());

        try {
            $this->assertEquals('48;5;231', Colors::hexBg('ffffff')->raw());
        } catch (Throwable $th) {
            $this->assertEquals('Hex code not found.', $th->getMessage());
        }

        try {
            $this->assertEquals('48;5;231', Colors::hexBg('#badas')->raw());
        } catch (Throwable $th) {
            $this->assertEquals('Hex code not found.', $th->getMessage());
        }
    }

    /**
     * Test it can convert rgb to terminal color code.
     *
     * @return void
     */
    public function testItCanConvertRGBToTerminalColorCode(): void
    {
        $this->assertEquals([38, 2, 0, 0, 0], Colors::rgbText(0, 0, 0)->get(), 'rgb text color white');
        $this->assertEquals([48, 2, 0, 0, 0], Colors::rgbBg(0, 0, 0)->get(), 'rgb bg color white');
    }
}
