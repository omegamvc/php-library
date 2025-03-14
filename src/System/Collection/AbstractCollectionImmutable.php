<?php

/**
 * Part of Omega - Collection Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
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
 *
 * @category   Omega
 * @package    Collection
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 *
 * @template TKey of array-key
 * @template TValue
 *
 * @implements CollectionInterface<TKey, TValue>
 */
abstract class AbstractCollectionImmutable implements CollectionInterface
{
    /**
     * @var array<TKey, TValue>
     */
    protected array $collection = [];

    /**
     * @param iterable<TKey, TValue> $collection
     */
    public function __construct(iterable $collection)
    {
        foreach ($collection as $key => $item) {
            $this->set($key, $item);
        }
    }

    /**
     * @param TKey $name
     *
     * @return TValue|null
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @return array<TKey, TValue>
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
     * @template TGetDefault
     *
     * @param int|string $name
     * @param TGetDefault|null $default
     * @return TValue|TGetDefault|null
     */
    public function get(int|string $name, $default = null)
    {
        return $this->collection[$name] ?? $default;
    }

    /**
     * @param TKey|null   $name
     * @param TValue $value
     * @return $this
     * @throws InvalidArgumentException if the  key is null.
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
     * Push item (set without key).
     *
     * @param TValue $value
     *
     * @return $this
     */
    protected function push($value): self
    {
        $this->collection[] = $value;

        return $this;
    }

    /**
     * @param TKey $key
     */
    public function has($key): bool
    {
        return array_key_exists($key, $this->collection);
    }

    /**
     * @param TValue $item
     */
    public function contain($item, bool $strict = false): bool
    {
        return in_array($item, $this->collection, $strict);
    }

    /**
     * @return TKey[]
     */
    public function keys(): array
    {
        return array_keys($this->collection);
    }

    /**
     * @return TValue[]
     */
    public function items(): array
    {
        return array_values($this->collection);
    }

    /**
     * @param TKey      $value Pluck key target as value
     * @param TKey|null $key   Pluck key target as key
     * @return array<TKey, TValue>
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
     * @return int
     */
    public function count(): int
    {
        return count($this->collection);
    }

    /**
     * @param callable(TValue, TKey=): bool $condition
     * @return int
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
     * @return array<int|string, int>
     */
    public function countBy(): array
    {
        return array_count_values($this->collection);
    }

    /**
     * @param callable(TValue, TKey=): (bool|void) $callable
     *
     * @return $this
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
     * @return $this
     */
    public function dump(): self
    {
        var_dump($this->collection);

        return $this;
    }

    /**
     * @param callable(TValue, TKey=): bool $condition
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
     * @param callable(TValue, TKey=): bool $condition
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
     * @return string|false
     */
    public function json(): string|false
    {
        return json_encode($this->collection);
    }

    /**
     * @template TGetDefault
     *
     * @param TGetDefault|null $default
     * @return TValue|TGetDefault|null
     */
    public function first($default = null)
    {
        $key = array_key_first($this->collection) ?? 0;

        return $this->collection[$key] ?? $default;
    }

    /**
     * @param positive-int $take
     *
     * @return array<TKey, TValue>
     */
    public function firsts(int $take): array
    {
        return array_slice($this->collection, 0, (int) $take);
    }

    /**
     * @template TGetDefault
     *
     * @param TGetDefault|null $default
     *
     * @return TValue|TGetDefault|null
     */
    public function last($default = null)
    {
        $key = array_key_last($this->collection);

        return $this->collection[$key] ?? $default;
    }

    /**
     * @param positive-int $take
     *
     * @return array<TKey, TValue>
     */
    public function lasts(int $take): array
    {
        return array_slice($this->collection, -$take, (int) $take);
    }

    /**
     * @return TKey|null
     */
    public function firstKey()
    {
        return array_key_first($this->collection);
    }

    /**
     * @return TKey|null
     */
    public function lastKey()
    {
        return array_key_last($this->collection);
    }

    /**
     * @return TValue|false
     */
    public function current()
    {
        return current($this->collection);
    }

    /**
     * @return TValue|false
     */
    public function next()
    {
        return next($this->collection);
    }

    /**
     * @return TValue|false
     */
    public function prev()
    {
        return prev($this->collection);
    }

    /**
     * @return TValue|null
     */
    public function rand()
    {
        $rand = array_rand($this->collection);

        return $this->get($rand);
    }

    public function isEmpty(): bool
    {
        return empty($this->collection);
    }

    public function length(): int
    {
        return count($this->collection);
    }

    /**
     * @return float|int
     */
    public function sum(): float|int
    {
        return array_sum($this->collection);
    }

    /**
     * @return float|int
     */
    public function avg(): float|int
    {
        return $this->sum() / $this->count();
    }

    /**
     * Find highest value.
     *
     * @param string|int|null $key
     * @return mixed
     * @throws InvalidArgumentException if the array for max is empty.
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
     * Find lowest value.
     *
     * @param string|int|null $key
     * @return mixed
     * @throws InvalidArgumentException if the array for min is empty.
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
     * @param TKey $offset
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
     * @param TKey $offset The offset to retrieve.
     * @return TValue|null The value at the given offset.
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
     * @param TKey|null $offset The offset to assign the value to.
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
     * @param TKey $offset The offset to unset.
     * @return void
     */
    public function offsetUnset($offset): void
    {
    }

    /**
     * @return Traversable<TKey, TValue>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->all());
    }

    public function __clone()
    {
        /** @phpstan-ignore-next-line */
        $this->collection = $this->deepClone($this->collection);
    }

    /**
     * @param array<TKey, TValue> $collection
     * @return array<TKey, TValue>
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
