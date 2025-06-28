<?php

declare(strict_types=1);

namespace Omega\Database\Query\Join;

class FullJoin extends AbstractJoin
{
    protected function joinBuilder(): string
    {
        return "FULL OUTER JOIN {$this->getAlias()} ON {$this->splitJoin()}";
    }
}
