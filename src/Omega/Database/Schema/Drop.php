<?php

declare(strict_types=1);

namespace Omega\Database\Schema;

/** Proxy for drop database and table */
class Drop
{
    /** @var SchemaConnection */
    private SchemaConnection $pdo;

    public function __construct(SchemaConnection $pdo)
    {
        $this->pdo = $pdo;
    }

    public function database(string $databaseName): DB\Drop
    {
        return new DB\Drop($databaseName, $this->pdo);
    }

    public function table(string $tableName): Table\Drop
    {
        $databaseName = $this->pdo->getConfig()['database_name'];

        return new Table\Drop($databaseName, $tableName, $this->pdo);
    }
}
