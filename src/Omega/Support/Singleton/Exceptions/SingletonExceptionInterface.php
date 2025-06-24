<?php

/**
 * Part of Omega - Support Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Support\Singleton\Exceptions;

use Throwable;

/**
 * Contract for singleton-related exceptions in Omega.
 *
 * This interface marks exceptions that are thrown when singleton constraints
 * are violated within the Omega framework (e.g., attempting to instantiate or clone
 * a singleton in an invalid context).
 *
 * Implementing this interface allows unified handling of all singleton-specific
 * errors and maintains consistency across internal exception hierarchies.
 *
 * @category   Omega
 * @package    Support
 * @subpackage Singleton\Exception
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
interface SingletonExceptionInterface extends Throwable
{
}

