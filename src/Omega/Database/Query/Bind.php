<?php

declare(strict_types=1);

namespace Omega\Database\Query;

/**
 * @internal
 */
final class Bind
{
    /**
     * bind name (required).
     *
     * @var string
     */
    private string $bind;

    /**
     * Bind value (required).
     *
     * @var mixed
     */
    private mixed $bindValue;

    /**
     * represented column name (optional).
     *
     * @var string
     */
    private string $columnName;

    /**
     * set prefix bind (bind name not same with column name).
     *
     * @var string
     */
    private string $prefixBind;

    /**
     * @param mixed $value
     */
    public function __construct(string $bind, mixed $value, string $columnName = '')
    {
        $this->bind       = $bind;
        $this->bindValue  = $value;
        $this->columnName = $columnName;
        $this->prefixBind = ':';
    }

    /**
     * @param mixed $value
     */
    public static function set(string $bind, mixed $value, string $columnName = ''): self
    {
        return new static($bind, $value, $columnName);
    }

    public function prefixBind(string $prefix): self
    {
        $this->prefixBind = $prefix;

        return $this;
    }

    public function setBind(string $bind): self
    {
        $this->bind = $bind;

        return $this;
    }

    public function setValue(mixed $bindValue): self
    {
        $this->bindValue = $bindValue;

        return $this;
    }

    public function setColumnName(string $columnName): self
    {
        $this->columnName = $columnName;

        return $this;
    }

    public function getBind(): string
    {
        return $this->prefixBind . $this->bind;
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->bindValue;
    }

    public function getColumnName(): string
    {
        return $this->columnName;
    }

    public function hasColumName(): bool
    {
        return '' !== $this->columnName;
    }

    public function markAsColumn(): self
    {
        $this->columnName = $this->bind;

        return $this;
    }

    public function hasBind(): bool
    {
        return '' === $this->bind;
    }
}
