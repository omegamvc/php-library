<?php

declare(strict_types=1);

namespace Omega\Macroable;

use Omega\Macroable\Exceptions\MacroNotFoundException;

interface MacroableInterface
{
    /**
     * Register string macro.
     *
     * @param string   $macroName Method name
     * @param callable $callBack  Method call able
     * @return void
     */
    public static function macro(string $macroName, callable $callBack): void;

    /**
     * Call macro.
     *
     * @param string             $method     Method name
     * @param array<int, string> $parameters Parameters
     * @return mixed
     * @throws MacroNotFoundException
     */
    public static function __callStatic(string $method, array $parameters): mixed;

    /**
     * Call macro.
     *
     * @param string             $method     Method name
     * @param array<int, string> $parameters Parameters
     * @return mixed
     * @throws MacroNotFoundException
     */
    public function __call(string $method, array $parameters);

    /**
     * Cek macro already register.
     *
     * @param string $macroName Macro name
     * @return bool True if macro has register
     */
    public static function hasMacro(string $macroName): bool;

    /**
     * Reset registered macro.
     *
     * @return void
     */
    public static function resetMacro(): void;
}
