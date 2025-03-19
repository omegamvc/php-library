<?php

/**
 * Part of Omega - Collection Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace System\Collection\Exceptions;

use InvalidArgumentException;

/**
 * NoModifyException.
 *
 * Exception thrown when an attempt is made to modify an immutable collection.
 *
 * @category   Omega
 * @package    Collection
 * @subpackage Exceptions
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class NoModifyException extends InvalidArgumentException
{
    /**
     * Creates a new Exception instance.
     *
     * Exception thrown when an attempt is made to modify an immutable collection.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct('Cannot modify an immutable collection.');
    }
}
