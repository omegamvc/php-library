<?php

declare(strict_types=1);

namespace System\Macroable;

use Closure;
use System\Macroable\Exceptions\MacroNotFoundException;

use function array_key_exists;

trait MacroableTrait
{
    /** @var string[] List of registered macro. */
    protected static array $macros = [];

    /**
     * Register string macro.
     *
     * @param string   $macroName Method name
     * @param callable $callBack  Method call able
     * @return void
     */
    public static function macro(string $macroName, callable $callBack): void
    {
        self::$macros[$macroName] = $callBack;
    }

    /**
     * Call macro.
     *
     * @param string             $method     Method name
     * @param array<int, string> $parameters Parameters
     * @return mixed
     * @throws MacroNotFoundException
     */
    public static function __callStatic(string $method, array $parameters)
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
     * Call macro.
     *
     * @param string             $method     Method name
     * @param array<int, string> $parameters Parameters
     * @return mixed
     * @throws MacroNotFoundException
     */
    public function __call(string $method, array $parameters)
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
     * Cek macro already register.
     *
     * @param string $macroName Macro name
     * @return bool True if macro has register
     */
    public static function hasMacro(string $macroName): bool
    {
        return array_key_exists($macroName, self::$macros);
    }

    /**
     * Reset registered macro.
     *
     * @return void
     */
    public static function resetMacro(): void
    {
        self::$macros = [];
    }
}
