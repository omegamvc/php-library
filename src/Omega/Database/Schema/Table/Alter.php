<?php

declare(strict_types=1);

namespace Omega\Database\Schema\Table;

use Omega\Database\Schema\SchemaConnection;
use Omega\Database\Schema\AbstractQuery;
use Omega\Database\Schema\Table\Attributes\Alter\DataType;

class Alter extends AbstractQuery
{
    /** @var Column[]|DataType[] */
    private array $alterColumns = [];

    /** @var Column[]|DataType[] */
    private array $addColumns = [];

    /** @var string[] */
    private array $dropColumns = [];

    /** @var array<string, string> */
    private array $renameColumns = [];

    /** @var string */
    private string $tableName;

    public function __construct(string $databaseName, string $tableName, SchemaConnection $pdo)
    {
        $this->tableName   = $databaseName . '.' . $tableName;
        $this->pdo         = $pdo;
    }

    /**
     * Add new column to the exist table.
     */
    public function __invoke(string $columnName): DataType
    {
        return $this->column($columnName);
    }

    public function add(string $columnName): DataType
    {
        return $this->addColumns[] = (new Column())->alterColumn($columnName);
    }

    public function drop(string $columnName): string
    {
        return $this->dropColumns[] = $columnName;
    }

    public function column(string $columnName): DataType
    {
        return $this->alterColumns[] = (new Column())->alterColumn($columnName);
    }

    public function rename(string $from, string $to): string
    {
        return $this->renameColumns[$from] = $to;
    }

    protected function builder(): string
    {
        $query = [];

        // merge alter, add, drop, rename
        $query = array_merge($query, $this->getModify(), $this->getColumns(), $this->getDrops(), $this->getRename());
        $query = implode(', ', $query);

        return "ALTER TABLE {$this->tableName} {$query};";
    }

    /** @return string[] */
    private function getModify(): array
    {
        $res = [];

        foreach ($this->alterColumns as $attribute) {
            $res[] = "MODIFY COLUMN {$attribute->__toString()}";
        }

        return $res;
    }

    /** @return string[] */
    private function getRename(): array
    {
        $res = [];

        foreach ($this->renameColumns as $old => $new) {
            $res[] = "RENAME COLUMN {$old} TO {$new}";
        }

        return $res;
    }

    /** @return string[] */
    private function getColumns(): array
    {
        $res = [];

        foreach ($this->addColumns as $attribute) {
            $res[] = "ADD {$attribute->__toString()}";
        }

        return $res;
    }

    /** @return string[] */
    private function getDrops(): array
    {
        $res = [];

        foreach ($this->dropColumns as $drop) {
            $res[] = "DROP COLUMN {$drop}";
        }

        return $res;
    }
}
