<?php

declare(strict_types=1);

namespace Omega\Database\Schema\DB;

use Omega\Database\Schema\SchemaConnection;
use Omega\Database\Schema\AbstractQuery;
use Omega\Database\Schema\Traits\ConditionTrait;

class Create extends AbstractQuery
{
    use ConditionTrait;

    /** @var string */
    private string $databaseName;

    public function __construct(string $databaseName, SchemaConnection $pdo)
    {
        $this->databaseName = $databaseName;
        $this->pdo          = $pdo;
    }

    protected function builder(): string
    {
        $condition = $this->join([$this->ifExists, $this->databaseName]);

        return 'CREATE DATABASE ' . $condition . ';';
    }
}
