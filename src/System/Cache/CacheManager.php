<?php

/**
 * Part of Omega - Cache Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace System\Cache;

use Closure;
use DateInterval;
use Exception;
use System\Cache\Storage\ArrayStorage;

use function is_callable;

/**
 * CacheManager class.
 *
 * The `CacheManager` class is a flexible cache manager that handles different
 * caching drivers. It implements the CacheInterface and provides a centralized
 * mechanism for interacting with multiple cache drivers. The class allows setting
 * a default driver and supports the addition of custom cache drivers. It resolves
 * the appropriate cache driver based on the specified driver name and delegates
 * cache operations to the corresponding driver.
 *
 * @category  System
 * @package   Cache
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class CacheManager implements CacheInterface
{
    /**
     * @var array<string, CacheInterface|Closure(): CacheInterface>
     * Holds an array of cache drivers. Each entry can either be a cache driver instance or a closure that returns one.
     */
    private array $driver = [];

    /** @var CacheInterface Holds an instance of the default cache driver. */
    private CacheInterface $defaultDriver;

    /**
     * CacheManager constructor.
     * Initializes the CacheManager with a default cache driver.
     *
     * @return void
     */
    public function __construct()
    {
        $this->setDefaultDriver(new ArrayStorage());
    }

    /**
     * Sets the default cache driver to be used when no specific driver is provided.
     *
     * @param CacheInterface $driver The cache driver to set as default.
     * @return self
     */
    public function setDefaultDriver(CacheInterface $driver): self
    {
        $this->defaultDriver = $driver;

        return $this;
    }

    /**
     * Adds a custom cache driver to the CacheManager.
     *
     * @param string $driverName The name of the driver.
     * @param CacheInterface|Closure(): CacheInterface $driver The cache driver or a closure that returns a cache driver.
     * @return self
     */
    public function setDriver(string $driverName, $driver): self
    {
        $this->driver[$driverName] = $driver;

        return $this;
    }

    /**
     * Resolves and returns the appropriate cache driver for the given driver name.
     * If the driver is callable, it will be executed to return the driver instance.
     *
     * @param string $driverName The name of the driver to resolve.
     * @return CacheInterface The resolved cache driver instance.
     * @throws Exception If the driver cannot be resolved or is invalid.
     */
    private function resolve(string $driverName): CacheInterface
    {
        $driver = $this->driver[$driverName];

        if (is_callable($driver)) {
            $driver = $driver();
        }

        if (null === $driver) {
            throw new Exception("Can use driver {$driverName}.");
        }

        return $this->driver[$driverName] = $driver;
    }

    /**
     * Returns the cache driver for the given driver name, or the default driver if no name is specified.
     *
     * @param string|null $driverName The name of the driver (optional).
     * @return CacheInterface The cache driver instance.
     * @throws Exception If the driver cannot be found or resolved.
     */
    public function driver(?string $driverName = null): CacheInterface
    {
        if (isset($this->driver[$driverName])) {
            return $this->resolve($driverName);
        }

        return $this->defaultDriver;
    }

    /**
     * Magic method to delegate cache operations to the appropriate cache driver.
     * This allows the CacheManager to pass method calls to the active cache driver.
     *
     * @param string $method The method to call on the cache driver.
     * @param mixed[] $parameters The parameters to pass to the method.
     * @return mixed The result of the method call on the cache driver.
     * @throws Exception If the driver cannot be resolved.
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->driver()->{$method}(...$parameters);
    }

    /**
     * {@inheritdoc}
     * @throws Exception If the driver cannot be found or resolved.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->driver()->get($key, $default);
    }

    /**
     * {@inheritdoc}
     * @throws Exception If the driver cannot be found or resolved.
     */
    public function set(string $key, mixed $value, int|DateInterval|null $ttl = null): bool
    {
        return $this->driver()->set($key, $value, $ttl);
    }

    /**
     * {@inheritdoc}
     * @throws Exception If the driver cannot be found or resolved.
     */
    public function delete(string $key): bool
    {
        return $this->driver()->delete($key);
    }

    /**
     * {@inheritdoc}
     * @throws Exception If the driver cannot be found or resolved.
     */
    public function clear(): bool
    {
        return $this->driver()->clear();
    }

    /**
     * {@inheritdoc}
     * @throws Exception If the driver cannot be found or resolved.
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        return $this->driver()->getMultiple($keys, $default);
    }

    /**
     * {@inheritdoc}
     * @throws Exception If the driver cannot be found or resolved.
     */
    public function setMultiple(iterable $values, int|DateInterval|null $ttl = null): bool
    {
        return $this->driver()->setMultiple($values, $ttl);
    }

    /**
     * {@inheritdoc}
     * @throws Exception If the driver cannot be found or resolved.
     */
    public function deleteMultiple(iterable $keys): bool
    {
        return $this->driver()->deleteMultiple($keys);
    }

    /**
     * {@inheritdoc}
     * @throws Exception If the driver cannot be found or resolved.
     */
    public function has(string $key): bool
    {
        return $this->driver()->has($key);
    }

    /**
     * {@inheritdoc}
     * @throws Exception If the driver cannot be found or resolved.
     */
    public function increment(string $key, int $value): int
    {
        return $this->driver()->increment($key, $value);
    }

    /**
     * {@inheritdoc}
     * @throws Exception If the driver cannot be found or resolved.
     */
    public function decrement(string $key, int $value): int
    {
        return $this->driver()->decrement($key, $value);
    }

    /**
     * {@inheritdoc}
     * @throws Exception If the driver cannot be found or resolved.
     */
    public function remember(string $key, Closure $callback, int|DateInterval|null $ttl = null): mixed
    {
        return $this->driver()->remember($key, $callback, $ttl);
    }
}
