<?php

declare(strict_types=1);

namespace System\Validator\Rule;

use function array_filter;
use function in_array;

class FilterPool
{
    /** @var Filter|array */
    private Filter|array $pool = [];

    /**
     * Get entry filter rule.
     *
     * @return Filter[] Filter rule
     */
    public function getPool(): array
    {
        $rules = [];
        foreach ($this->pool as $ruler) {
            $field = $ruler['field'];
            $rule  = $ruler['rule'];
            if ($rule instanceof Filter) {
                // @phpstan-ignore-next-line
                $existRule     = $rules[$field] ?? new Filter();
                // @phpstan-ignore-next-line
                $rules[$field] = $existRule->combine($rule);
            }
        }

        return $rules;
    }

    /**
     * Combine filter rule with other filter rule.
     *
     * @param FilterPool $filterPool FilterPool class to combine
     * @return self
     */
    public function combine(FilterPool $filterPool): self
    {
        foreach ($filterPool->pool as $validRule) {
            $this->pool[] = $validRule;
        }

        return $this;
    }

    /**
     * Filter filter only allow field.
     *
     * @param array<int, string> $fields Fields allow to filter
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
     * Filter filter expect allow field.
     *
     * @param array<int, string> $fields Fields allow to filter
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
     * Add new Filter rule.
     *
     * @param string ...$field Field name
     * @return Filter New rule filter
     */
    public function rule(string ...$field): Filter
    {
        return $this->setFilterRule(new Filter(), $field);
    }

    /**
     * Add new filter rule.
     *
     * @param string ...$field Field name
     * @return Filter New rule filter
     */
    public function __invoke(string ...$field): Filter
    {
        return $this->rule(...$field);
    }

    /**
     * Add new filter rule.
     *
     * @param string $name Field name
     * @return Filter New rule filter
     */
    public function __get(string $name): Filter
    {
        return $this->rule($name);
    }

    /**
     * Set new fields rule.
     *
     * @param string $name  Field name
     * @param string $value Filter Rule
     * @return void
     */
    public function __set(string $name, string $value): void
    {
        $this->rule($name)->raw($value);
    }

    /**
     * Helper to add multi filter rule in single method.
     *
     * @param Filter                    $filter Installs for new filter rule
     * @param array<int|string, string> $fields Fields name
     * @return Filter Rule filter base from param
     */
    private function setFilterRule(Filter $filter, array $fields): Filter
    {
        foreach ($fields as $field) {
            $this->pool[] = [
                'field' => $field,
                'rule'  => $filter,
            ];
        }

        return $filter;
    }
}
