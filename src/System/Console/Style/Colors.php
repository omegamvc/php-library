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

use InvalidArgumentException;
use System\Console\Style\Color\BackgroundColor;
use System\Console\Style\Color\ForegroundColor;
use System\Text\Str;

use function max;
use function min;
use function sscanf;

/**
 * Handles color conversion for terminal output.
 *
 * The `Colors` class provides methods to convert hexadecimal and
 * RGB color values into terminal-compatible color codes for both
 * foreground (text) and background styling.
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
class Colors
{
    /**
     * Converts a hex color code to a terminal foreground color.
     *
     * @param string $hex_code A hex color code starting with `#` (e.g., `#RRGGBB`).
     * @return ForegroundColor The terminal-compatible foreground color.
     * @throws InvalidArgumentException If the provided hex code is invalid.
     */
    public static function hexText(string $hex_code): ForegroundColor
    {
        if (!Str::is($hex_code, '/^#[0-9a-fA-F]{6}$/i')) {
            throw new InvalidArgumentException(
                'Hex code not found.'
            );
        }

        [$r, $g, $b] = sscanf($hex_code, '#%02x%02x%02x');

        return self::rgbText($r, $g, $b);
    }

    /**
     * Converts a hex color code to a terminal background color.
     *
     * @param string $hex_code A hex color code starting with `#` (e.g., `#RRGGBB`).
     * @return BackgroundColor The terminal-compatible background color.
     * @throws InvalidArgumentException If the provided hex code is invalid.
     */
    public static function hexBg(string $hex_code): BackgroundColor
    {
        if (!Str::is($hex_code, '/^#[0-9a-fA-F]{6}$/i')) {
            throw new InvalidArgumentException(
                'Hex code not found.'
            );
        }

        [$r, $g, $b] = sscanf($hex_code, '#%02x%02x%02x');

        return self::rgbBg($r, $g, $b);
    }

    /**
     * Converts RGB values to a terminal foreground color.
     *
     * @param int $r Red component (0-255).
     * @param int $g Green component (0-255).
     * @param int $b Blue component (0-255).
     * @return ForegroundColor The terminal-compatible foreground color.
     */
    public static function rgbText(int $r, int $g, int $b): ForegroundColor
    {
        // normalize (value: 0-255) using min/max for more concise code
        $r = min(255, max(0, $r));
        $g = min(255, max(0, $g));
        $b = min(255, max(0, $b));

        return new ForegroundColor([38, 2, $r, $g, $b]);
    }

    /**
     * Converts RGB values to a terminal background color.
     *
     * @param int $r Red component (0-255).
     * @param int $g Green component (0-255).
     * @param int $b Blue component (0-255).
     * @return BackgroundColor The terminal-compatible background color.
     */
    public static function rgbBg(int $r, int $g, int $b): BackgroundColor
    {
        // normalize (value: 0-255) using min/max for more concise code
        $r = min(255, max(0, $r));
        $g = min(255, max(0, $g));
        $b = min(255, max(0, $b));

        return new BackgroundColor([48, 2, $r, $g, $b]);
    }
}
