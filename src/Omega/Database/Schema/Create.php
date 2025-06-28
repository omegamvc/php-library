<?php

declare(strict_types=1);

namespace Omega\Database\Schema;

/** Proxy for create database and table */
class Create
{
    /** @var SchemaConnection */
    private SchemaConnection $pdo;

    public function __construct(SchemaConnection $pdo)
    {
        $this->pdo = $pdo;
    }

    public function database(string $databaseName): DB\Create
    {
        return new DB\Create($databaseName, $this->pdo);
    }

    public function table(string $tableName): Table\Create
    {
        $databaseName = $this->pdo->getConfig()['database_name'];

        return new Table\Create($databaseName, $tableName, $this->pdo);
    }
}
