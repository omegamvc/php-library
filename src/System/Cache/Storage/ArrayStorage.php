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

namespace System\Cache\Storage;

use Closure;
use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;

use function array_key_exists;
use function time;

/**
 * ArrayStorage class.
 *
 * The `ArrayStorage` class implements the `CacheInterface` and provides an in-memory
 * caching solution. It is designed for scenarios where fast, temporary storage is
 * needed without persistent storage, such as caching within a single request lifecycle.
 * The class stores cache entries in an associative array, where each key corresponds to
 * a cached value along with optional metadata like expiration timestamp and modification time.
 *
 * It supports setting expiration times using integers (in seconds), `DateInterval`, or
 * `DateTimeInterface`. The class also includes utility methods to check for expired entries
 * and to generate precise timestamps for cache tracking. Since this storage is purely in-memory,
 * all data is lost once the object is destroyed.
 *
 * @category   System
 * @package    Cache
 * @subpackage Storage
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class ArrayStorage extends AbstractStorage
{
    /**
     * Internal storage for cache items.
     *
     * Each entry consists of:
     * - `value`: The cached data.
     * - `timestamp` (optional): The expiration timestamp.
     * - `mtime` (optional): The precise modification time.
     *
     * @var array<string, array{value: mixed, timestamp?: int, mtime?: float}>
     */
    protected array $storage = [];

    /**
     * The constructor of the `ArrayStorage` class initializes the array-based storage instance,
     * setting the default Time-To-Live (TTL) value.
     *
     * - `$defaultTTL` (optional): An integer representing the default TTL value in seconds for
     *    items stored in the array. By default, it is set to 3,600 seconds (1 hour). If a custom
     *    TTL is provided, it will override the default.
     *
     * This constructor calls the parent constructor to initialize the base storage functionality
     * with the specified TTL.
     *
     * @param int|null $defaultTTL Holds an integer representing a time to live value.
     */
    public function __construct(?int $defaultTTL = 3_600)
    {
        $path = null;

        parent::__construct($path, $defaultTTL);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (false === array_key_exists($key, $this->storage)) {
            return $default;
        }

        $item = $this->storage[$key];

        $expiresAt = $item['timestamp'] ?? 0;

        if ($this->isExpired($expiresAt)) {
            $this->delete($key);

            return $default;
        }

        return $item['value'];
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, mixed $value, int|DateInterval|null $ttl = null): bool
    {
        $this->storage[$key] = [
            'value'     => $value,
            'timestamp' => $this->calculateExpirationTimestamp($ttl),
            'mtime'     => $this->createMtime(),
        ];

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key): bool
    {
        if ($this->has($key)) {
            unset($this->storage[$key]);

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        $this->storage = [];

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function setMultiple(iterable $values, int|DateInterval|null $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $state = null;

        foreach ($keys as $key) {
            $result = $this->delete($key);

            $state = null === $state ? $result : $result && $state;
        }

        return $state ?: false;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->storage);
    }

    /**
     * {@inheritdoc}
     */
    public function increment(string $key, int $value): int
    {
        if (false === $this->has($key)) {
            $this->set($key, $value, 0);

            return $this->storage[$key]['value'];
        }

        $this->storage[$key]['value'] = ((int) $this->storage[$key]['value']) + $value;

        return $this->storage[$key]['value'];
    }

    /**
     * {@inheritdoc}
     */
    public function decrement(string $key, int $value): int
    {
        return $this->increment($key, $value * -1);
    }

    /**
     * {@inheritdoc}
     */
    public function remember(string $key, Closure $callback, int|DateInterval|null $ttl = null): mixed
    {
        $value = $this->get($key);

        if (null !== $value) {
            return $value;
        }

        $this->set($key, $value = $callback(), $ttl);

        return $value;
    }

    /**
     * Retrieves cache information for a given key.
     *
     * Returns stored cache data along with metadata if available.
     * If the key does not exist, an empty array is returned.
     *
     * @param string $key The cache key.
     * @return array<string, array{value: mixed, timestamp?: int, mtime?: float}> Cache data with metadata.
     */
    public function getInfo(string $key): array
    {
        return $this->storage[$key] ?? [];
    }

    /**
     * Determines whether a cache entry has expired.
     *
     * @param int $timestamp The expiration timestamp of the cache item.
     * @return bool `true` if expired, `false` otherwise.
     */
    private function isExpired(int $timestamp): bool
    {
        return $timestamp !== 0 && time() >= $timestamp;
    }

    /**
     * Calculates the expiration timestamp for a cache item.
     *
     * The expiration can be defined as:
     * - An integer (number of seconds from now).
     * - A `DateInterval` (to calculate expiration relative to now).
     * - A `DateTimeInterface` (an exact expiration time).
     * If no TTL is provided, the default TTL is used.
     *
     * @param int|DateInterval|DateTimeInterface|null $ttl The TTL duration.
     * @return int The calculated expiration timestamp.
     */
    public function calculateExpirationTimestamp(int|DateInterval|DateTimeInterface|null $ttl): int
    {
        if ($ttl instanceof DateInterval) {
            return (new DateTimeImmutable())->add($ttl)->getTimestamp();
        }

        if ($ttl instanceof DateTimeInterface) {
            return $ttl->getTimestamp();
        }

        $ttl ??= $this->getDefaultTTL();

        return (new DateTimeImmutable())->add(new DateInterval("PT{$ttl}S"))->getTimestamp();
    }
}
