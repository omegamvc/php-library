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
use FilesystemIterator;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use function array_slice;
use function basename;
use function dirname;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function implode;
use function is_dir;
use function is_int;
use function is_null;
use function mkdir;
use function serialize;
use function sha1;
use function str_split;
use function time;
use function unlink;
use function unserialize;

use const LOCK_EX;

/**
 * FileStorage class.
 *
 * The `FileStorage` class implements the CacheInterface and provides a simple file-based
 * cache storage solution. It stores cache data in files, allowing for basic operations
 * such as storing, retrieving, and deleting cache items. Each cached item is serialized
 * and stored in a file with a unique path based on the cache key. The class supports
 * setting expiration times for cache items, either as a fixed time or a DateInterval.
 * It also provides methods to manage multiple cache items at once and to clear all stored
 * cache data. This class is intended for use in environments where a simple file-based
 * cache is sufficient and does not require advanced database or memory-based caching
 * solutions.
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
class FileStorage extends AbstractStorage
{
    /**
     * The constructor of the `FileStorage` class initializes the file-based storage instance,
     * setting the storage path and the default Time-To-Live (TTL) value.
     *
     * - `$path` (required): A string representing the directory path where the storage files will be stored.
     *
     * - `$defaultTTL` (optional): An integer representing the default TTL value in seconds for items
     *    stored in the file system. By default, it is set to 3,600 seconds (1 hour). If a custom TTL
     *    is provided, it will override the default.
     *
     * The constructor first calls the parent constructor to initialize the base storage functionality
     * with the specified path and TTL. It then checks if the provided directory exists. If the directory
     * does not exist, it is created with permissions `0777` and recursive directory creation enabled.
     *
     * @param string $path       Holds a string representing the storage location.
     * @param int    $defaultTTL Holds an integer representing a time to live value.
     * @return void
     */
    public function __construct(string $path, int $defaultTTL = 3_600)
    {
        parent::__construct($path, $defaultTTL);

        if (false === is_dir($this->path)) {
            mkdir($this->path, 0777, true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $filePath = $this->makePath($key);

        if (false === file_exists($filePath)) {
            return $default;
        }

        $data = file_get_contents($filePath);

        if ($data === false) {
            return $default;
        }

        $cacheData = unserialize($data);

        if (time() >= $cacheData['timestamp']) {
            $this->delete($key);

            return $default;
        }

        return $cacheData['value'];
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, mixed $value, int|DateInterval|null $ttl = null): bool
    {
        $filePath  = $this->makePath($key);
        $directory = dirname($filePath);

        if (false === is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $cacheData = [
            'value'     => $value,
            'timestamp' => $this->calculateExpirationTimestamp($ttl),
            'mtime'     => $this->createMtime(),
        ];

        $serializedData = serialize($cacheData);

        return file_put_contents($filePath, $serializedData, LOCK_EX) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key): bool
    {
        $filePath = $this->makePath($key);

        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $filePath = $fileinfo->getRealPath();

            if (basename($filePath) === '.gitignore') {
                continue;
            }

            $action = $fileinfo->isDir() ? 'rmdir' : 'unlink';
            $action($filePath);
        }

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
        $state = null;

        foreach ($values as $key => $value) {
            $result = $this->set($key, $value, $ttl);
            $state  = is_null($state) ? $result : $result && $state;
        }

        return $state ?: false;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $state = null;

        foreach ($keys as $key) {
            $result = $this->delete($key);
            $state  = is_null($state) ? $result : $result && $state;
        }

        return $state ?: false;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        return file_exists($this->makePath($key));
    }

    /**
     * {@inheritdoc}
     */
    public function increment(string $key, int $value): int
    {
        if (false === $this->has($key)) {
            $this->set($key, $value, 0);

            return $value;
        }

        $info = $this->getInfo($key);

        $ori = $info['value'] ?? 0;
        $ttl = $info['timestamp'] ?? 0;

        if (false === is_int($ori)) {
            throw new InvalidArgumentException('Value increment must be integer.');
        }

        $result = (int) ($ori + $value);

        $this->set($key, $result, $ttl);

        return $result;
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
     * Returns the cache data along with the timestamp and the modification time, if available. If the cache key does not exist,
     * or the data cannot be read, an empty array is returned.
     *
     * @param string $key The cache key.
     * @return array<string, array{value: mixed, timestamp?: int, mtime?: float}> Cache data array with 'value', 'timestamp', and 'mtime'.
     */
    public function getInfo(string $key): array
    {
        $filePath = $this->makePath($key);

        if (false === file_exists($filePath)) {
            return [];
        }

        $data = file_get_contents($filePath);

        if (false === $data) {
            return [];
        }

        return unserialize($data);
    }

    /**
     * Generates the full file path for a given cache key.
     *
     * This method generates a unique file path based on the cache key by hashing it and splitting the hash into parts.
     *
     * @param string $key The cache key.
     * @return string The generated file path for the cache.
     */
    protected function makePath(string $key): string
    {
        $hash  = sha1($key);
        $parts = array_slice(str_split($hash, 2), 0, 2);

        return $this->path . '/' . implode('/', $parts) . '/' . $hash;
    }

    /**
     * Calculates the expiration timestamp for the cache item.
     *
     * This method calculates the expiration timestamp based on the TTL (Time To Live), which can be either an integer (in seconds),
     * a `DateInterval`, or a `DateTimeInterface`. If no TTL is provided, the default TTL is used.
     *
     * @param int|DateInterval|DateTimeInterface|null $ttl The TTL for the cache item, either in seconds or as a `DateInterval`.
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

        return time() + $ttl;
    }
}
