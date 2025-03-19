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

use System\Console\Style\Decorate;

use function chr;
use function implode;
use function str_repeat;

/**
 * The `PrinterTrait` provides a set of helper methods for printing styled and formatted text
 * to the command line, including methods for applying color codes, printing new lines, tabs,
 * clearing the current line or cursor position, and more.
 *
 * The color codes and formatting follow ANSI escape sequences for terminal styling.
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
trait PrinterTrait
{
    /**
     * Apply a set of color rules to a text string and return the formatted result.
     *
     * @param array<int, string|int> $rule The array of style rules (e.g., color codes).
     * @param string|int             $text The text to be styled.
     * @param bool                   $reset Whether to reset the color formatting after the text.
     * @param array<int, string|int> $resetRule The reset rule(s) for clearing applied styles.
     * @return string The formatted text with applied rules.
     */
    protected function rules(
        array $rule,
        string|int $text,
        bool $reset = true,
        array $resetRule = [Decorate::RESET]
    ): string {
        $stringRules      = implode(';', $rule);
        $stringResetRules = implode(';', $resetRule);

        return $this->rule($stringRules, $text, $reset, $stringResetRules);
    }

    /**
     * Apply a single color rule to a text string and return the formatted result.
     *
     * @param int|string $rule The style rule (e.g., color code).
     * @param string     $text The text to be styled.
     * @param bool       $reset Whether to reset the color formatting after the text.
     * @param int|string $resetRule The reset rule to clear applied styles.
     * @return string The formatted text with applied color rule.
     */
    protected function rule(
        int|string $rule,
        string $text,
        bool $reset = true,
        int|string $resetRule = Decorate::RESET
    ): string {
        $rule      = chr(27) . '[' . $rule . 'm' . $text;
        $resetRule = chr(27) . '[' . $resetRule . 'm';

        return $reset
            ? $rule . $resetRule
            : $rule;
    }

    /**
     * Print new lines multiple times.
     *
     * @param int $count The number of new lines to print.
     * @return void
     * @deprecated This method is deprecated and may be removed in future versions.
     */
    protected function print_n(int $count = 1): void
    {
        echo str_repeat("\n", $count);
    }

    /**
     * Print tabs multiple times.
     *
     * @param int $count The number of tabs to print.
     * @return void
     * @deprecated This method is deprecated and may be removed in future versions.
     */
    protected function print_t(int $count = 1): void
    {
        echo str_repeat("\t", $count);
    }

    /**
     * Return a string with new lines repeated a given number of times.
     *
     * @param int $count The number of new lines to repeat.
     * @return string A string containing the specified number of new lines.
     */
    protected function newLine(int $count = 1): string
    {
        return str_repeat("\n", $count);
    }

    /**
     * Return a string with tabs repeated a given number of times.
     *
     * @param int $count The number of tabs to repeat.
     * @return string A string containing the specified number of tabs.
     */
    protected function tabs(int $count = 1): string
    {
        return str_repeat("\t", $count);
    }

    /**
     * Clear from the cursor position to the beginning of the line.
     *
     * @deprecated This method is deprecated and may be removed in future versions.
     * @return void
     */
    protected function clearCursor(): void
    {
        echo chr(27) . '[1K';
    }

    /**
     * Clear the entire current line.
     *
     * @deprecated This method is deprecated and may be removed in future versions.
     * @return void
     */
    protected function clear_line(): void
    {
        echo chr(27) . '[2K';
    }

    /**
     * Replace the output on a single line with new text.
     *
     * @param string $replace The new text to print.
     * @param int    $line The line to replace. Defaults to -1 (current line).
     * @return void
     */
    protected function replaceLine(string $replace, int $line = -1): void
    {
        $this->moveLine($line);
        echo chr(27) . "[K\r" . $replace;
    }

    /**
     * Clear or reset the current line to an empty state.
     *
     * @param int $line The line to clear. Defaults to -1 (current line).
     * @return void
     */
    protected function clearLine(int $line = -1): void
    {
        $this->moveLine($line);
        $this->replaceLine('');
    }

    /**
     * Move the cursor to a specified line (relative to the bottom of the screen).
     *
     * @param int $line The line to move to.
     * @return void
     */
    protected function moveLine(int $line): void
    {
        echo chr(27) . "[{$line}A";
    }
}
