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

namespace System\Console\Output;

/**
 * OutputInterface class.
 *
 * Defines an interface for handling console output.
 *
 * This interface allows writing text to an output stream and checking whether
 * the stream is interactive (e.g., connected to a terminal).
 *
 * @category   System
 * @package    Console
 * @subpackage Output
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
interface OutputInterface
{
    /**
     * Writes the buffer to the stream.
     *
     * @param string $buffer The text to be written to the output stream.
     * @return void
     */
    public function write(string $buffer): void;

    /**
     * Checks whether the stream is interactive (connected to a terminal).
     *
     * @return bool Returns true if the stream is interactive, false otherwise.
     */
    public function isInteractive(): bool;
}
