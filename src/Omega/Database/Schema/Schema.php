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
    private $pdo;

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

    public function refresh(string $table_name): Truncate
    {
        $database_name = $this->pdo->configs()['database_name'];

        return new Truncate($database_name, $table_name, $this->pdo);
    }

    public function table(string $table_name, callable $blueprint): CreateTable
    {
        $database_name = $this->pdo->configs()['database_name'];
        $columns       = new CreateTable($database_name, $table_name, $this->pdo);
        $blueprint($columns);

        return $columns;
    }

    /**
     * Update table structur.
     *
     * @param string                $table_name Target table name
     * @param callable(Alter): void $blueprint
     */
    public function alter(string $table_name, callable $blueprint): Alter
    {
        $database_name = $this->pdo->configs()['database_name'];
        $columns       = new Alter($database_name, $table_name, $this->pdo);
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
