<?php

declare(strict_types=1);

namespace System\Database\Schema\DB;

use System\Database\Schema\SchemaConnection;
use System\Database\Schema\AbstractSchemaQuery;
use System\Database\Schema\Traits\ConditionTrait;

class Drop extends AbstractSchemaQuery
{
    use ConditionTrait;

    /** @var string */
    private $database_name;

    public function __construct(string $database_name, SchemaConnection $pdo)
    {
        $this->database_name = $database_name;
        $this->pdo           = $pdo;
    }

    protected function builder(): string
    {
        $conditon = $this->join([$this->if_exists, $this->database_name]);

        return 'DROP DATABASE ' . $conditon . ';';
    }
}
