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

use DateInterval;
use DateTimeInterface;

use function microtime;
use function round;
use function time;

/**
 * AbstractStorage class.
 *
 * The `AbstractStorage` class serves as a base class for implementing different
 * types of storage systems. It provides common functionality and properties for
 * managing storage operations. This class is meant to be extended by other storage
 * implementations, ensuring that all derived classes adhere to a standard structure
 * for interacting with storage. It includes essential methods for handling storage
 * initialization, data retrieval, and data expiration management.
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
abstract class AbstractStorage implements StorageInterface
{
    /**
     * The constructor of the `AbstractStorage` class initializes the storage instance,
     * setting up the optional properties for the storage path and default Time-To-Live (TTL).
     *
     * - `$path` (optional): A string representing the storage location or directory path.
     *    If not provided, the storage system may use a default path or rely on other configurations.
     *
     * - `$defaultTTL` (optional): An integer representing the default TTL value in seconds for
     *    stored items. If not provided, the storage system may fall back to a predefined default
     *    value or operate without an expiration time.
     *
     * This constructor allows for flexible configuration of storage instances, providing the
     * option to specify a custom path and TTL or to use the system's defaults.
     *
     * @param string|null $path       Holds a string representing the storage location.
     * @param int|null    $defaultTTL Holds an integer representing a time to live value.
     * @return void
     */
    public function __construct(
        protected ?string $path = null,
        protected ?int $defaultTTL = null
    ) {
    }

    /**
     * Retrieve the default TTL (time to live).
     *
     * The `getDefaultTTL` method retrieves the default Time-To-Live (TTL) value
     * for the storage system. TTL determines the lifespan of stored items, after
     * which they are considered expired and eligible for removal. This method
     * provides the default TTL value, which can be used when no specific expiration
     * time is set for a particular item in the storage system. The default TTL is
     * typically defined in the configuration or storage settings.
     *
     * @return int|null Return the default time to live for the storage system.
     */
    public function getDefaultTTL(): ?int
    {
        return $this->defaultTTL;
    }

    /**
     * Calculates the precise modification time.
     *
     * Generates a timestamp with millisecond precision based on current time and microtime.
     *
     * @return float The calculated modification time.
     */
    public function createMtime(): float
    {
        $currentTime = time();
        $microtime   = microtime(true);

        $fractionalPart = $microtime - $currentTime;

        if ($fractionalPart >= 1) {
            $currentTime += (int) $fractionalPart;
            $fractionalPart -= (int) $fractionalPart;
        }

        $mtime = $currentTime + $fractionalPart;

        return round($mtime, 3);
    }

    /**
     * Retrieves cache information for a given key.
     *
     * Returns the cache data along with the timestamp and the modification time, if
     * available. If the cache key does not exist, or the data cannot be read, an empty
     * array is returned.
     *
     * @param string $key The cache key.
     * @return array<string, array{value: mixed, timestamp?: int, mtime?: float}> Cache data array with 'value',
     *                                                                            'timestamp', and 'mtime'.
     */
    abstract public function getInfo(string $key): array;

    /**
     * Calculates the expiration timestamp for the cache item.
     *
     * This method calculates the expiration timestamp based on the TTL (Time To Live), which can be either an
     * integer (in seconds), a `DateInterval`, or a `DateTimeInterface`. If no TTL is provided, the default TTL
     * is used.
     *
     * @param int|DateInterval|DateTimeInterface|null $ttl The TTL for the cache item, either in
     *                                                     seconds or as a `DateInterval`.
     * @return int The calculated expiration timestamp.
     */
    abstract public function calculateExpirationTimestamp(int|DateInterval|DateTimeInterface|null $ttl): int;
}
