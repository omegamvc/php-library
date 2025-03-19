<?php

/**
 * Part of Omega - Collection Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace System\Collection;

use function array_chunk;
use function array_diff;
use function array_diff_assoc;
use function array_diff_key;
use function array_key_exists;
use function array_key_first;
use function array_reverse;
use function array_slice;
use function array_values;
use function arsort;
use function call_user_func;
use function ceil;
use function in_array;
use function is_array;
use function is_callable;

/**
 * Collection class.
 *
 * A mutable collection that extends `AbstractCollectionImmutable` and provides methods
 * for modifying its contents.
 *
 * This class allows adding, removing, and transforming elements in a structured way,
 * making it a flexible tool for handling data collections. Unlike `AbstractCollectionImmutable`,
 * this class permits in-place modifications.
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
 * @extends AbstractCollectionImmutable<TKey, TValue>
 */
class Collection extends AbstractCollectionImmutable
{
    /**
     * Sets a value in the collection using property-style access.
     *
     * @param TKey   $name  The key to assign the value to.
     * @param TValue $value The value to store.
     * @return void
     */
    public function __set($name, $value): void
    {
        $this->set($name, $value);
    }

    /**
     * Adds references from another collection.
     *
     * This method merges the contents of another `AbstractCollectionImmutable`
     * into the current collection.
     *
     * @param AbstractCollectionImmutable<TKey, TValue> $collection The collection to merge.
     * @return $this
     */
    public function ref(AbstractCollectionImmutable $collection): self
    {
        $this->add($collection->collection);

        return $this;
    }

    /**
     * Clears all elements from the collection.
     *
     * @return $this
     */
    public function clear(): self
    {
        $this->collection = [];

        return $this;
    }

    /**
     * Adds elements from an array to the collection.
     *
     * If a key already exists, its value is replaced.
     *
     * @param array<TKey, TValue> $collection The array of elements to add.
     * @return $this
     */
    public function add(array $collection): self
    {
        foreach ($collection as $key => $item) {
            $this->set($key, $item);
        }

        return $this;
    }

    /**
     * Removes an element from the collection by key.
     *
     * If the key does not exist, the method does nothing.
     *
     * @param TKey $name The key of the element to remove.
     * @return $this
     */
    public function remove($name): self
    {
        if ($this->has($name)) {
            unset($this->collection[$name]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $value): self
    {
        parent::set($name, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function push($value): self
    {
        parent::push($value);

        return $this;
    }

    /**
     * Replaces the current collection with a new one.
     *
     * This method clears the current collection and adds the elements
     * from the new collection.
     *
     * @param array<TKey, TValue> $new_collection The new collection to replace the current one.
     * @return $this
     */
    public function replace(array $new_collection): self
    {
        $this->collection = [];
        foreach ($new_collection as $key => $item) {
            $this->set($key, $item);
        }

        return $this;
    }

    /**
     * Applies a callback to each element in the collection.
     *
     * This method modifies the current collection based on the result of the
     * callback function, which receives the value and key of each item.
     *
     * @param callable(TValue, TKey=): TValue $callable The callback to apply to each element.
     * @return $this
     */
    public function map(callable $callable): self
    {
        if (!is_callable($callable)) {
            return $this;
        }

        $new_collection = [];
        foreach ($this->collection as $key => $item) {
            $new_collection[$key] = call_user_func($callable, $item, $key);
        }

        $this->replace($new_collection);

        return $this;
    }

    /**
     * Filters the collection based on a condition.
     *
     * This method retains only the elements for which the condition function
     * returns `true`.
     *
     * @param callable(TValue, TKey=): bool $condition_true The condition to test each element.
     * @return $this
     */
    public function filter(callable $condition_true): self
    {
        if (!is_callable($condition_true)) {
            return $this;
        }

        $new_collection = [];
        foreach ($this->collection as $key => $item) {
            $condition = $condition_true($item, $key);

            if ($condition === true) {
                $new_collection[$key] = $item;
            }
        }

        $this->replace($new_collection);

        return $this;
    }

    /**
     * Rejects elements from the collection based on a condition.
     *
     * This method removes elements for which the condition function returns `true`.
     *
     * @param callable(TValue, TKey=): bool $condition_true The condition to test each element.
     * @return $this
     */
    public function reject(callable $condition_true): self
    {
        if (!is_callable($condition_true)) {
            return $this;
        }

        $new_collection = [];
        foreach ($this->collection as $key => $item) {
            $condition = $condition_true($item, $key);

            if ($condition === false) {
                $new_collection[$key] = $item;
            }
        }

        $this->replace($new_collection);

        return $this;
    }

    /**
     * Reverses the order of elements in the collection.
     *
     * @return $this
     */
    public function reverse(): self
    {
        return $this->replace(array_reverse($this->collection));
    }

    /**
     * Sorts the collection in ascending order.
     *
     * @return $this
     */
    public function sort(): self
    {
        asort($this->collection);

        return $this;
    }

    /**
     * Sorts the collection in descending order.
     *
     * @return $this
     */
    public function sortDesc(): self
    {
        arsort($this->collection);

        return $this;
    }

    /**
     * Sorts the collection by a custom comparison function.
     *
     * @param callable $callable The comparison function to use for sorting.
     * @return $this
     */
    public function sortBy(callable $callable): self
    {
        uasort($this->collection, $callable);

        return $this;
    }

    /**
     * Sorts the collection by a custom comparison function in descending order.
     *
     * @param callable $callable The comparison function to use for sorting.
     * @return $this
     */
    public function sortByDesc(callable $callable): self
    {
        return $this->sortBy($callable)->reverse();
    }

    /**
     * Sorts the collection by its keys in ascending order.
     *
     * @return $this
     */
    public function sortKey(): self
    {
        ksort($this->collection);

        return $this;
    }

    /**
     * Sorts the collection by its keys in descending order.
     *
     * @return $this
     */
    public function sortKeyDesc(): self
    {
        krsort($this->collection);

        return $this;
    }

    /**
     * Creates a shallow copy of the current collection.
     *
     * @return Collection<TKey, TValue> A new collection instance.
     */
    public function clone(): Collection
    {
        return clone $this;
    }

    /**
     * Splits the collection into chunks of the given length.
     *
     * @param int  $length         The length of each chunk.
     * @param bool $preserve_keys Whether to preserve the original keys.
     * @return $this The current collection instance.
     */
    public function chunk(int $length, bool $preserve_keys = true): self
    {
        $this->collection = array_chunk($this->collection, $length, $preserve_keys);

        return $this;
    }

    /**
     * Splits the collection into the specified number of parts.
     *
     * @param int  $count          The number of parts.
     * @param bool $preserve_keys Whether to preserve the original keys.
     * @return $this The current collection instance.
     */
    public function split(int $count, bool $preserve_keys = true): self
    {
        $length = (int) ceil($this->length() / $count);

        return $this->chunk($length);
    }

    /**
     * Excludes elements with specified keys from the collection.
     *
     * @param TKey[] $excepts The keys to exclude from the collection.
     * @return $this The current collection instance.
     */
    public function except(array $excepts): self
    {
        /* @phpstan-ignore-next-line */
        $this->filter(fn ($item, $key) => !in_array($key, $excepts));

        return $this;
    }

    /**
     * Includes only elements with specified keys in the collection.
     *
     * @param TKey[] $only The keys to include in the collection.
     * @return $this The current collection instance.
     */
    public function only(array $only): self
    {
        /* @phpstan-ignore-next-line */
        $this->filter(fn ($item, $key) => in_array($key, $only));

        return $this;
    }

    /**
     * Flattens the collection into a single-level array up to the specified depth.
     *
     * @param int|float $depth The depth level to flatten (default is INF for unlimited depth).
     * @return $this The current collection instance.
     */
    public function flatten(int|float $depth = INF): self
    {
        $flatten = $this->flattenRecursing($this->collection, $depth);
        $this->replace($flatten);

        return $this;
    }

    /**
     * Recursively flattens an array up to a specified depth.
     *
     * @param array<TKey, TValue> $array The array to flatten.
     * @param int|float           $depth The depth level to flatten (default is INF for unlimited depth).
     * @return array<TKey, TValue> The flattened array.
     */
    private function flattenRecursing(array $array, int|float $depth = INF): array
    {
        $result = [];

        foreach ($array as $key => $item) {
            $item = $item instanceof Collection ? $item->all() : $item;

            if (!is_array($item)) {
                $result[$key] = $item;
            } else {
                $values = $depth === 1
                    ? array_values($item)
                    : $this->flattenRecursing($item, $depth - 1);

                foreach ($values as $key_dept => $value) {
                    $result[$key_dept] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * Converts the collection into an immutable collection.
     *
     * @return CollectionImmutable<TKey, TValue> A new immutable collection instance.
     */
    public function immutable(): CollectionImmutable
    {
        return new CollectionImmutable($this->collection);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset): void
    {
        $this->remove($offset);
    }

    /**
     * Randomly shuffles the elements of the collection.
     *
     * @return $this The current collection instance.
     */
    public function shuffle(): self
    {
        $items = $this->collection;
        $keys  = $this->keys();
        shuffle($keys);
        $reordered = [];
        foreach ($keys as $key) {
            $reordered[$key] = $items[$key];
        }

        return $this->replace($reordered);
    }

    /**
     * Converts the collection into an associative array by applying a callback function.
     *
     * @template TKeyItem of array-key
     * @template TValueItem
     *
     * @param callable(TValue, TKey=): array<TKeyItem, TValueItem> $callable The callback function to map the values.
     * @return $this The current collection instance.
     */
    public function assocBy(callable $callable): self
    {
        /** @var array<TKeyItem, TValueItem> $newCollection */
        $newCollection = [];
        foreach ($this->collection as $key => $item) {
            $arrayAssoc          = $callable($item, $key);
            $key                  = array_key_first($arrayAssoc);
            $newCollection[$key] = $arrayAssoc[$key];
        }

        return $this->replace($newCollection);
    }

    /**
     * Reduces the collection to a single value using a callback function.
     *
     * @param callable(TValue, TValue): TValue $callable The callback function to reduce the values.
     * @param TValue|null                      $carry   The initial value to start the reduction (default is null).
     * @return TValue|null The reduced value.
     */
    public function reduce(callable $callable, $carry = null)
    {
        foreach ($this->collection as $item) {
            $carry = $callable($carry, $item);
        }

        return $carry;
    }

    /**
     * Returns the first `limit` elements from the collection.
     *
     * @param int $limit The number of elements to take from the collection.
     * @return $this The current collection instance.
     */
    public function take(int $limit): self
    {
        if ($limit < 0) {
            return $this->replace(
                array_slice($this->collection, $limit, abs($limit))
            );
        }

        return $this->replace(
            array_slice($this->collection, 0, $limit)
        );
    }

    /**
     * Returns the elements of the collection that are not present in the given collection.
     *
     * @param array<TKey, TValue> $collection The collection to compare against.
     * @return $this The current collection instance.
     */
    public function diff(array $collection): self
    {
        return $this->replace(
            array_diff($this->collection, $collection)
        );
    }

    /**
     * Returns the elements of the collection that do not have the same keys as those in the given collection.
     *
     * @param array<TKey, TValue> $collection The collection to compare against.
     * @return $this The current collection instance.
     */
    public function diffKeys(array $collection): self
    {
        return $this->replace(
            array_diff_key($this->collection, $collection)
        );
    }

    /**
     * Returns the elements of the collection that do not have the same key-value pairs as ù
     * those in the given collection.
     *
     * @param array<TKey, TValue> $collection The collection to compare against.
     * @return $this The current collection instance.
     */
    public function diffAssoc(array $collection): self
    {
        return $this->replace(
            array_diff_assoc($this->collection, $collection)
        );
    }

    /**
     * Returns the elements of the collection that are not present in the given collection.
     *
     * @param array<TKey, TValue> $collection The collection to compare against.
     * @return $this The current collection instance.
     */
    public function complement(array $collection): self
    {
        return $this->replace(
            array_diff($collection, $this->collection)
        );
    }

    /**
     * Returns the elements of the collection that do not have the same keys as those in the given collection.
     *
     * @param array<TKey, TValue> $collection The collection to compare against.
     * @return $this The current collection instance.
     */
    public function complementKeys(array $collection): self
    {
        return $this->replace(
            array_diff_key($collection, $this->collection)
        );
    }

    /**
     * Returns the elements of the collection that do not have the same key-value pairs as those in the
     * given collection.
     *
     * @param array<TKey, TValue> $collection The collection to compare against.
     * @return $this The current collection instance.
     */
    public function complementAssoc(array $collection): self
    {
        return $this->replace(
            array_diff_assoc($collection, $this->collection)
        );
    }

    /**
     * Filters the collection based on the specified operator and value for a given key.
     *
     * @param TKey   $key      The key to filter by.
     * @param string $operator The operator to use for comparison.
     * @param TValue $value    The value to compare against.
     * @return $this The current collection instance.
     */
    public function where($key, string $operator, $value): self
    {
        if ('=' === $operator || '==' === $operator) {
            return $this->filter(fn ($TValue) => array_key_exists($key, $TValue) && $TValue[$key] == $value);
        }

        if ('===' === $operator) {
            return $this->filter(fn ($TValue) => array_key_exists($key, $TValue) && $TValue[$key] === $value);
        }

        if ('!=' === $operator) {
            return $this->filter(fn ($TValue) => array_key_exists($key, $TValue) && $TValue[$key] != $value);
        }

        if ('!==' === $operator) {
            return $this->filter(fn ($TValue) => array_key_exists($key, $TValue) && $TValue[$key] !== $value);
        }

        if ('>' === $operator) {
            return $this->filter(fn ($TValue) => array_key_exists($key, $TValue) && $TValue[$key] > $value);
        }

        if ('>=' === $operator) {
            return $this->filter(fn ($TValue) => array_key_exists($key, $TValue) && $TValue[$key] >= $value);
        }

        if ('<' === $operator) {
            return $this->filter(fn ($TValue) => array_key_exists($key, $TValue) && $TValue[$key] < $value);
        }

        if ('<=' === $operator) {
            return $this->filter(fn ($TValue) => array_key_exists($key, $TValue) && $TValue[$key] <= $value);
        }

        return $this->replace([]);
    }

    /**
     * Filters the collection where the values of a specified key are within a given range.
     *
     * @param TKey     $key   The key to filter by.
     * @param TValue[] $range The range of values to filter by.
     * @return $this The current collection instance.
     */
    public function whereIn($key, $range): self
    {
        return $this->filter(fn ($TValue) => array_key_exists($key, $TValue) && in_array($TValue[$key], $range));
    }

    /**
     * Filters the collection where the values of a specified key are not within a given range.
     *
     * @param TKey     $key   The key to filter by.
     * @param TValue[] $range The range of values to filter by.
     * @return $this The current collection instance.
     */
    public function whereNotIn($key, $range): self
    {
        return $this->filter(fn ($TValue) => array_key_exists($key, $TValue)
            && false === in_array($TValue[$key], $range));
    }
}
