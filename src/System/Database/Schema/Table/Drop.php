<?php

declare(strict_types=1);

namespace System\Database\Schema\Table;

use System\Database\Schema\SchemaConnection;
use System\Database\Schema\AbstractSchemaQuery;
use System\Database\Schema\Traits\ConditionTrait;

class Drop extends AbstractSchemaQuery
{
    use ConditionTrait;

    /** @var string */
    private $table_name;

    public function __construct(string $database_name, string $table_name, SchemaConnection $pdo)
    {
        $this->table_name    = $database_name . '.' . $table_name;
        $this->pdo           = $pdo;
    }

    protected function builder(): string
    {
        $conditon = $this->join([$this->if_exists, $this->table_name]);

        return 'DROP TABLE ' . $conditon . ';';
    }
}
