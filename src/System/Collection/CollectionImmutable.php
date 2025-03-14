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

use System\Collection\Exceptions\NoModifyException;

/**
 * Immutable collection implementation.
 *
 * This class extends `AbstractCollectionImmutable`, providing an immutable
 * collection that prevents modifications after instantiation.
 *
 * Any attempt to modify the collection via `offsetSet()` or `offsetUnset()`
 * will result in a `NoModifyException`.
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
 * @extends AbstractCollectionImmutable<TKey, TValue>
 */
class CollectionImmutable extends AbstractCollectionImmutable
{
    /**
     * Prevents setting values in the collection.
     *
     * This method always throws a `NoModifyException` because the collection is immutable.
     *
     * @inheritdoc
     * @throws NoModifyException
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new NoModifyException();
    }

    /**
     * Prevents unsetting values in the collection.
     *
     * This method always throws a `NoModifyException` because the collection is immutable.
     *
     * @inheritdoc
     * @throws NoModifyException
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new NoModifyException();
    }
}
