<?php

declare(strict_types=1);

namespace Omega\Database\MySchema\Table;

use Omega\Database\MySchema\SchemaConnection;
use Omega\Database\MySchema\Query;
use Omega\Database\MySchema\Table\Attributes\Alter\DataType;

class Alter extends Query
{
    /** @var Column[]|DataType[] */
    private $alter_columns = [];

    /** @var Column[]|DataType[] */
    private $add_columns = [];

    /** @var string[] */
    private $drop_columns = [];

    /** @var array<string, string> */
    private $rename_columns = [];

    /** @var string */
    private $table_name;

    public function __construct(string $database_name, string $table_name, SchemaConnection $pdo)
    {
        $this->table_name   = $database_name . '.' . $table_name;
        $this->pdo          = $pdo;
    }

    /**
     * Add new column to the exist table.
     */
    public function __invoke(string $column_name): DataType
    {
        return $this->column($column_name);
    }

    public function add(string $column_name): DataType
    {
        return $this->add_columns[] = (new Column())->alterColumn($column_name);
    }

    public function drop(string $column_name): string
    {
        return $this->drop_columns[] = $column_name;
    }

    public function column(string $column_name): DataType
    {
        return $this->alter_columns[] = (new Column())->alterColumn($column_name);
    }

    public function rename(string $from, string $to): string
    {
        return $this->rename_columns[$from] = $to;
    }

    protected function builder(): string
    {
        $query = [];

        // merge alter, add, drop, rename
        $query = array_merge($query, $this->getModify(), $this->getColumns(), $this->getDrops(), $this->getRename());
        $query = implode(', ', $query);

        return "ALTER TABLE {$this->table_name} {$query};";
    }

    /** @return string[] */
    private function getModify(): array
    {
        $res = [];

        foreach ($this->alter_columns as $attribute) {
            $res[] = "MODIFY COLUMN {$attribute->__toString()}";
        }

        return $res;
    }

    /** @return string[] */
    private function getRename(): array
    {
        $res = [];

        foreach ($this->rename_columns as $old => $new) {
            $res[] = "RENAME COLUMN {$old} TO {$new}";
        }

        return $res;
    }

    /** @return string[] */
    private function getColumns(): array
    {
        $res = [];

        foreach ($this->add_columns as $attribute) {
            $res[] = "ADD {$attribute->__toString()}";
        }

        return $res;
    }

    /** @return string[] */
    private function getDrops(): array
    {
        $res = [];

        foreach ($this->drop_columns as $drop) {
            $res[] = "DROP COLUMN {$drop}";
        }

        return $res;
    }
}
