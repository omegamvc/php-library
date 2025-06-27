<?php

declare(strict_types=1);

namespace Omega\Database\Schema\Table;

use Omega\Database\Schema\Table\Attributes\Alter\DataType as AlterDataType;
use Omega\Database\Schema\Table\Attributes\DataType;

class Column
{
    /** @var string|DataType|AlterDataType */
    protected $query;

    public function __toString()
    {
        return (string) $this->query;
    }

    public function column(string $column_name): DataType
    {
        return $this->query = new DataType($column_name);
    }

    public function alterColumn(string $column_name): AlterDataType
    {
        return $this->query = new AlterDataType($column_name);
    }

    public function raw(string $query): self
    {
        $this->query = $query;

        return $this;
    }
}
