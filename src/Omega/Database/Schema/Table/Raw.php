<?php

declare(strict_types=1);

namespace Omega\Database\Schema\Table;

use Omega\Database\Schema\SchemaConnection;
use Omega\Database\Schema\AbstractQuery;

class Raw extends AbstractQuery
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
