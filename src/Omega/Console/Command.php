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

namespace Omega\Console;

use ArrayAccess;
use Omega\Console\Traits\TerminalTrait;
use Omega\Text\Str;
use function array_key_exists;
use function array_merge;
use function array_shift;
use function explode;
use function preg_match;
use function preg_replace;
use function str_split;

/**
 * Add customize terminal style by adding traits:
 * - TraitCommand (optional).
 *
 * @category  Omega
 * @package   Console
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 *
 * @property bool|int|string|string[]|null $_
 * @property bool|int|string|string[]|null $name
 * @property bool|int|string|string[]|null $nick
 * @property bool|int|string|string[]|null $whois
 * @property bool|int|string|string[]|null $default
 * @property bool|int|string|string[]|null $t
 * @property bool|int|string|string[]|null $n
 * @property bool|int|string|string[]|null $s
 * @property bool|int|string|string[]|null $l
 * @property bool|int|string|string[]|null $cp
 * @property bool|int|string|string[]|null $io
 * @property bool|int|string|string[]|null $i
 * @property bool|int|string|string[]|null $o
 * @property bool|int|string|string[]|null $ab
 * @property bool|int|string|string[]|null $a
 * @property bool|int|string|string[]|null $b
 * @property bool|int|string|string[]|null $y
 * @property bool|int|string|string[]|null $d
 * @property bool|int|string|string[]|null $vvv
 * @property bool|int|string|string[]|null $v
 * @property bool|int|string|string[]|null $last
 *
 * @method echoTextRed()
 * @method echoTextYellow()
 * @method echoTextGreen()
 *
 * @implements ArrayAccess<string, string|bool|int|null>
 */
class Command implements ArrayAccess
{
    use TerminalTrait;

    /**
     * Commandline input.
     *
     * @var string|array<int, string>
     */
    protected string|array $cmd;

    /**
     * Commandline input.
     *
     * @var array<int, string>
     */
    protected array $option;

    /**
     * Base dir.
     *
     * @var string
     */
    protected string $baseDir;

    /**
     * Option object mapper.
     *
     * @var array<string, string|string[]|bool|int|null>
     */
    protected array $optionMapper;

    /**
     * Option describe for print.
     *
     * @var array<string, string>
     */
    protected array $commandDescribes = [];

    /**
     * Option describe for print.
     *
     * @var array<string, string>
     */
    protected array $optionDescribes = [];

    /**
     * Relation between Option and Argument.
     *
     * @var array<string, array<int, string>>
     */
    protected array $commandRelation = [];

    /**
     * Parse commandline.
     *
     * @param array<int, string>                  $argv
     * @param array<string, string|bool|int|null> $defaultOption
     * @return void
     */
    public function __construct(array $argv, array $defaultOption = [])
    {
        array_shift($argv);

        $this->cmd          = array_shift($argv) ?? '';
        $this->option       = $argv;
        $this->optionMapper = $defaultOption;

        foreach ($this->optionMapper($argv) as $key => $value) {
            $this->optionMapper[$key] = $value;
        }
    }

    /**
     * parse option to readable array option.
     *
     * @param array<int, string|bool|int|null> $argv Option to parse
     * @return array<string, string|bool|int|null>
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
                $name      = preg_replace('/^(-{1,2})/', '', $keyValue[0]);

                // alias check
                if (preg_match('/^-(?!-)([a-zA-Z]+)$/', $keyValue[0], $single_dash)) {
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
                    if (\is_int($options[$name])) {
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
     * Detect string is command or value.
     */
    private function isCommandParam(string $command): bool
    {
        return Str::startsWith($command, '-') || Str::startsWith($command, '--');
    }

    /**
     * Remove quote single or double.
     */
    private function removeQuote(string $value): string
    {
        return Str::match($value, '/(["\'])(.*?)\1/')[2] ?? $value;
    }

    /**
     * Get parse commandline parameters (name, value).
     *
     * @param string|string[]|bool|int|null $default Default if parameter not found
     *
     * @return string|string[]|bool|int|null
     */
    protected function option(string $name, $default = null)
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
     * Get exist option status.
     */
    protected function hasOption(string $name): bool
    {
        return array_key_exists($name, $this->optionMapper);
    }

    /**
     * Get all option array positional.
     *
     * @return string[]
     */
    protected function optionPosition()
    {
        return $this->optionMapper[''];
    }

    /**
     * Get parse commandline parameters (name, value).
     *
     * @param string $name
     *
     * @return string|bool|int|null
     */
    public function __get($name)
    {
        return $this->option($name);
    }

    /**
     * @param mixed $offset — Check parse commandline parameters
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->optionMapper);
    }

    /**
     * @param mixed $offset — Check parse commandline parameters
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->option($offset);
    }

    public function offsetSet($offset, $value): void
    {
        throw new \Exception('Command cant be modify');
    }

    public function offsetUnset($offset): void
    {
        throw new \Exception('Command cant be modify');
    }

    /**
     * Default class to run some code.
     *
     * @return void
     */
    public function main()
    {
        // print welcome screen or what ever you want
    }
}
