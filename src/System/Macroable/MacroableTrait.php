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

use Closure;
use System\Macroable\Exception\MacroNotFoundException;

use function array_key_exists;

/**
 * The `MacroableTrait` allows a class to dynamically register and call macros.
 *
 * Macros are user-defined methods that can be added at runtime, making the class
 * extendable without modifying its original structure.
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
trait MacroableTrait
{
    /**
     * @var array<string, callable> Stores the list of registered macros.
     * The array keys represent macro names, and the values are their corresponding callbacks.
     */
    protected static array $macros = [];

    /**
     * Registers a new macro.
     *
     * @param string   $macroName The name of the macro.
     * @param callable $callback  The callback function to be associated with the macro.
     * @return void
     */
    public static function macro(string $macroName, callable $callback): void
    {
        self::$macros[$macroName] = $callback;
    }

    /**
     * Dynamically calls a registered static macro.
     *
     * @param string              $method     The macro name being called.
     * @param array<int, mixed>   $parameters The parameters to pass to the macro.
     * @return mixed The result of the macro execution.
     * @throws MacroNotFoundException If the macro is not registered.
     */
    public static function __callStatic(string $method, array $parameters): mixed
    {
        if (!array_key_exists($method, self::$macros)) {
            throw new MacroNotFoundException($method);
        }

        /** @var Closure $macro */
        $macro = static::$macros[$method];

        if ($macro instanceof Closure) {
            $macro = $macro->bindTo(null, static::class);
        }

        return $macro(...$parameters);
    }

    /**
     * Dynamically calls a registered macro on an instance.
     *
     * @param string              $method     The macro name being called.
     * @param array<int, mixed>   $parameters The parameters to pass to the macro.
     * @return mixed The result of the macro execution.
     * @throws MacroNotFoundException If the macro is not registered.
     */
    public function __call(string $method, array $parameters): mixed
    {
        if (!array_key_exists($method, self::$macros)) {
            throw new MacroNotFoundException($method);
        }

        /** @var Closure $macro */
        $macro = static::$macros[$method];

        if ($macro instanceof Closure) {
            $macro = $macro->bindTo($this, static::class);
        }

        return $macro(...$parameters);
    }

    /**
     * Checks if a macro is registered.
     *
     * @param string $macroName The macro name to check.
     * @return bool True if the macro is registered, otherwise false.
     */
    public static function hasMacro(string $macroName): bool
    {
        return array_key_exists($macroName, self::$macros);
    }

    /**
     * Clears all registered macros.
     *
     * @return void
     */
    public static function resetMacro(): void
    {
        self::$macros = [];
    }
}
