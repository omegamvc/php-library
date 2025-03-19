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
use System\Console\Traits\TerminalTrait;
use System\Text\Str;

use function array_key_exists;
use function array_merge;
use function array_shift;
use function count;
use function explode;
use function is_array;
use function is_int;
use function preg_match;
use function preg_replace;
use function str_split;

/**
 * Command class for parsing and managing command-line input for a console application.
 *
 * This class allows you to process command-line options, handle argument mapping,
 * and provide an easy-to-use interface for accessing command parameters.
 * It implements the ArrayAccess interface, allowing command options to be accessed
 * using array notation. The class also supports command aliases, option parsing,
 * and provides methods for retrieving values or checking the existence of options.
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
 * @implements ArrayAccess<string, string|bool|int|null>
 */
class Command implements ArrayAccess
{
    use TerminalTrait;

    /** @var string|array<int, string> The command-line input, which may be a single string or an array of strings. */
    protected string|array $cmd;

    /** @var array<int, string> Array of command options passed to the command. */
    protected array $option;

    /** @var string The base directory of the command. */
    protected string $baseDir;

    /** @var array<string, string|string[]|bool|int|null> A map of the command options and their associated values. */
    protected array $optionMapper;

    /** @var array<string, string> A description of the command options for printing in the console. */
    protected array $commandDescribes = [];

    /** @var array<string, string> A description of each option for printing in the console. */
    protected array $optionDescribes = [];

    /** @var array<string, array<int, string>> Mapping of option-to-argument relations. */
    protected array $commandRelation = [];

    /**
     * Command constructor that parses the command-line input and options.
     *
     * This method processes the input arguments (`argv`), removes the script name, and
     * populates the command and options accordingly. It also applies any default options
     * if provided and maps the options for easy access.
     *
     * @param array<int, string>                  $argv The command-line arguments passed to the application.
     * @param array<string, string|bool|int|null> $defaultOption Default options to apply.
     * @return void
     */
    public function __construct(array $argv, array $defaultOption = [])
    {
        array_shift($argv);

        $this->cmd           = array_shift($argv) ?? '';
        $this->option        = $argv;
        $this->optionMapper = $defaultOption;

        foreach ($this->optionMapper($argv) as $key => $value) {
            $this->optionMapper[$key] = $value;
        }
    }

    /**
     * Parse command-line options and convert them into a readable array format.
     *
     * This method parses each option and argument in the provided `argv` array, detecting
     * whether they are parameters with values or just options. It also handles aliases and
     * groups options into a structured array.
     *
     * @param array<int, string|bool|int|null> $argv The command-line options to parse.
     * @return array<string, string|bool|int|null> An associative array of parsed options.
     */
    private function optionMapper(array $argv): array
    {
        $options      = [];
        $options['_'] = $options['name'] = $argv[0] ?? '';
        $lastOption   = null;
        $alias        = [];

        foreach ($argv as $key => $option) {
            if ($this->isCommandParam($option)) {
                $keyValue = explode('=', $option);
                $name     = preg_replace('/^(-{1,2})/', '', $keyValue[0]);

                // alias check
                if (preg_match('/^-(?!-)([a-zA-Z]+)$/', $keyValue[0], $singleDash)) {
                    $alias[$name] = array_key_exists($name, $alias)
                        ? array_merge($alias[$name], str_split($name))
                        : str_split($name);
                }

                // param have value
                if (isset($keyValue[1])) {
                    $options[$name] = $this->removeQuote($keyValue[1]);
                    continue;
                }

                // check value in next param
                $nextKey = $key + 1;

                if (!isset($argv[$nextKey])) {
                    $options[$name] = true;
                    continue;
                }

                $next = $argv[$nextKey];
                if ($this->isCommandParam($next)) {
                    $options[$name] = true;
                }

                $lastOption = $name;
                continue;
            }

            $options[$lastOption][] = $this->removeQuote($option);
        }

        // re-group alias
        foreach ($alias as $key => $names) {
            foreach ($names as $name) {
                if (array_key_exists($name, $options)) {
                    if (is_int($options[$name])) {
                        $options[$name]++;
                    }
                    continue;
                }
                $options[$name] = $options[$key];
            }
        }

        return $options;
    }

    /**
     * Check whether the provided string is a command option or a regular value.
     *
     * This method checks if the string starts with a single or double dash (`-` or `--`),
     * indicating it's a command option rather than a value.
     *
     * @param string $command The string to check.
     * @return bool True if the string is a command option, false otherwise.
     */
    private function isCommandParam(string $command): bool
    {
        return Str::startsWith($command, '-') || Str::startsWith($command, '--');
    }

    /**
     * Remove surrounding quotes from a string (either single or double quotes).
     *
     * This method strips away any quotes around the given value (e.g., "value" or 'value').
     *
     * @param string $value The string to remove quotes from.
     * @return string The unquoted string.
     */
    private function removeQuote(string $value): string
    {
        return Str::match($value, '/(["\'])(.*?)\1/')[2] ?? $value;
    }

    /**
     * Retrieve the value of a specified command-line option.
     *
     * This method returns the value of a command-line option, or the default value
     * if the option is not set. If the option is an array with only one element,
     * it returns the single value.
     *
     * @param string                        $name The option name.
     * @param string|string[]|bool|int|null $default The default value if the option is not found.
     * @return string|string[]|bool|int|null The value of the option, or the default value.
     */
    protected function option(string $name, string|array|bool|int|null $default = null): string|array|bool|int|null
    {
        if (!array_key_exists($name, $this->optionMapper)) {
            return $default;
        }
        $option = $this->optionMapper[$name];
        if (is_array($option) && 1 === count($option)) {
            return $option[0];
        }

        return $option;
    }

    /**
     * Check if a command-line option exists.
     *
     * This method returns whether a specified option is present in the parsed command-line options.
     *
     * @param string $name The option name to check.
     * @return bool True if the option exists, false otherwise.
     */
    protected function hasOption(string $name): bool
    {
        return array_key_exists($name, $this->optionMapper);
    }

    /**
     * Retrieve all positional options.
     *
     * This method returns an array of all positional options that were passed in the command-line.
     *
     * @return string[] An array of positional options.
     */
    protected function optionPosition(): array
    {
        return $this->optionMapper[''];
    }

    /**
     * Magic method for retrieving command-line options.
     *
     * This method allows access to options using object property syntax.
     *
     * @param string $name The name of the option.
     * @return string|bool|int|null The value of the option.
     */
    public function __get(string $name): string|bool|int|null
    {
        return $this->option($name);
    }

    /**
     * Check if a command-line parameter exists using array syntax.
     *
     * @param mixed $offset The parameter to check.
     * @return bool True if the parameter exists, false otherwise.
     */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->optionMapper);
    }

    /**
     * Retrieve a command-line parameter using array syntax.
     *
     * @param mixed $offset The parameter to retrieve.
     * @return mixed The value of the parameter.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->option($offset);
    }

    /**
     * Throws an exception, as command parameters cannot be modified.
     *
     * @param mixed $offset The parameter to check.
     * @param mixed $value  The value to check.
     * @return void
     * @throws Exception Always throws an exception.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new Exception('Command cant be modify');
    }

    /**
     * Throws an exception, as command parameters cannot be removed.
     *
     * @param mixed $offset The parameter to check.
     * @return void
     * @throws Exception Always throws an exception.
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new Exception('Command cant be modify');
    }

    /**
     * Default method for running some code or printing a welcome screen.
     *
     * This method serves as the default point for executing code or displaying a message.
     * It can be customized for specific use cases.
     *
     * @return void
     */
    public function main()
    {
        // Use for printing welcome screen.
    }
}
