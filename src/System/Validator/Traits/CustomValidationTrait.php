<?php

declare(strict_types=1);

namespace System\Validator\Traits;

use function array_key_exists;

/**
 * Trait contain Custom validation.
 */
trait CustomValidationTrait
{
    /**
     * Check the field is contain in input fields.
     * Custom rule to prevent runtime error when validation is empty.
     *
     * @param string                $field
     * @param array<string, string> $input
     * @param array<string, string> $params
     * @param mixed                 $value
     * @return bool
     */
    protected function validate_(string $field, array $input, array $params, mixed $value): bool
    {
        return array_key_exists($field, $input);
    }
}
