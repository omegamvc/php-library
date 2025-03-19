<?php

/**
 * Part of Omega - Collection Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.00
 */

declare(strict_types=1);

namespace System\Collection;

use ArrayIterator;
use InvalidArgumentException;
use Traversable;

use function array_column;
use function array_count_values;
use function array_key_exists;
use function array_key_first;
use function array_key_last;
use function array_keys;
use function array_rand;
use function array_slice;
use function array_sum;
use function call_user_func;
use function count;
use function current;
use function in_array;
use function is_array;
use function is_null;
use function is_object;
use function json_encode;
use function max;
use function min;
use function next;
use function prev;
use function var_dump;

/**
 * AbstractCollectionImmutable class.
 *
 * An abstract immutable collection that provides basic methods for managing key-value pairs.
 * This class ensures immutability by only allowing modification through internal methods.
 *
 * @category   Omega
 * @package    Collection
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 *
 * @template TKey of array-key
 * @template TValue
 *
 * @implements CollectionInterface<TKey, TValue>
 */
abstract class AbstractCollectionImmutable implements CollectionInterface
{
    /**@var array<TKey, TValue> Stores the collection data as an associative array. */
    protected array $collection = [];

    /**
     * Initializes the collection with the given iterable data.
     *
     * @param iterable<TKey, TValue> $collection Holds the initial collection of key-value pairs.
     * @return void
     */
    public function __construct(iterable $collection)
    {
        foreach ($collection as $key => $item) {
            $this->set($key, $item);
        }
    }

    /**
     * Retrieves a value from the collection using the object property syntax.
     *
     * @param TKey $name Holds the key to retrieve.
     * @return TValue|null Return the value associated with the key or null if not found.
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Returns all elements in the collection as an associative array.
     *
     * @return array<TKey, TValue> Holds the entire collection.
     */
    public function all(): array
    {
        return $this->collection;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return $this->collection;
    }

    /**
     * Retrieves a value from the collection by key, with an optional default value.
     *
     * @template TGetDefault
     *
     * @param int|string $name The key to retrieve.
     * @param TGetDefault|null $default The default value if the key does not exist.
     * @return TValue|TGetDefault|null The value associated with the key or the default value.
     */
    public function get(int|string $name, $default = null)
    {
        return $this->collection[$name] ?? $default;
    }

    /**
     * Sets a value in the collection.
     *
     * @param TKey|null $name The key to assign the value to.
     * @param TValue $value The value to store.
     * @return $this
     * @throws InvalidArgumentException if the key is null.
     */
    protected function set(mixed $name, $value): self
    {
        if ($name === null) {
            throw new InvalidArgumentException("Key cannot be null.");
        }

        $this->collection[$name] = $value;

        return $this;
    }

    /**
     * Pushes an item into the collection without a key.
     *
     * @param TValue $value The value to append.
     * @return $this
     */
    protected function push($value): self
    {
        $this->collection[] = $value;

        return $this;
    }

    /**
     * Checks if a key exists in the collection.
     *
     * @param TKey $key The key to check.
     * @return bool True if the key exists, false otherwise.
     */
    public function has($key): bool
    {
        return array_key_exists($key, $this->collection);
    }

    /**
     * Checks if a value exists in the collection.
     *
     * @param TValue $item The value to check.
     * @param bool $strict Whether to use strict comparison (default: false).
     * @return bool True if the value is found, false otherwise.
     */
    public function contain($item, bool $strict = false): bool
    {
        return in_array($item, $this->collection, $strict);
    }

    /**
     * Returns all the keys in the collection.
     *
     * @return TKey[] The list of keys.
     */
    public function keys(): array
    {
        return array_keys($this->collection);
    }

    /**
     * Returns all values in the collection.
     *
     * @return TValue[] The list of values.
     */
    public function items(): array
    {
        return array_values($this->collection);
    }

    /**
     * Extracts a specific key's values from the collection.
     *
     * @param TKey $value The key whose values should be extracted.
     * @param TKey|null $key Optional key to use as index in the resulting array.
     * @return array<TKey, TValue> The extracted values.
     */
    public function pluck($value, $key = null): array
    {
        $results = [];

        foreach ($this->collection as $item) {
            if (is_array($item)) {
                $itemValue = $item[$value] ?? null;
            } elseif (is_object($item)) {
                $itemValue = $item->{$value} ?? null;
            } else {
                $itemValue = null;
            }

            if (is_null($key)) {
                $results[] = $itemValue;
                continue;
            }

            if (is_array($item)) {
                $itemKey = $item[$key] ?? null;
            } elseif (is_object($item)) {
                $itemKey = $item->{$key} ?? null;
            } else {
                $itemKey = null;
            }

            if ($itemKey !== null) {
                $results[$itemKey] = $itemValue;
            }
        }

        return $results;
    }

    /**
     * Returns the number of elements in the collection.
     *
     * @return int The total count.
     */
    public function count(): int
    {
        return count($this->collection);
    }

    /**
     * Counts the elements that satisfy a given condition.
     *
     * @param callable(TValue, TKey=): bool $condition The condition to check.
     * @return int The number of elements that match the condition.
     */
    public function countIf(callable $condition): int
    {
        $count = 0;
        foreach ($this->collection as $key => $item) {
            $doSomething = call_user_func($condition, $item, $key);

            $count += $doSomething === true ? 1 : 0;
        }

        return $count;
    }

    /**
     * Counts occurrences of values in the collection.
     *
     * @return array<int|string, int> An associative array where keys are values and values are counts.
     */
    public function countBy(): array
    {
        return array_count_values($this->collection);
    }

    /**
     * Iterates over the collection and applies a callback to each element.
     *
     * @param callable(TValue, TKey=): (bool|void) $callable The function to apply.
     * @return $this The current instance.
     */
    public function each(callable $callable): self
    {
        foreach ($this->collection as $key => $item) {
            $doSomething = call_user_func($callable, $item, $key);

            if (false === $doSomething) {
                break;
            }
        }

        return $this;
    }

    /**
     * Dumps the collection contents for debugging.
     *
     * @return $this The current instance.
     */
    public function dump(): self
    {
        var_dump($this->collection);

        return $this;
    }

    /**
     * Checks if at least one element satisfies the given condition.
     *
     * @param callable(TValue, TKey=): bool $condition The condition to check.
     * @return bool True if at least one element matches, false otherwise.
     */
    public function some(callable $condition): bool
    {
        foreach ($this->collection as $key => $item) {
            $call = call_user_func($condition, $item, $key);

            if ($call === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if all elements satisfy the given condition.
     *
     * @param callable(TValue, TKey=): bool $condition The condition to check.
     * @return bool True if all elements match, false otherwise.
     */
    public function every(callable $condition): bool
    {
        foreach ($this->collection as $key => $item) {
            $call = call_user_func($condition, $item, $key);

            if ($call === false) {
                return false;
            }
        }

        return true;
    }
    /**
     * Converts the collection to a JSON-encoded string.
     *
     * @return string|false The JSON representation or false on failure.
     */
    public function json(): string|false
    {
        return json_encode($this->collection);
    }

    /**
     * Retrieves the first element of the collection.
     *
     * @template TGetDefault
     * @param TGetDefault|null $default The default value if the collection is empty.
     * @return TValue|TGetDefault|null The first element or the default value.
     */
    public function first($default = null)
    {
        $key = array_key_first($this->collection) ?? 0;

        return $this->collection[$key] ?? $default;
    }

    /**
     * Retrieves the first N elements of the collection.
     *
     * @param positive-int $take The number of elements to retrieve.
     * @return array<TKey, TValue> The first N elements.
     */
    public function firsts(int $take): array
    {
        return array_slice($this->collection, 0, (int) $take);
    }

    /**
     * Retrieves the last element of the collection.
     *
     * @template TGetDefault
     * @param TGetDefault|null $default The default value if the collection is empty.
     * @return TValue|TGetDefault|null The last element or the default value.
     */
    public function last($default = null)
    {
        $key = array_key_last($this->collection);

        return $this->collection[$key] ?? $default;
    }

    /**
     * Retrieves the last N elements of the collection.
     *
     * @param positive-int $take The number of elements to retrieve.
     * @return array<TKey, TValue> The last N elements.
     */
    public function lasts(int $take): array
    {
        return array_slice($this->collection, -$take, (int) $take);
    }

    /**
     * Retrieves the first key in the collection.
     *
     * @return TKey|null The first key or null if the collection is empty.
     */
    public function firstKey()
    {
        return array_key_first($this->collection);
    }

    /**
     * Retrieves the last key in the collection.
     *
     * @return TKey|null The last key or null if the collection is empty.
     */
    public function lastKey()
    {
        return array_key_last($this->collection);
    }

    /**
     * Retrieves the current element in the collection.
     *
     * @return TValue|false The current value or false if invalid.
     */
    public function current()
    {
        return current($this->collection);
    }

    /**
     * Advances the internal pointer and returns the next element.
     *
     * @return TValue|false The next value or false if at the end.
     */
    public function next()
    {
        return next($this->collection);
    }

    /**
     * Moves the internal pointer backward and returns the previous element.
     *
     * @return TValue|false The previous value or false if at the beginning.
     */
    public function prev()
    {
        return prev($this->collection);
    }

    /**
     * Retrieves a random item from the collection.
     *
     * @return TValue|null The randomly selected item or null if the collection is empty.
     */
    public function rand()
    {
        $rand = array_rand($this->collection);

        return $this->get($rand);
    }

    /**
     * Checks if the collection is empty.
     *
     * @return bool True if the collection has no items, false otherwise.
     */
    public function isEmpty(): bool
    {
        return empty($this->collection);
    }

    /**
     * Gets the number of items in the collection.
     *
     * @return int The total count of items.
     */
    public function length(): int
    {
        return count($this->collection);
    }

    /**
     * Computes the sum of all numeric values in the collection.
     *
     * @return float|int The sum of all values.
     */
    public function sum(): float|int
    {
        return array_sum($this->collection);
    }

    /**
     * Computes the average of all numeric values in the collection.
     *
     * @return float|int The average value.
     */
    public function avg(): float|int
    {
        return $this->sum() / $this->count();
    }

    /**
     * Finds the highest value in the collection.
     *
     * @param string|int|null $key The key to retrieve the maximum value from, if the collection
     *                             contains arrays or objects.
     * @return mixed The highest value found.
     * @throws InvalidArgumentException If the collection is empty.
     */
    public function max(string|int|null $key = null): mixed
    {
        $column = array_column($this->collection, $key);

        if (empty($column)) {
            throw new InvalidArgumentException("Array for max is empty.");
        }

        return max($column);
    }

    /**
     * Finds the lowest value in the collection.
     *
     * @param string|int|null $key The key to retrieve the minimum value from, if the collection
     *                             contains arrays or objects.
     * @return mixed The lowest value found.
     * @throws InvalidArgumentException If the collection is empty.
     */
    public function min(string|int|null $key = null): mixed
    {
        $column = array_column($this->collection, $key);

        if (empty($column)) {
            throw new InvalidArgumentException("Array for min is empty.");
        }

        return min($column);
    }

    /**
     * Checks if the given offset exists in the collection.
     *
     * @param TKey $offset The key to check for existence.
     * @return bool True if the key exists, false otherwise.
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Retrieves the value at the given offset.
     *
     * This method is required by ArrayAccess.
     *
     * @param TKey $offset The key of the item to retrieve.
     * @return TValue|null The value at the given key or null if not found.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->__get($offset);
    }

    /**
     * Sets a value at the given offset.
     *
     * This method is required by ArrayAccess.
     *
     * @param TKey|null $offset The key to assign the value to.
     * @param TValue    $value  The value to store.
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Unsets the value at the given offset.
     *
     * This method is required by ArrayAccess.
     *
     * @param TKey $offset The key to remove from the collection.
     * @return void
     */
    public function offsetUnset($offset): void
    {
    }

    /**
     * Returns an iterator for traversing the collection.
     *
     * @return Traversable<TKey, TValue> An iterator for the collection.
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->all());
    }

    /**
     * Clones the collection, ensuring deep cloning of objects and nested arrays.
     *
     * @return void
     */
    public function __clone()
    {
        /** @phpstan-ignore-next-line */
        $this->collection = $this->deepClone($this->collection);
    }

    /**
     * Recursively clones an array, ensuring deep cloning of nested arrays and objects.
     *
     * @param array<TKey, TValue> $collection The collection to clone.
     * @return array<TKey, TValue> The cloned collection.
     */
    /** @phpstan-ignore-next-line */
    protected function deepClone(array $collection): array
    {
        $clone = [];
        foreach ($collection as $key => $value) {
            if (is_array($value)) {
                $clone[$key] = $this->deepClone($value);
            } elseif (is_object($value)) {
                $clone[$key] = clone $value;
            } else {
                $clone[$key] = $value;
            }
        }

        return $clone;
    }
}
