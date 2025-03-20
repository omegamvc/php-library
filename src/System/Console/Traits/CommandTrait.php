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

namespace System\Console\Traits;

use System\Console\Style\Color\BackgroundColor;
use System\Console\Style\Color\ForegroundColor;
use System\Console\Style\Decorate;

/**
 * CommandTrait class.
 *
 * The CommandTrait provides methods for applying various text and background color styles
 * to text output in the terminal. It includes predefined methods for common colors and effects,
 * as well as "Just In Time" color customization for text and background.
 *
 * The color codes used are standard ANSI escape codes for terminal styling.
 *
 * @category   System
 * @package    Console
 * @subpackage Traits
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
trait CommandTrait
{
    use PrinterTrait;

    /**
     * @param string $text The text to be styled.
     * @return string The styled text with red color. (bash code: 31)
     */
    protected function textRed(string $text): string
    {
        return $this->rule(Decorate::TEXT_RED, $text);
    }

    /**
     * @param string $text The text to be styled.
     * @return string The styled text with yellow color. (bash code: 33)
     */
    protected function textYellow(string $text): string
    {
        return $this->rule(Decorate::TEXT_YELLOW, $text);
    }

    /**
     * @param string $text The text to be styled.
     * @return string The styled text with green color. (bash code: 32)
     */
    protected function textGreen(string $text): string
    {
        return $this->rule(Decorate::TEXT_GREEN, $text);
    }

    /**
     * @param string $text The text to be styled.
     * @return string The styled text with blue color. (bash code: 34)
     */
    protected function textBlue(string $text): string
    {
        return $this->rule(Decorate::TEXT_BLUE, $text);
    }

    /**
     * @param string $text The text to be styled.
     * @return string The styled text with dim color. (bash code: 2)
     */
    protected function textDim(string $text): string
    {
        return $this->rule(Decorate::TEXT_DIM, $text);
    }

    /**
     * @param string $text The text to be styled.
     * @return string The styled text with magenta color. (bash code: 35)
     */
    protected function textMagenta(string $text): string
    {
        return $this->rule(Decorate::TEXT_MAGENTA, $text);
    }

    /**
     * @param string $text The text to be styled.
     * @return string The styled text with cyan color. (bash code: 36)
     */
    protected function textCyan(string $text): string
    {
        return $this->rule(Decorate::TEXT_CYAN, $text);
    }

    /**
     * @param string $text The text to be styled.
     * @return string The styled text with light gray color. (bash code: 37)
     */
    protected function textLightGray(string $text): string
    {
        return $this->rule(Decorate::TEXT_LIGHT_GRAY, $text);
    }

    /**
     * @param string $text The text to be styled.
     * @return string The styled text with dark gray color. (bash code: 90)
     */
    protected function textDarkGray(string $text): string
    {
        return $this->rule(Decorate::TEXT_DARK_GRAY, $text);
    }

    /**
     * @param string $text The text to be styled.
     * @return string The styled text with light red color. (bash code: 91)
     */
    protected function textLightRed(string $text): string
    {
        return $this->rule(Decorate::TEXT_LIGHT_RED, $text);
    }

    /**
     * @param string $text The text to be styled.
     * @return string The styled text with light green color. (bash code: 92)
     */
    protected function textLightGreen(string $text): string
    {
        return $this->rule(Decorate::TEXT_LIGHT_GREEN, $text);
    }

    /**
     * @param string $text The text to be styled.
     * @return string The styled text with light yellow color. (bash code: 93)
     */
    protected function textLightYellow(string $text): string
    {
        return $this->rule(Decorate::TEXT_LIGHT_YELLOW, $text);
    }

    /**
     * @param string $text The text to be styled.
     * @return string The styled text with light blue color. (bash code: 94)
     */
    protected function textLightBlue(string $text): string
    {
        return $this->rule(Decorate::TEXT_LIGHT_BLUE, $text);
    }

    /**
     * @param string $text The text to be styled.
     * @return string The styled text with light magenta color. (bash code: 95)
     */
    protected function textLightMagenta(string $text): string
    {
        return $this->rule(Decorate::TEXT_LIGHT_MAGENTA, $text);
    }

    /**
     * @param string $text The text to be styled.
     * @return string The styled text with light cyan color. (bash code: 96)
     */
    protected function textLightCyan(string $text): string
    {
        return $this->rule(Decorate::TEXT_LIGHT_CYAN, $text);
    }

    /**
     * @param string $text The text to be styled.
     * @return string The styled text with white color. (bash code: 97)
     */
    protected function textWhite(string $text): string
    {
        return $this->rule(Decorate::TEXT_WHITE, $text);
    }

    /**
     * @param string $text The text to be styled.
     * @return string The styled text with red background color. (bash code: 41)
     */
    protected function bgRed(string $text): string
    {
        return $this->rule(Decorate::BG_RED, $text);
    }

    /**
     * @param string $text The text to be styled.
     * @return string The styled text with yellow background color. (bash code: 43)
     */
    protected function bgYellow(string $text): string
    {
        return $this->rule(Decorate::BG_YELLOW, $text);
    }

    /**
     * @param string $text The text to be styled.
     * @return string The styled text with green background color. (bash code: 42)
     */
    protected function bgGreen(string $text): string
    {
        return $this->rule(Decorate::BG_GREEN, $text);
    }

    /**
     * @param string $text The text to be styled.
     * @return string The styled text with blue background color. (bash code: 44)
     */
    protected function bgBlue(string $text): string
    {
        return $this->rule(Decorate::BG_BLUE, $text);
    }

    /**
     * @param string $text The text to be styled.
     * @return string The styled text with magenta background color. (bash code: 45)
     */
    protected function bgMagenta(string $text): string
    {
        return $this->rule(Decorate::BG_MAGENTA, $text);
    }

    /**
     * @param string $text The text to be styled.
     * @return string The styled text with cyan background color. (bash code: 46)
     */
    protected function bgCyan(string $text): string
    {
        return $this->rule(Decorate::BG_CYAN, $text);
    }

    /**
     * @param string $text The text to be styled.
     * @return string The styled text with light gray background color. (bash code: 47)
     */
    protected function bgLightGray(string $text): string
    {
        return $this->rule(Decorate::BG_LIGHT_GRAY, $text);
    }

    /**
     * @param string $text The text to be styled.
     * @return string The styled text with dark gray background color. (bash code: 100)
     */
    protected function bgDarkGray(string $text): string
    {
        return $this->rule(Decorate::BG_DARK_GRAY, $text);
    }

    /**
     * @param string $text The text to be styled.
     * @return string The styled text with light red background color. (bash code: 101)
     */
    protected function bgLightRed(string $text): string
    {
        return $this->rule(Decorate::BG_LIGHT_RED, $text);
    }

    /**
     * @param string $text The text to be styled.
     * @return string The styled text with light green background color. (bash code: 102)
     */
    protected function bgLightGreen(string $text): string
    {
        return $this->rule(Decorate::BG_LIGHT_GREEN, $text);
    }

    /**
     * @param string $text The text to be styled.
     * @return string The styled text with light yellow background color. (bash code: 103)
     */
    protected function bgLightYellow(string $text): string
    {
        return $this->rule(Decorate::BG_LIGHT_YELLOW, $text);
    }

    /**
     * @param string $text The text to be styled.
     * @return string The styled text with light blue background color. (bash code: 104)
     */
    protected function bgLightBlue(string $text): string
    {
        return $this->rule(Decorate::BG_LIGHT_BLUE, $text);
    }

    /**
     * @param string $text The text to be styled.
     * @return string The styled text with light magenta background color. (bash code: 105)
     */
    protected function bgLightMagenta(string $text): string
    {
        return $this->rule(Decorate::BG_LIGHT_MAGENTA, $text);
    }

    /**
     * @param string $text The text to be styled.
     * @return string The styled text with light cyan background color. (bash code: 106)
     */
    protected function bgLightCyan(string $text): string
    {
        return $this->rule(Decorate::BG_LIGHT_CYAN, $text);
    }

    /**
     * @param string $text The text to be styled.
     * @return string The styled text with white background color. (bash code: 107)
     */
    protected function bgWhite(string $text): string
    {
        return $this->rule(Decorate::BG_WHITE, $text);
    }

    /**
     * Just-in-time text color customization.
     *
     * @param ForegroundColor $color The color code (0-256).
     * @param string $text The text to be styled.
     * @return string The styled text with the specified color.
     */
    protected function textColor(ForegroundColor $color, string $text): string
    {
        return $this->rules($color->get(), $text);
    }

    /**
     * Just-in-time background color customization.
     *
     * @param BackgroundColor $color The color code (0-256).
     * @param string $text The text to be styled.
     * @return string The styled text with the specified background color.
     */
    protected function bgColor(BackgroundColor $color, string $text): string
    {
        return $this->rules($color->get(), $text);
    }

    /**
     * Apply bold text style.
     *
     * @param string $text The text to be styled.
     * @return string The styled bold text.
     */
    protected function textBold(string $text): string
    {
        return $this->rule(Decorate::BOLD, $text, true, Decorate::RESET_BOLD);
    }

    /**
     * Apply underline text style.
     *
     * @param string $text The text to be styled.
     * @return string The styled underlined text.
     */
    protected function textUnderline(string $text): string
    {
        return $this->rule(Decorate::UNDERLINE, $text, true, Decorate::RESET_UNDERLINE);
    }

    /**
     * Apply blinking text style.
     *
     * @param string $text The text to be styled.
     * @return string The styled blinking text.
     */
    protected function textBlink(string $text): string
    {
        return $this->rule(Decorate::BLINK, $text, true, Decorate::RESET_BLINK);
    }

    /**
     * Apply reverse text style (swap foreground and background).
     *
     * @param string $text The text to be styled.
     * @return string The styled reversed text.
     */
    protected function textReverse(string $text): string
    {
        return $this->rule(Decorate::REVERSE, $text, true, Decorate::RESET_REVERSE);
    }

    /**
     * Apply hidden text style.
     *
     * @param string $text The text to be styled.
     * @return string The styled hidden text.
     */
    protected function textHidden(string $text): string
    {
        return $this->rule(Decorate::HIDDEN, $text, true, Decorate::RESET_HIDDEN);
    }
}
