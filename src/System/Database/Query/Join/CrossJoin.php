<?php

declare(strict_types=1);

namespace System\Database\Query\Join;

class CrossJoin extends AbstractJoin
{
    /**
     * Create cross join table query.
     */
    protected function joinBuilder(): string
    {
        return "CROSS JOIN $this->_tableName";
    }
}
