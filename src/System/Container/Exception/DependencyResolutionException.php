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

use Exception;

/**
 * Exception thrown when there is an error resolving a dependency.
 *
 * This exception is thrown when the container encounters an issue while
 * trying to resolve a dependency for a service. This could be caused by
 * missing or conflicting dependencies, or issues in the service's
 * construction process.
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
class DependencyResolutionException extends Exception implements ContainerExceptionInterface
{
    /**
     * DependencyResolutionException constructor.
     *
     * @param string $message The error message describing the dependency resolution issue.
     */
    public function __construct(string $message)
    {
        parent::__construct("Error resolving dependency: " . $message);
    }
}
