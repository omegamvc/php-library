<?php

/**
 * Part of Omega - Collection Package
 * PHP version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace System\Collection;

use ArrayAccess;
use Countable;
use IteratorAggregate;

/**
 * This interface defines a contract for a collection structure that supports
 * array-like access, iteration, and element counting.
 *
 * @category   Omega
 * @package    Collection
 * @subpackage Exceptions
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 *
 * @template TKey of array-key
 * @template TValue
 * @extends ArrayAccess<TKey, TValue>
 * @extends IteratorAggregate<TKey, TValue>
 */
interface CollectionInterface extends ArrayAccess, Countable, IteratorAggregate
{
    /**
     * Converts the collection into a plain associative array.
     *
     * @return array<TKey, TValue> The array representation of the collection.
     */
    public function toArray(): array;
}
