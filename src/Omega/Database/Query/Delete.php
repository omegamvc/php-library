<?php

declare(strict_types=1);

namespace Omega\Database\Query;

use Omega\Database\Connection;
use Omega\Database\Query\Join\AbstractJoin;
use Omega\Database\Query\Traits\ConditionTrait;
use Omega\Database\Query\Traits\SubQueryTrait;

class Delete extends AbstractExecute
{
    use ConditionTrait;
    use SubQueryTrait;

    protected ?string $alias = null;

    public function __construct(string $tableName, Connection $pdo)
    {
        $this->table = $tableName;
        $this->pdo    = $pdo;
    }

    public function __toString()
    {
        return $this->builder();
    }

    /**
     * Set alias for the table.
     * If using an alias, conditions with binding values will be ignored,
     * except when using subqueries, clause in join also will be generate as alias.
     */
    public function alias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Join statment:
     *  - inner join
     *  - left join
     *  - right join
     *  - full join.
     */
    public function join(AbstractJoin $ref_table): self
    {
        $table = $this->alias ?? $this->table;
        $ref_table->table($table);

        $this->join[] = $ref_table->stringJoin();
        $binds         = (fn () => $this->{'subQuery'})->call($ref_table);

        if (null !== $binds) {
            $this->binds = array_merge($this->binds, $binds->getBind());
        }

        return $this;
    }

    private function getJoin(): string
    {
        return 0 === count($this->join)
            ? ''
            : implode(' ', $this->join)
        ;
    }

    protected function builder(): string
    {
        $build = [];

        $build['join']  = $this->getJoin();
        $build['where'] = $this->getWhere();

        $query_parts = implode(' ', array_filter($build, fn ($item) => $item !== ''));

        return $this->query =  null === $this->alias
            ? "DELETE FROM {$this->table} {$query_parts}"
            : "DELETE {$this->alias} FROM {$this->table} AS {$this->alias} {$query_parts}";
    }
}
