<?php

declare(strict_types=1);

namespace System\Database\Query\Join;

class RightJoin extends AbstractJoin
{
    protected function joinBuilder(): string
    {
        $on = $this->splitJoin();

        return "RIGHT JOIN $this->_tableName ON $on";
    }
}
