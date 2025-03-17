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

/**
 * CacheInterface class.
 *
 * The `CacheInterface` defines a standard contract for caching mechanisms, providing a consistent
 * API for storing, retrieving, and managing cached data. Implementations of this interface allow
 * applications to temporarily store data, reducing redundant computations and improving performance.
 *
 * Caching systems that conform to this interface can:
 *
 * - Store and retrieve individual and multiple cache entries.
 * - Set expiration times for cached data.
 * - Remove specific cache items or clear the entire cache.
 * - Increment and decrement numerical values in cache storage.
 * - Utilize lazy-loading via the remember method, executing a callback when data is missing.
 *
 * @category  System
 * @package   Cache
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
interface CacheInterface
{
    /**
     * Retrieves an item from the cache by its unique key.
     * If the key does not exist or is expired, the provided default value is returned.
     *
     * @param string $key     The cache key to retrieve.
     * @param mixed  $default The default value to return if the key is not found.
     * @return mixed The cached value or the default value.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Stores a value in the cache under a unique key, optionally specifying a time-to-live (TTL).
     *
     * @param string                $key   The cache key.
     * @param mixed                 $value The value to store.
     * @param int|DateInterval|null $ttl   The time-to-live duration; if null, it persists indefinitely.
     * @return bool True on success, false on failure.
     */
    public function set(string $key, mixed $value, int|DateInterval|null $ttl = null): bool;

    /**
     * Deletes an item from the cache by its unique key.
     *
     * @param string $key The cache key to delete.
     * @return bool True if the item was successfully deleted, false otherwise.
     */
    public function delete(string $key): bool;

    /**
     * Clears all cache entries, effectively resetting the cache.
     *
     * @return bool True on success, false on failure.
     */
    public function clear(): bool;

    /**
     * Retrieves multiple cache items at once.
     * If a key does not exist or is expired, the provided default value is used.
     *
     * @param iterable<string> $keys    A list of cache keys to retrieve.
     * @param mixed            $default The default value for missing keys.
     * @return iterable<string, mixed>  An associative array of key-value pairs.
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable;

    /**
     * Stores multiple key-value pairs in the cache at once, with an optional expiration TTL.
     *
     * @param iterable<string, mixed> $values An associative array of key-value pairs.
     * @param int|DateInterval|null   $ttl    The time-to-live duration; if null, items persist indefinitely.
     * @return bool True on success, false on failure.
     */
    public function setMultiple(iterable $values, int|DateInterval|null $ttl = null): bool;

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable<string> $keys A list of cache keys to delete.
     * @return bool True on success, false on failure.
     */
    public function deleteMultiple(iterable $keys): bool;

    /**
     * Checks if an item exists in the cache without retrieving it.
     *
     * @param string $key The cache key to check.
     * @return bool True if the key exists, false otherwise.
     */
    public function has(string $key): bool;

    /**
     * Increments a numeric value in the cache.
     * If the key does not exist, it is initialized to 0 before incrementing.
     *
     * @param string $key   The cache key.
     * @param int    $value The amount to increment by.
     * @return int The new value after incrementing.
     */
    public function increment(string $key, int $value): int;

    /**
     * Decrements a numeric value in the cache.
     * If the key does not exist, it is initialized to 0 before decrementing.
     *
     * @param string $key   The cache key.
     * @param int    $value The amount to decrement by.
     * @return int The new value after decrementing.
     */
    public function decrement(string $key, int $value): int;

    /**
     * Retrieves an item from the cache, or stores a computed value if the key does not exist.
     * If the key is not found, the callback is executed, its result is cached, and then returned.
     *
     * @param string                $key      The cache key.
     * @param Closure               $callback A function that generates the value if the key is missing.
     * @param int|DateInterval|null $ttl      The time-to-live duration; if null, it persists indefinitely.
     * @return mixed The cached or newly computed value.
     */
    public function remember(string $key, Closure $callback, int|DateInterval|null $ttl = null): mixed;
}
