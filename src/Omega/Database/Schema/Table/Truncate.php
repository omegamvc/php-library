<?php

declare(strict_types=1);

namespace Omega\Database\Schema\Table;

use Omega\Database\Schema\SchemaConnection;
use Omega\Database\Schema\AbstractQuery;
use Omega\Database\Schema\Traits\ConditionTrait;

class Truncate extends AbstractQuery
{
    use ConditionTrait;

    /** @var string */
    private string $tableName;

    public function __construct(string $databaseName, string $tableName, SchemaConnection $pdo)
    {
        $this->tableName    = $databaseName . '.' . $tableName;
        $this->pdo           = $pdo;
    }

    protected function builder(): string
    {
        $condition = $this->join([$this->ifExists, $this->tableName]);

        return 'TRUNCATE TABLE ' . $condition . ';';
    }
}
