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
    private $table_name;

    public function __construct(string $database_name, string $table_name, SchemaConnection $pdo)
    {
        $this->table_name    = $database_name . '.' . $table_name;
        $this->pdo           = $pdo;
    }

    protected function builder(): string
    {
        $conditon = $this->join([$this->if_exists, $this->table_name]);

        return 'TRUNCATE TABLE ' . $conditon . ';';
    }
}
