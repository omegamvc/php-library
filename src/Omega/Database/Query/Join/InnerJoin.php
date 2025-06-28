<?php

declare(strict_types=1);

namespace Omega\Database\Query\Join;

class InnerJoin extends AbstractJoin
{
    protected function joinBuilder(): string
    {
        return "INNER JOIN {$this->getAlias()} ON {$this->splitJoin()}";
    }
}
