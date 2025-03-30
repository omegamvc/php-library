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
 * Exception thrown when an alias is registered to itself.
 *
 * This exception is thrown when an attempt is made to alias a service or abstract entry
 * to itself, which is not allowed as it creates an invalid alias relationship in the container.
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
class AliasConflictException extends RuntimeException implements ContainerExceptionInterface
{
    /**
     * AliasConflictException constructor.
     *
     * @param string $abstract The abstract name of the service being aliased.
     * @return void
     */
    public function __construct(string $abstract)
    {
        parent::__construct("Alias " . $abstract . " cannot be aliased to itself.");
    }
}
