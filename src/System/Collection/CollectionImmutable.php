<?php

declare(strict_types=1);

namespace System\Collection;

use System\Collection\Exceptions\NoModifyException;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @extends AbstractCollectionImmutable<TKey, TValue>
 */
class CollectionImmutable extends AbstractCollectionImmutable
{
    /**
     * {@inheritdoc}
     *
     * @throws NoModifyException
     */
    public function offsetSet($offset, $value): void
    {
        throw new NoModifyException();
    }

    /**
     * {@inheritdoc}
     *
     * @throws NoModifyException
     */
    public function offsetUnset($offset): void
    {
        throw new NoModifyException();
    }
}
