<?php

declare(strict_types=1);

namespace System\Validator\Rule;

use function array_filter;
use function in_array;

class ValidPool
{
    /** @var Valid|array */
    private Valid|array $pool = [];

    /**
     * Get entry valid rule.
     *
     * @return Valid[] Valid rule
     */
    public function getPool(): array
    {
        $rules = [];
        foreach ($this->pool as $ruler) {
            $field = $ruler['field'];
            $rule  = $ruler['rule'];
            if ($rule instanceof Valid) {
                // @phpstan-ignore-next-line
                $existRule    = $rules[$field] ?? new Valid();
                // @phpstan-ignore-next-line
                $rules[$field] = $existRule->combine($rule);
            }
        }

        return $rules;
    }

    /**
     * Filter validation only allow field.
     *
     * @param array<int, string> $fields Fields allow to validation
     * @return self
     */
    public function only(array $fields): self
    {
        $this->pool = array_filter(
            $this->pool,
            fn ($field) => in_array($field['field'], $fields)
        );

        return $this;
    }

    /**
     * Filter validation expect allow field.
     *
     * @param array<int, string> $fields Fields allow to validation
     * @return self
     */
    public function except(array $fields): self
    {
        $this->pool = array_filter(
            $this->pool,
            fn ($field) => !in_array($field['field'], $fields)
        );

        return $this;
    }

    /**
     * Combine validation rule with other validation rule.
     *
     * @param ValidPool $validPool ValidPool class to combine
     * @return self
     */
    public function combine(ValidPool $validPool): self
    {
        foreach ($validPool->pool as $validRule) {
            $this->pool[] = $validRule;
        }

        return $this;
    }

    /**
     * Add new valid rule.
     *
     * @param string ...$field Field name
     * @return Valid New rule Validation
     */
    public function rule(string ...$field): Valid
    {
        return $this->setFieldRule(new Valid(), $field);
    }

    /**
     * Add new valid rule.
     *
     * @param string ...$field Field name
     * @return Valid New rule Validation
     */
    public function __invoke(string ...$field): Valid
    {
        return $this->rule(...$field);
    }

    /**
     * Add new valid rule.
     *
     * @param string $name Field name
     * @return Valid New rule Validation
     */
    public function __get(string $name): Valid
    {
        return $this->rule($name);
    }

    /**
     * Set new field rule.
     *
     * @param string $name  Field name
     * @param string $value Validation Rule
     * @return void
     */
    public function __set(string $name, string $value): void
    {
        $this->rule($name)->raw($value);
    }

    /**
     * Helper to add multi rule in single method.
     *
     * @param Valid                     $valid  Instance for new validation rule
     * @param array<int|string, string> $fields Fields name
     *
     * @return Valid Rule Validation base from param
     */
    private function setFieldRule(Valid $valid, array $fields): Valid
    {
        foreach ($fields as $field) {
            $this->pool[] = [
                'field' => $field,
                'rule'  => $valid,
            ];
        }

        return $valid;
    }
}
