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

namespace System\Macroable;

use System\Macroable\Exception\MacroNotFoundException;

/**
 * The `MacroableInterface` defines a contract for classes that support dynamic method
 * registration via macros. Implementing this interface allows a class to register, check,
 * and call macros at runtime, enabling greater flexibility and extensibility.
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
interface MacroableInterface
{
    /**
     * Registers a new macro.
     *
     * @param string   $macroName The name of the macro.
     * @param callable $callback  The callback function to be associated with the macro.
     * @return void
     */
    public static function macro(string $macroName, callable $callback): void;

    /**
     * Dynamically calls a registered static macro.
     *
     * @param string              $method     The macro name being called.
     * @param array<int, mixed>   $parameters The parameters to pass to the macro.
     * @return mixed The result of the macro execution.
     * @throws MacroNotFoundException If the macro is not registered.
     */
    public static function __callStatic(string $method, array $parameters);

    /**
     * Dynamically calls a registered macro on an instance.
     *
     * @param string              $method     The macro name being called.
     * @param array<int, mixed>   $parameters The parameters to pass to the macro.
     * @return mixed The result of the macro execution.
     * @throws MacroNotFoundException If the macro is not registered.
     */
    public function __call(string $method, array $parameters);

    /**
     * Checks if a macro is registered.
     *
     * @param string $macroName The macro name to check.
     * @return bool True if the macro is registered, otherwise false.
     */
    public static function hasMacro(string $macroName): bool;

    /**
     * Clears all registered macros.
     *
     * @return void
     */
    public static function resetMacro(): void;
}
