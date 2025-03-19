<?php

/**
 * Part of Omega - Config Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace System\Config;

use ArrayAccess;

use function array_key_exists;

/**
 * ConfigRepository class.
 *
 * The `ConfigRepository` class serves as a centralized storage for application configuration
 * settings. It allows retrieving, modifying, and checking configuration values dynamically.
 *
 * This class implements ArrayAccess, making it possible to access configuration values using
 * array-like syntax. Additionally, it provides helper methods such as `has()`, `get()`, `set()`,
 * `push()`, and `toArray()` for flexible configuration management.
 *
 * It is particularly useful for managing settings in a structured way while maintaining ease
 * of use and direct accessibility.
 *
 * @category  System
 * @package   Config
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 *
 * @implements ArrayAccess<string, mixed>
 */
class ConfigRepository implements ArrayAccess
{
    /** @var array<string, mixed> Holds the configuration values. */
    protected array $config;

    /**
     * Create new config using array.
     *
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Determines whether a configuration key exists.
     *
     * @param string $key The key to check.
     * @return bool True if the key exists, false otherwise.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->config);
    }

    /**
     * Retrieves a configuration value.
     *
     * @param string $key     The configuration key.
     * @param mixed  $default Default value if the key is not found.
     * @return mixed The configuration value or default if not found.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Sets a configuration value.
     *
     * @param string $key   The configuration key.
     * @param mixed  $value The value to store.
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $this->config[$key] = $value;
    }

    /**
     * Appends a value to an existing configuration array.
     *
     * If the specified key does not exist, it initializes an empty array.
     *
     * @param string $key   The configuration key.
     * @param mixed  $value The value to append.
     * @return void
     */
    public function push(string $key, mixed $value): void
    {
        $array   = $this->get($key, []);
        $array[] = $value;

        $this->set($key, $array);
    }

    /**
     * Returns the configuration as an array.
     *
     * @return array<string, mixed> The stored configuration values.
     */
    public function toArray(): array
    {
        return $this->config;
    }

    /**
     * Checks whether an offset exists in the configuration.
     *
     * @param mixed $offset The offset key.
     * @return bool True if the offset exists, false otherwise.
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Retrieves a configuration value using array access.
     *
     * @param mixed $offset The offset key.
     * @return mixed The configuration value.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Sets a configuration value using array access.
     *
     * @param mixed $offset The offset key.
     * @param mixed $value  The value to set.
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Unsets a configuration value by setting it to null.
     *
     * @param mixed $offset The offset key.
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->set($offset, null);
    }
}
