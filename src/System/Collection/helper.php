<?php

/**
 * Part of Omega - Collection Package
 * PHP version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace System\Collection;

use function array_key_exists;
use function array_merge;
use function array_slice;
use function count;
use function explode;
use function implode;
use function is_array;

/**
 * Collection Helper Functions.
 *
 * This file provides utility functions to simplify the creation and management of
 * collection instances, including mutable and immutable collections, as well as
 * a helper for retrieving values from nested arrays using dot notation.
 *
 * @category   Omega
 * @package    Collection
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
if (!function_exists('collection')) {
    /**
     * Creates a new mutable collection instance.
     *
     * This helper provides an easy way to instantiate a `Collection` object
     * from an iterable data source.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey, TValue> $collection The iterable data source.
     * @return Collection<TKey, TValue> The created collection instance.
     */
    function collection(iterable $collection = []): Collection
    {
        return new Collection($collection);
    }
}

if (!function_exists('collection_immutable')) {
    /**
     * Creates a new immutable collection instance.
     *
     * This helper allows creating an immutable collection from an iterable
     * data source, ensuring that its contents cannot be modified after initialization.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey, TValue> $collection The iterable data source.
     * @return Collection<TKey, TValue> The created immutable collection instance.
     */
    function collection_immutable(iterable $collection = []): Collection
    {
        return new Collection($collection);
    }
}

if (!function_exists('data_get')) {
    /**
     * Retrieves a value from a nested array using dot notation.
     *
     * This function allows accessing deeply nested values within an array
     * structure using a string-based dot notation.
     *
     * @template TValue
     * @template TGetDefault
     *
     * @param array<array-key, TValue> $array   The array to search within.
     * @param array-key                $key     The dot-notation key path.
     * @param TGetDefault              $default The default value to return if the key is not found.
     * @return TGetDefault|array<array-key, TValue>|null The retrieved value or the default if not found.
     */
    function data_get(array $array, int|string $key, mixed $default = null): mixed
    {
        $segments = explode('.', (string) $key);

        foreach ($segments as $segment) {
            if (!is_array($array)) {
                return $default;
            }
            if (array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } elseif ('*' === $segment) {
                $values = [];
                foreach ($array as $item) {
                    if (!is_array($item)) {
                        continue;
                    }

                    /** @var array<array-key, TValue> $item */
                    $value = data_get($item, implode('.', array_slice($segments, 1)), $default);
                    if (is_array($value)) {
                        $values = array_merge($values, $value);
                    } elseif (null !== $value) {
                        $values[] = $value;
                    }
                }

                /** @var array<array-key, TValue> $values */
                return count($values) > 0 ? $values : [];
            } else {
                return $default;
            }
        }

        return $array;
    }
}
