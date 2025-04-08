<?php

declare(strict_types=1);

namespace System\Database\Schema\Table;

use System\Database\Schema\SchemaConnection;
use System\Database\Schema\AbstractSchemaQuery;

class Raw extends AbstractSchemaQuery
{
    private string $raw;

    public function __construct(string $raw, SchemaConnection $pdo)
    {
        $this->raw   = $raw;
        $this->pdo   = $pdo;
    }

    protected function builder(): string
    {
        return $this->raw;
    }
}
