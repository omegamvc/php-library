<?php

declare(strict_types=1);

namespace System\Validator\Traits;

/**
 * Trait contain Custom Filter.
 */
trait CustomFilterTrait
{
    /**
     * Filter doest performer anything.
     * Custom rule to prevent runtime error when validation is empty.
     *
     * @template T
     * @param T                     $value
     * @param array<string, string> $params
     * @return T
     */
    protected function filter_($value, array $params = [])
    {
        return $value;
    }
}
