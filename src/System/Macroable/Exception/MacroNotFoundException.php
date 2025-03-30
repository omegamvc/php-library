<?php

/**
 * Part of Omega - Macroable Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace System\Macroable\Exception;

use InvalidArgumentException;

use function sprintf;

/**
 * Thrown when attempting to call a macro that has not been registered in a macroable class.
 *
 * @category   Omega
 * @package    Macroable
 * @subpackage Exception
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class MacroNotFoundException extends InvalidArgumentException implements MacroableExceptionInterface
{
    /**
     * Creates a new exception instance when a macro is not found.
     *
     * @param string $methodName The name of the macro method that was not found.
     * @return void
     */
    public function __construct(string $methodName)
    {
        parent::__construct(
            sprintf(
                'Macro `%s` is not defined.', $methodName
            )
        );
    }
}
