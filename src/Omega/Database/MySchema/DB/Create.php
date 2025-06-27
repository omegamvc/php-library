<?php

declare(strict_types=1);

namespace Omega\Database\MySchema\DB;

use Omega\Database\MySchema\SchemaConnection;
use Omega\Database\MySchema\Query;
use Omega\Database\MySchema\Traits\ConditionTrait;

class Create extends Query
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

        return 'CREATE DATABASE ' . $conditon . ';';
    }
}
