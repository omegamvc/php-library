<?php

declare(strict_types=1);

namespace Omega\Database\Schema;

use Omega\Database\Schema\Table\Alter;
use Omega\Database\Schema\Table\Create as CreateTable;
use Omega\Database\Schema\Table\Raw;
use Omega\Database\Schema\Table\Truncate;

class Schema
{
    /** @var SchemaConnection PDO property */
    private SchemaConnection $pdo;

    public function __construct(SchemaConnection $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(): Create
    {
        return new Create($this->pdo);
    }

    public function drop(): Drop
    {
        return new Drop($this->pdo);
    }

    public function refresh(string $tableName): Truncate
    {
        $databaseName = $this->pdo->getConfig()['database_name'];

        return new Truncate($databaseName, $tableName, $this->pdo);
    }

    public function table(string $tableName, callable $blueprint): CreateTable
    {
        $databaseName = $this->pdo->getConfig()['database_name'];
        $columns       = new CreateTable($databaseName, $tableName, $this->pdo);
        $blueprint($columns);

        return $columns;
    }

    /**
     * Update table structure.
     *
     * @param string                $tableName Target table name
     * @param callable(Alter): void $blueprint
     */
    public function alter(string $tableName, callable $blueprint): Alter
    {
        $databaseName = $this->pdo->getConfig()['database_name'];
        $columns       = new Alter($databaseName, $tableName, $this->pdo);
        $blueprint($columns);

        return $columns;
    }

    /**
     * Run raw table.
     */
    public function raw(string $raw): Raw
    {
        return new Raw($raw, $this->pdo);
    }
}
