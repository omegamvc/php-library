<?php

/**
 * Part of Omega - Cron Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace System\Cron;

/**
 * Provides an interface for message interpolation.
 *
 * This interface defines a method for interpolating messages with contextual data.
 * Implementing classes should replace placeholders within the message string
 * using the provided context array.
 *
 * @category  System
 * @package   Cron
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
interface InterpolateInterface
{
    /**
     * Interpolates a message with the given context.
     *
     * This method replaces placeholders in the message string with corresponding
     * values from the context array. Placeholders are typically enclosed in braces `{}`.
     *
     * @param string               $message The message string containing placeholders.
     * @param array<string, mixed> $context An associative array of values to replace placeholders.
     * @return void
     */
    public function interpolate(string $message, array $context = []): void;
}
