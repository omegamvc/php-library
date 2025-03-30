<?php

/**
 * Part of Omega - Container Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace System\Container\Exception;

use RuntimeException;

/**
 * Exception thrown when a circular alias loop is detected.
 *
 * This exception is thrown when an alias loop is detected in the container,
 * where one alias refers to another in a circular fashion, preventing the
 * container from resolving the alias correctly.
 *
 * @category   Omega
 * @package    Container
 * @subpackage Exception
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class AliasLoopException extends RuntimeException implements ContainerExceptionInterface
{
    /**
     * AliasLoopException constructor.
     *
     * @param string $abstract The abstract name of the alias that caused the loop.
     */
    public function __construct(string $abstract)
    {
        parent::__construct("Alias loop detected for " . $abstract);
    }
}
