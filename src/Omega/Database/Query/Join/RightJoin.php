<?php

declare(strict_types=1);

namespace Omega\Database\Query\Join;

class RightJoin extends AbstractJoin
{
    protected function joinBuilder(): string
    {
        return "RIGHT JOIN {$this->getAlias()} ON {$this->splitJoin()}";
    }
}
