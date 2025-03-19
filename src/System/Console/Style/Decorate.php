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

namespace System\Console\Style;

/**
 * Class representing console text decoration styles.
 *
 * This class contains constants that define various text decorations and color codes,
 * including foreground and background colors, formatting (bold, underline, etc.), and reset codes.
 * The constants represent ANSI escape codes used for styling text in the console.
 *
 * These constants can be used to modify text appearance and provide a more visually
 * expressive output for console applications.
 *
 * @category   System
 * @package    Console
 * @subpackage Style
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
class Decorate
{
    // text
    public const int TEXT_DIM           = 2;
    public const int TEXT_RED           = 31;
    public const int TEXT_GREEN         = 32;
    public const int TEXT_YELLOW        = 33;
    public const int TEXT_BLUE          = 34;
    public const int TEXT_MAGENTA       = 35;
    public const int TEXT_CYAN          = 36;
    public const int TEXT_LIGHT_GRAY    = 37;
    public const int TEXT_DEFAULT       = 39;
    public const int TEXT_DARK_GRAY     = 90;
    public const int TEXT_LIGHT_RED     = 91;
    public const int TEXT_LIGHT_GREEN   = 92;
    public const int TEXT_LIGHT_YELLOW  = 93;
    public const int TEXT_LIGHT_BLUE    = 94;
    public const int TEXT_LIGHT_MAGENTA = 95;
    public const int TEXT_LIGHT_CYAN    = 96;
    public const int TEXT_WHITE         = 97;
    // background color
    public const int BG_RED           = 41;
    public const int BG_GREEN         = 42;
    public const int BG_YELLOW        = 43;
    public const int BG_BLUE          = 44;
    public const int BG_MAGENTA       = 45;
    public const int BG_CYAN          = 46;
    public const int BG_LIGHT_GRAY    = 47;
    public const int BG_DEFAULT       = 49;
    public const int BG_DARK_GRAY     = 100;
    public const int BG_LIGHT_RED     = 101;
    public const int BG_LIGHT_GREEN   = 102;
    public const int BG_LIGHT_YELLOW  = 103;
    public const int BG_LIGHT_BLUE    = 104;
    public const int BG_LIGHT_MAGENTA = 105;
    public const int BG_LIGHT_CYAN    = 106;
    public const int BG_WHITE         = 107;
    // other
    public const int BOLD            = 1;
    public const int UNDERLINE       = 4;
    public const int BLINK           = 5;
    public const int REVERSE         = 7;
    public const int HIDDEN          = 8;
    // reset
    public const int RESET           = 0;
    public const int RESET_BOLD      = 21;
    public const int RESET_BOLD_DIM  = 22;
    public const int RESET_UNDERLINE = 24;
    public const int RESET_BLINK     = 25;
    public const int RESET_REVERSE   = 27;
    public const int RESET_HIDDEN    = 28;
    // more code see https://misc.flogisoft.com/bash/tip_colors_and_formatting

    /**
     * Get constant value based on its string name.
     *
     * This method accepts a string representing the name of a constant and returns its
     * corresponding integer value, which is an ANSI code for console text styling.
     *
     * @param string $name The name of the constant.
     * @return int The integer value of the constant.
     */
    public static function getConst(string $name): int
    {
        return constant("self::{$name}");
    }
}
