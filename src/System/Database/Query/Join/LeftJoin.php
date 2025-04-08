<?php

declare(strict_types=1);

namespace System\Database\Query\Join;

class LeftJoin extends AbstractJoin
{
    protected function joinBuilder(): string
    {
        $on = $this->splitJoin();

        return "LEFT JOIN $this->_tableName ON $on";
    }
}
