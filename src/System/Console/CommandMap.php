<?php

/**
 * Part of Omega - Console Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace System\Console;

use ArrayAccess;
use Exception;
use InvalidArgumentException;
use System\Text\Str;

use function array_key_exists;
use function is_array;

/**
 * The `CommandMap` class is responsible for parsing and managing command-line input
 * for console applications. It provides structured access to command parameters, handles
 * alias mapping, and facilitates execution logic by resolving command, class, and method
 * mappings.
 *
 * This class implements `ArrayAccess`, allowing access to command properties via array
 * notation. It also supports pattern-based command matching, making it flexible for handling
 * various console command structures.
 *
 * @category  System
 * @package   Console
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 *
 * @property string $_ Get argument name
 *
 * @implements ArrayAccess<string, string|string[]|(array<string, string|bool|int|null>)|(callable(string): bool)>
 */
class CommandMap implements ArrayAccess
{
    /**
     * @var array<string, mixed> Stores command details such as cmd, mode, class, function, and default options.
     */
    private array $command;

    /**
     * Initializes the command map with a given set of command properties.
     *
     * @param array<string, mixed> $command An associative array containing command details.
     */
    public function __construct(array $command)
    {
        $this->command = $command;
    }

    /**
     * Retrieves the command rule as an array.
     *
     * @return string[] The command(s) associated with this map.
     */
    public function cmd(): array
    {
        if (false === array_key_exists('cmd', $this->command)) {
            return [];
        }
        $cmd = $this->command['cmd'];

        return is_array($cmd) ? $cmd : [$cmd];
    }

    /**
     * Retrieves the execution mode.
     *
     * @return string The execution mode (default: 'full').
     */
    public function mode(): string
    {
        return $this->command['mode'] ?? 'full';
    }

    /**
     * Retrieves command patterns as an array.
     *
     * @return string[] The pattern(s) associated with this command.
     */
    public function patterns(): array
    {
        if (false === array_key_exists('pattern', $this->command)) {
            return [];
        }
        $pattern = $this->command['pattern'];

        return is_array($pattern) ? $pattern : [$pattern];
    }

    /**
     * Retrieves the class associated with the command execution.
     *
     * @throws InvalidArgumentException If no class is defined.
     * @return string The class name.
     */
    public function class(): string
    {
        if (is_array($this->fn()) && array_key_exists(0, $this->fn())) {
            return $this->fn()[0];
        }

        if (array_key_exists('class', $this->command)) {
            return $this->command['class'];
        }

        throw new InvalidArgumentException('Command map require class in (class or fn).');
    }

    /**
     * Retrieves the function/method to be executed.
     *
     * @return string|string[] The function name or an array containing class and function.
     */
    public function fn(): array|string
    {
        return $this->command['fn'] ?? 'main';
    }

    /**
     * Retrieves the method associated with the command.
     *
     * @return string The method name to be executed.
     */
    public function method(): string
    {
        return is_array($this->fn()) ? $this->fn()[1] : $this->fn();
    }

    /**
     * Retrieves the default options associated with the command.
     *
     * @return array<string, mixed> Default command options.
     */
    public function defaultOption(): array
    {
        return $this->command['default'] ?? [];
    }

    /**
     * Returns a callable that checks whether a given command matches
     * the predefined patterns or commands.
     *
     * @return callable(string): bool A function that returns true if the
     *                                input matches the command pattern.
     */
    public function match(): callable
    {
        if (array_key_exists('pattern', $this->command)) {
            $pattern  = $this->command['pattern'];
            $patterns = is_array($pattern) ? $pattern : [$pattern];

            return fn($given): bool => in_array($given, $patterns, true);

            /**return function ($given) use ($patterns): bool {
                foreach ($patterns as $cmd) {
                    if ($given == $cmd) {
                        return true;
                    }
                }

                return false;
            };*/
        }

        if (array_key_exists('match', $this->command)) {
            return $this->command['match'];
        }

        if (array_key_exists('cmd', $this->command)) {
            return function ($given): bool {
                foreach ($this->cmd() as $cmd) {
                    if ('full' === $this->mode()) {
                        if ($given == $cmd) {
                            return true;
                        }
                    }

                    if (Str::startsWith($given, $cmd)) {
                        return true;
                    }
                }

                return false;
            };
        }

        return fn ($given) => false;
    }

    /**
     * Checks whether a given input matches the command pattern.
     *
     * @param string $given The command input to check.
     * @return bool True if the input matches, false otherwise.
     */
    public function isMatch(string $given): bool
    {
        return ($this->match())($given);
    }

    /**
     * Retrieves the class and method/function to call.
     *
     * @return string[] An array containing class and function/method name.
     */
    public function call(): array
    {
        return is_array($this->fn())
            ? $this->fn()
            : [$this->class(), $this->fn()];
    }

    /**
     * Checks whether a command key exists.
     *
     * @param mixed $offset The command key.
     * @return bool True if the key exists, false otherwise.
     */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->command);
    }

    /**
     * Retrieves the value of a command key.
     *
     * @param mixed $offset The command key.
     * @return mixed The value associated with the key.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->command[$offset];
    }

    /**
     * Prevents modifying the command properties.
     *
     * @param mixed $offset The command key.
     * @param mixed $value  The new value.
     * @return void
     * @throws Exception Always throws an exception as modification is not allowed.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new Exception('CommandMap cant be reassignment');
    }

    /**
     * Prevents unsetting a command property.
     *
     * @param mixed $offset The command key.
     * @return void
     * @throws Exception Always throws an exception as properties cannot be removed.
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new Exception('CommandMap cant be reassignment');
    }
}
