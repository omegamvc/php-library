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

use function array_key_exists;
use function count;
use function explode;
use function function_exists;
use function min;
use function preg_match;
use function shell_exec;
use function trim;

use const PHP_OS_FAMILY;

/**
 * The `TerminalTrait` provides methods to determine the actual terminal dimensions,
 * ensuring proper width constraints for formatted output.
 *
 * It retrieves the terminal width based on system environment variables or
 * by executing shell commands. The width is then constrained within a defined range.
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
trait TerminalTrait
{
    /**
     * Get the terminal width within a defined range.
     *
     * This method attempts to retrieve the terminal width from system environment variables
     * or shell commands. If unavailable, it returns the default minimum width.
     *
     * @param int $min The minimum allowed width.
     * @param int $max The maximum allowed width.
     * @return int The calculated terminal width.
     */
    protected function getWidth(int $min = 80, int $max = 160): int
    {
        if (array_key_exists('COLUMNS', $_ENV)) {
            return $this->minMax((int) trim((string) $_ENV['COLUMNS']), $min, $max);
        }

        if (!function_exists('shell_exec')) {
            return $min;
        }

        if ('Windows' === PHP_OS_FAMILY) {
            $modeOutput = shell_exec('mode con');
            if (preg_match('/Columns:\s+(\d+)/', $modeOutput, $matches)) {
                return $this->minMax((int) $matches[1], $min, $max);
            }

            return $min;
        }

        $sttyOutput = shell_exec('stty size 2>&1');
        if ($sttyOutput) {
            $dimensions = explode(' ', trim($sttyOutput));
            if (2 === count($dimensions)) {
                return $this->minMax((int) $dimensions[1], $min, $max);
            }
        }

        return $min;
    }

    /**
     * Constrain a value within a given minimum and maximum range.
     *
     * This helper function ensures that a given value does not exceed the specified
     * minimum or maximum limits.
     *
     * @param int $value The value to be constrained.
     * @param int $min The minimum allowed value.
     * @param int $max The maximum allowed value.
     * @return int The constrained value.
     */
    private function minMax(int $value, int $min, int $max): int
    {
        return $value < $min ? $min : min($value, $max);
    }
}
