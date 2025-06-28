<?php

declare(strict_types=1);

namespace Omega\Database\Schema\Table\Attributes;

use Exception;

class Constraint
{
    /** @var string */
    protected string $dataType;

    /** @var string */
    protected string $nullable;

    /** @var string */
    protected string $default;

    /** @var string */
    protected string $autoIncrement;

    /** @var string */
    protected string $order;

    /** @var string */
    protected string $unsigned;

    /** @var string */
    protected string $raw;

    public function __construct(string $dataType)
    {
        $this->dataType     = $dataType;
        $this->nullable      = '';
        $this->default       = '';
        $this->autoIncrement = '';
        $this->raw           = '';
        $this->order         = '';
        $this->unsigned      = '';
    }

    public function __toString(): string
    {
        return $this->query();
    }

    private function query(): string
    {
        $column = [
            $this->dataType,
            $this->unsigned,
            $this->nullable,
            $this->default,
            $this->autoIncrement,
            $this->raw,
            $this->order,
        ];

        return implode(' ', array_filter($column, fn ($item) => $item !== ''));
    }

    public function notNull(bool $notNull = true): self
    {
        $this->nullable = $notNull ? 'NOT NULL' : 'NULL';

        return $this;
    }

    public function null(bool $null = true): self
    {
        return $this->notNull(!$null);
    }

    /**
     * Set default constraint.
     *
     * @param int|string $default Default set value
     * @param bool       $wrap    Wrap default value with "'"
     */
    public function default(int|string $default, bool $wrap = true): self
    {
        $wrap          = is_int($default) ? false : $wrap;
        $this->default = $wrap ? "DEFAULT '{$default}'" : "DEFAULT {$default}";

        return $this;
    }

    public function defaultNull(): self
    {
        return $this->default('NULL', false);
    }

    public function autoIncrement(bool $increment = true): self
    {
        $this->autoIncrement = $increment ? 'AUTO_INCREMENT' : '';

        return $this;
    }

    public function increment(bool $increment): self
    {
        return $this->autoIncrement($increment);
    }

    /**
     * Make datatype tobe unsigned (int, tinyint, bigint, smallint).
     * @throws Exception
     */
    public function unsigned(): self
    {
        if (false === preg_match('/^(int|tinyint|bigint|smallint)(\(\d+\))?$/', $this->dataType)) {
            throw new Exception('Cant use UNSIGNED not integer datatype.');
        }
        $this->unsigned = 'UNSIGNED';

        return $this;
    }

    public function raw(string $raw): self
    {
        $this->raw = $raw;

        return $this;
    }
}
