<?php

/**
 * Part of Omega - Tests\Console Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Console\Style;

use Omega\Console\Output\OutputStream;
use Omega\Console\Style\Colors;
use Omega\Console\Style\Style;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Throwable;

use function chr;
use function fclose;
use function fopen;
use function ob_get_clean;
use function ob_start;
use function Omega\Console\style;
use function rewind;
use function sprintf;
use function stream_get_contents;

/**
 * Class StyleTest
 *
 * This test suite verifies the behavior of the Style class used to render
 * ANSI escape sequences for terminal output. It includes tests for:
 * - Standard text and background color rendering
 * - RGB and HEX color parsing
 * - Raw ANSI code injection
 * - Magic method support for color variants (e.g., Tailwind-style names)
 * - Style composition and chaining (with push, repeat, tabs, new lines)
 * - Integration with the global `style()` helper function
 *
 * Each test ensures the final output string is correctly formatted with
 * ANSI codes, and maintains visual accuracy for terminal rendering.
 *
 * @category   Omega
 * @package    Tests
 * @subpackage Console\Style
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(Colors::class)]
#[CoversClass(Style::class)]
class StyleTest extends TestCase
{
    /**
     * Test it can render text color terminal code.
     *
     * @return void
     */
    public function testItCanRenderTextColorTerminalCode(): void
    {
        $cmd = new Style('text');
        $text = $cmd->textBlue();

        $this->assertEquals(sprintf('%s[34;49mtext%s[0m', chr(27), chr(27)), $text, 'text must return blue text terminal code');
    }

    /**
     * Test it can render bg color terminal code.
     *
     * @return void
     */
    public function testItCanRenderBgColorTerminalCode(): void
    {
        $cmd = new Style('text');
        $text = $cmd->bgBlue();

        $this->assertEquals(sprintf('%s[39;44mtext%s[0m', chr(27), chr(27)), $text, 'text must return blue background terminal code');
    }

    /**
     * Test it can render text and bg color terminal code.
     *
     * @return void
     */
    public function testItCanRenderTextAndBgColorTerminalCode(): void
    {
        $cmd = new Style('text');
        $text = $cmd->textRed()->bgBlue();

        $this->assertEquals(sprintf('%s[31;44mtext%s[0m', chr(27), chr(27)), $text, 'text must return red text and blue text terminal code');
    }

    /**
     * Test it can render color using raw rule interface.
     *
     * @return void
     */
    public function testItCanRenderColorUsingRawRuleInterface(): void
    {
        $cmd = new Style('text');
        $text = $cmd->raw(Colors::hexText('#ffd787'));

        $this->assertEquals(sprintf('%s[38;2;255;215;135;49mtext%s[0m', chr(27), chr(27)), $text, 'text must return raw color terminal code');

        $cmd = new Style('text');
        $text = $cmd->raw(Colors::rgbText(0, 0, 0));

        $this->assertEquals(sprintf('%s[38;2;0;0;0;49mtext%s[0m', chr(27), chr(27)), $text);
    }

    /**
     * Test it can render color raw.
     *
     * @return void
     */
    public function testItCanRenderColorRaw(): void
    {
        $cmd = new Style('text');
        $text = $cmd->raw('38;2;0;0;0');

        $this->assertEquals(sprintf('%s[39;49;38;2;0;0;0mtext%s[0m', chr(27), chr(27)), $text);
    }

    /**
     * Test it can render color multi raw.
     *
     * @return void
     */
    public function testItCanRenderColorMultiRaw(): void
    {
        $cmd = new Style('text');
        $text = $cmd
            ->raw('38;2;0;0;0')
            ->raw('48;2;255;255;255');

        $this->assertEquals(sprintf('%s[39;49;38;2;0;0;0;48;2;255;255;255mtext%s[0m', chr(27), chr(27)), $text);
    }

    /**
     * Test it can render chain code.
     *
     * @return void
     */
    public function testItCanRenderChainCode(): void
    {
        $cmd = new Style('text');

        ob_start();
        $cmd('i')
            ->textDim()
            ->push('love')
            ->textRed()
            ->push('php')
            ->textBlue()
            ->out(false);
        $text = ob_get_clean();

        $this->assertEquals(sprintf('%s[2;49mi%s[0m%s[31;49mlove%s[0m%s[34;49mphp%s[0m', chr(27), chr(27), chr(27), chr(27), chr(27), chr(27)), $text, 'text must return blue text terminal code');
    }

    /**
     * Test it can post render style.
     *
     * @return void
     */
    public function testItCanPostRenderStyle(): void
    {
        $printer = [
            style('i')->bgBlue(),
            style(' love ')->bgBlue(),
            style('php')->bgBlue(),
        ];

        ob_start();
        echo 'start ';
        foreach ($printer as $print) {
            echo $print;
        }
        echo ' end';
        $out = ob_get_clean();

        $this->assertEquals(
            sprintf('start %s[39;44mi%s[0m%s[39;44m love %s[0m%s[39;44mphp%s[0m end', chr(27), chr(27), chr(27), chr(27), chr(27), chr(27)),
            $out
        );
    }

    /**
     * Test it can render text color terminal code with push new line tabs spaces.
     *
     * @return void
     */
    public function testItCanRenderTextColorTerminalCodeWithPushNewLineTabsSpaces(): void
    {
        $cmd = new Style('text');
        $text = $cmd
            ->textBlue()
            ->tabs(2);
        $this->assertEquals(sprintf('%s[34;49mtext%s[0m%s[39;49m%s%s[0m', chr(27), chr(27), chr(27), "\t\t", chr(27)), $text);

        $cmd = new Style('text');
        $text = $cmd
            ->textBlue()
            ->newLines(2);
        $this->assertEquals(sprintf('%s[34;49mtext%s[0m%s[39;49m%s%s[0m', chr(27), chr(27), chr(27), "\n\n", chr(27)), $text);

        $cmd = new Style('text');
        $text = $cmd
            ->textBlue()
            ->repeat('.', 5);
        $this->assertEquals(sprintf('%s[34;49mtext%s[0m%s[39;49m%s%s[0m', chr(27), chr(27), chr(27), '.....', chr(27)), $text);
    }

    /**
     * Test it can render color using text color.
     *
     * @return void
     */
    public function testItCanRenderColorUsingTextColor(): void
    {
        $cmd = new Style('text');
        $text = $cmd->textColor(Colors::hexText('#ffd787'));

        $this->assertEquals(sprintf('%s[38;2;255;215;135;49mtext%s[0m', chr(27), chr(27)), $text, 'text must return raw color terminal code');
    }

    /**
     * Test it can render color using text color with hex string.
     *
     * @return void
     */
    public function testItCanRenderColorUsingTextColorWithHexString(): void
    {
        $cmd = new Style('text');
        $text = $cmd->textColor('#ffd787');

        $this->assertEquals(sprintf('%s[38;2;255;215;135;49mtext%s[0m', chr(27), chr(27)), $text, 'text must return raw color terminal code');
    }

    /**
     * Test it can render color using bg color.
     *
     * @return void
     */
    public function testItCanRenderColorUsingBgColor(): void
    {
        $cmd = new Style('text');
        $text = $cmd->bgColor(Colors::hexBg('#ffd787'));

        $this->assertEquals(sprintf('%s[39;48;2;255;215;135mtext%s[0m', chr(27), chr(27)), $text, 'text must return raw color terminal code');
    }

    /**
     * Test it can render color using bg color with hex string.
     *
     * @return void
     */
    public function testItCanRenderColorUsingBgColorWithHexString(): void
    {
        $cmd = new Style('text');
        $text = $cmd->bgColor('#ffd787');

        $this->assertEquals(sprintf('%s[39;48;2;255;215;135mtext%s[0m', chr(27), chr(27)), $text, 'text must return raw color terminal code');
    }

    /**
     * Test it can render color variant using magic call.
     *
     * @return void
     */
    public function testItCanRenderColorVariantUsingMagicCall(): void
    {
        $text = (new Style('text'))->text_red_500();

        $this->assertEquals(sprintf('%s[38;2;239;68;68;49mtext%s[0m', chr(27), chr(27)), $text, 'text must return raw color terminal code');

        $text = (new Style('text'))->bg_blue_500();

        $this->assertEquals(sprintf('%s[39;48;2;59;130;246mtext%s[0m', chr(27), chr(27)), $text, 'text must return raw color terminal code');
    }

    /**
     * Test it can throw exception when color variant not register.
     *
     * @return void
     */
    public function testItCanThrowExceptionWhenColorVariantNotRegister(): void
    {
        try {
            (new Style('text'))->text_red_10();
        } catch (Throwable $th) {
            $this->assertEquals('Undefined constant self::RED_10', $th->getMessage());
        }
    }

    /**
     * Test it can count text length without rule counted.
     *
     * @return void
     */
    public function testItCanCountTextLengthWithoutRuleCounted(): void
    {
        $text = new Style('12345');
        $text->bgBlue()->textWhite()->underline();

        $this->assertEquals(5, $text->length());
    }

    /**
     * Test it can count text number length without rule counted.
     *
     * @return void
     */
    public function testItCanCountTextNumberLengthWithoutRuleCounted(): void
    {
        $text = new Style('12345');
        $text->bgBlue()->textWhite()->underline();

        $this->assertEquals(5, $text->length());

        // add using invoke
        $text('123')->bgBlue()->textWhite()->underline();
        $this->assertEquals(3, $text->length());

        // add using push
        $text->push('123')->bgBlue()->textWhite()->underline();
        $this->assertEquals(6, $text->length());
    }

    /**
     * Test it can push using style.
     *
     * @return void
     */
    public function testItCanPushUsingStyle(): void
    {
        $cmd = new Style('text');
        $cmd->textBlue();

        $tap = new Style('text2');
        $tap->textRed();

        // push using tab
        $cmd->tap($tap);

        $text = $cmd->__toString();

        $this->assertEquals(
            sprintf('%s[34;49mtext%s[0m%s[31;49mtext2%s[0m', chr(27), chr(27), chr(27), chr(27)),
            $text
        );
    }

    /**
     * Test it can render and reset decorate.
     *
     * @return void
     */
    public function testItCanRenderAndResetDecorate(): void
    {
        $cmd = new Style('text');
        $text = $cmd->textBlue()->resetDecorate();

        $this->assertEquals(sprintf('%s[34;49mtext%s[0m', chr(27), chr(27)), $text, 'text must return blue text terminal code');
    }

    /**
     * Test it can render and reset decorate using raw reset.
     *
     * @return void
     */
    public function testItCanRenderAndResetDecorateUsingRawReset(): void
    {
        $cmd = new Style('text');
        $text = $cmd->textBlue()->rawReset([0, 22]);

        $this->assertEquals(sprintf('%s[34;49mtext%s[0;22m', chr(27), chr(27)), $text, 'text must return blue text terminal code');
    }

    /**
     * Test it can print using yield.
     *
     * @return void
     */
    public function testItCanPrintUsingYield(): void
    {
        $cmd = new Style('text');

        ob_start();
        $cmd('i')
            ->textDim()
            ->push('love')
            ->textRed()
            ->push('php')
            ->textBlue()
            ->yield();
        $text = ob_get_clean();

        $this->assertEquals(sprintf('%s[2;49mi%s[0m%s[31;49mlove%s[0m%s[34;49mphp%s[0m', chr(27), chr(27), chr(27), chr(27), chr(27), chr(27)), $text, 'text must return blue text terminal code');
    }

    /**
     * Test it can print using yield and continue.
     *
     * @return void
     */
    public function testItCanPrintUsingYieldAndContinue(): void
    {
        $cmd = new Style('text');

        ob_start();
        $cmd('i')
            ->textDim()
            ->push('love')
            ->textRed()
            ->yield()
            ->push('php')
            ->textBlue();
        $text = ob_get_clean();

        $this->assertEquals(sprintf('%s[2;49mi%s[0m%s[31;49mlove%s[0m', chr(27), chr(27), chr(27), chr(27)), $text, 'text must return blue text terminal code');
    }

    /**
     * Test it can print using yield continue and out.
     *
     * @return void
     */
    public function testItCanPrintUsingYieldContinueAndOut(): void
    {
        $cmd = new Style('text');

        ob_start();
        $cmd('i')
            ->textDim()
            ->push('love')
            ->textRed()
            ->yield()
            ->push('php')
            ->textBlue()
            ->out(false);
        $text = ob_get_clean();

        $this->assertEquals(sprintf('%s[2;49mi%s[0m%s[31;49mlove%s[0m%s[34;49mphp%s[0m', chr(27), chr(27), chr(27), chr(27), chr(27), chr(27)), $text, 'text must return blue text terminal code');
    }

    /**
     * Test it only print if condition true.
     *
     * @return void
     */
    public function testItOnlyPrintIfConditionTrue(): void
    {
        $cmd = new Style('text');
        ob_start();
        $cmd('i')
            ->textDim()
            ->push('love')
            ->textRed()
            ->push('php')
            ->textBlue()
            ->outIf(true, false);
        $text = ob_get_clean();

        $this->assertEquals(sprintf('%s[2;49mi%s[0m%s[31;49mlove%s[0m%s[34;49mphp%s[0m', chr(27), chr(27), chr(27), chr(27), chr(27), chr(27)), $text);

        // using callback
        ob_start();
        $cmd('i')
            ->textDim()
            ->push('love')
            ->textRed()
            ->push('php')
            ->textBlue()
            ->outIf(fn(): bool => true, false);
        $text = ob_get_clean();

        $this->assertEquals(sprintf('%s[2;49mi%s[0m%s[31;49mlove%s[0m%s[34;49mphp%s[0m', chr(27), chr(27), chr(27), chr(27), chr(27), chr(27)), $text);

        // if false
        ob_start();
        $cmd('i')
            ->textDim()
            ->push('love')
            ->textRed()
            ->push('php')
            ->textBlue()
            ->outIf(false, false);
        $text = ob_get_clean();

        $this->assertEquals('', $text);
    }

    /**
     * Test writing to a valid stream.
     *
     * @return void
     */
    public function testWriteToStream(): void
    {
        $stream = fopen('php://memory', 'w+');
        $outputStream = new OutputStream($stream);
        $style = new Style('');

        $style->setOutputStream($outputStream);
        $style->write(false);

        rewind($stream);
        $this->assertEquals('', stream_get_contents($stream));
        fclose($stream);
    }
}
