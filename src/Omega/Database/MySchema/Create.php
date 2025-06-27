<?php

declare(strict_types=1);

namespace Omega\Database\MySchema;

/** Proxy for create database and table */
class Create
{
    /** @var SchemaConnection */
    private $pdo;

    public function __construct(SchemaConnection $pdo)
    {
        $this->pdo = $pdo;
    }

    public function database(string $database_name): DB\Create
    {
        return new DB\Create($database_name, $this->pdo);
    }

    public function table(string $table_name): Table\Create
    {
        $database_name = $this->pdo->configs()['database_name'];

        return new Table\Create($database_name, $table_name, $this->pdo);
    }
}
