<?php

declare(strict_types=1);

namespace System\Database\Query\Join;

class FullJoin extends AbstractJoin
{
    protected function joinBuilder(): string
    {
        $on = $this->splitJoin();

        return "FULL OUTER JOIN $this->_tableName ON $on";
    }
}
