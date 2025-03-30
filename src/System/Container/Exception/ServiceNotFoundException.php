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
 * Exception thrown when a service cannot be found in the container.
 *
 * This exception is thrown when an attempt is made to retrieve a service
 * from the container, but the service is not registered or available
 * for resolution. This may occur if the service has not been properly
 * registered or has been removed from the container.
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
class ServiceNotFoundException extends Exception implements ContainerExceptionInterface
{
    /**
     * ServiceNotFoundException constructor.
     *
     * @param string $id The ID or name of the service that was not found.
     */
    public function __construct(string $id)
    {
        parent::__construct("Service " . $id . " not found in the container.");
    }
}
