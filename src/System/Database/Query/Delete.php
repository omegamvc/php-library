<?php

declare(strict_types=1);

namespace System\Database\Query;

use System\Database\Connection;
use System\Database\Query\Traits\ConditionTrait;

class Delete extends AbstractExecute
{
    use ConditionTrait;

    public function __construct(string $table_name, Connection $PDO)
    {
        $this->_table = $table_name;
        $this->PDO    = $PDO;
    }

    public function __toString()
    {
        return $this->builder();
    }

    protected function builder(): string
    {
        $where = $this->getWhere();

        $this->_query = "DELETE FROM `$this->_table` $where";

        return $this->_query;
    }
}
