<?php

declare(strict_types=1);

namespace Omega\Database\MyQuery\Join;

class LeftJoin extends AbstractJoin
{
    protected function joinBuilder(): string
    {
        $on = $this->splitJoin();

        return "LEFT JOIN {$this->getAlias()} ON {$on}";
    }
}
