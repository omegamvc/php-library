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
 * RuleInterface class.
 *
 * Defines a contract for retrieving terminal formatting rules.
 *
 * Implementing classes should provide methods to retrieve the formatting
 * rules as an array of integers or as a raw string for direct use in ANSI sequences.
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
interface RuleInterface
{
    /**
     * Retrieves the terminal formatting rule as an array of integers.
     *
     * @return array<int, int> The array of ANSI formatting codes.
     */
    public function get(): array;

    /**
     * Retrieves the terminal formatting rule as a raw string.
     *
     * @return string The ANSI formatting rule as a semicolon-separated string.
     */
    public function raw(): string;
}
