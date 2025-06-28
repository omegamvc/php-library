<?php

declare(strict_types=1);

namespace Omega\Database\Schema\Table;

use Omega\Database\Schema\Table\Attributes\Alter\DataType as AlterDataType;
use Omega\Database\Schema\Table\Attributes\DataType;

class Column
{
    /** @var string|DataType|AlterDataType */
    protected string|AlterDataType|DataType $query;

    public function __toString(): string
    {
        return (string) $this->query;
    }

    public function column(string $columnName): DataType
    {
        return $this->query = new DataType($columnName);
    }

    public function alterColumn(string $columnName): AlterDataType
    {
        return $this->query = new AlterDataType($columnName);
    }

    public function raw(string $query): self
    {
        $this->query = $query;

        return $this;
    }
}
