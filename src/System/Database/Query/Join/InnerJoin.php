<?php

declare(strict_types=1);

namespace System\Database\Query\Join;

class InnerJoin extends AbstractJoin
{
    protected function joinBuilder(): string
    {
        $on = $this->splitJoin();

        return "INNER JOIN $this->_tableName ON $on";
    }
}
