<?php

declare(strict_types=1);

namespace Omega\Database\Query;

use Omega\Database\Connection;
use Omega\Database\Query\Query;
use Omega\Database\Query\Join\AbstractJoin;
use Omega\Database\Query\Traits\ConditionTrait;
use Omega\Database\Query\Traits\SubQueryTrait;

final class Select extends AbstractFetch
{
    use ConditionTrait;
    use SubQueryTrait;

    /**
     * @param string|InnerQuery $tableName   Table name
     * @param string[]          $columnsName Selected column
     * @param Connection        $pdo         MyPDO class
     * @param string[]          $options     Add costume option (eg: query)
     *
     * @return void
     */
    public function __construct(
        string|InnerQuery $tableName,
        array $columnsName,
        Connection $pdo,
        ?array $options = null
    ) {
        $this->subQuery = $tableName instanceof InnerQuery ? $tableName : new InnerQuery(table: $tableName);
        $this->column   = $columnsName;
        $this->pdo      = $pdo;

        // inherit bind from sub query
        if ($tableName instanceof InnerQuery) {
            $this->binds = $tableName->getBind();
        }

        $column       = implode(', ', $columnsName);
        $this->query = $options['query'] ?? "SELECT {$column} FROM { $this->subQuery}";
    }

    public function __toString()
    {
        return $this->builder();
    }

    /**
     * Instance of `Select::class`.
     *
     * @param string   $tableName  Table name
     * @param string[] $columnName Selected column
     * @param Connection    $pdo         MyPdo
     *
     * @return Select
     */
    public static function from(string $tableName, array $columnName, Connection $pdo): Select
    {
        return new static($tableName, $columnName, $pdo);
    }

    /**
     * Join statment:
     *  - inner join
     *  - left join
     *  - right join
     *  - full join.
     */
    public function join(AbstractJoin $refTable): self
    {
        // override master table
        $refTable->table($this->subQuery->getAlias());

        $this->join[] = $refTable->stringJoin();
        $binds        = (fn () => $this->{'subQuery'})->call($refTable);

        if (null !== $binds) {
            $this->binds = array_merge($this->binds, $binds->getBind());
        }

        return $this;
    }

    private function joinBuilder(): string
    {
        return 0 === count($this->join)
            ? ''
            : implode(' ', $this->join)
        ;
    }

    /**
     * Set data start for fetc all data.
     *
     * @param int $limitStart limit start
     * @param int $limitEnd   limit end
     *
     * @return self
     */
    public function limit(int $limitStart, int $limitEnd): self
    {
        $this->limitStart($limitStart);
        $this->limitEnd($limitEnd);

        return $this;
    }

    /**
     * Set data start for fetch all data.
     *
     * @param int $value limit start default is 0
     *
     * @return self
     */
    public function limitStart(int $value): self
    {
        $this->limitStart = max($value, 0);

        return $this;
    }

    /**
     * Set data end for feact all data
     * zero value meaning no data show.
     *
     * @param int $value limit start default
     *
     * @return self
     */
    public function limitEnd(int $value): self
    {
        $this->limitEnd = max($value, 0);

        return $this;
    }

    /**
     * Set offest.
     *
     * @param int $value offset
     * @return self
     */
    public function offset(int $value): self
    {
        $this->offset = max($value, 0);

        return $this;
    }

    /**
     * Set limit using limit and offset.
     *
     * @param int $limit
     * @param int $offset
     * @return self
     */
    public function limitOffset(int $limit, int $offset): self
    {
        return $this
            ->limitStart($limit)
            ->limitEnd(0)
            ->offset($offset);
    }

    /**
     * Set sort column and order
     * column name must register.
     *
     * @param string $columnName
     * @param int $orderUsing
     * @param string|null $belongTo
     * @return $this
     */
    public function order(string $columnName, int $orderUsing = Query::ORDER_ASC, ?string $belongTo = null): self
    {
        $order = 0 === $orderUsing ? 'ASC' : 'DESC';
        $belongTo ??= null === $this->subQuery ? $this->table : $this->subQuery->getAlias();
        $res = "{$belongTo}.{$columnName}";

        $this->sortOrder[$res] = $order;

        return $this;
    }

    /**
     * Set sort column and order
     * with Column if not null.
     *
     * @param string $columnName
     * @param int $orderUsing
     * @param string|null $belongTo
     * @return $this
     */
    public function orderIfNotNull(string $columnName, int $orderUsing = Query::ORDER_ASC, ?string $belongTo = null): self
    {
        return $this->order("{$columnName} IS NOT NULL", $orderUsing, $belongTo);
    }

    /**
     * Set sort column and order
     * with Column if null.
     *
     * @param string $columnName
     * @param int $orderUsing
     * @param string|null $belongTo
     * @return $this
     */
    public function orderIfNull(string $columnName, int $orderUsing = Query::ORDER_ASC, ?string $belongTo = null): self
    {
        return $this->order("{$columnName} IS NULL", $orderUsing, $belongTo);
    }

    /**
     * Adds one or more columns to the
     * GROUP BY clause of the SQL query.
     *
     * @param string ...$groups
     * @return $this
     */
    public function groupBy(string ...$groups): self
    {
        $this->groupBy = $groups;

        return $this;
    }

    /**
     * Build SQL query syntax for bind in next step.
     */
    protected function builder(): string
    {
        $column = implode(', ', $this->column);

        $build = [];

        $build['join']       = $this->joinBuilder();
        $build['where']      = $this->getWhere();
        $build['group_by']   = $this->getGroupBy();
        $build['sort_order'] = $this->getOrderBy();
        $build['limit']      = $this->getLimit();

        $condition = implode(' ', array_filter($build, fn ($item) => $item !== ''));

        return $this->query = "SELECT {$column} FROM {$this->subQuery} {$condition}";
    }

    /**
     * Get formated combine limit and offset.
     */
    private function getLimit(): string
    {
        $limit = $this->limitEnd > 0 ? "LIMIT $this->limitEnd" : '';

        if ($this->limitStart === 0) {
            return $limit;
        }

        if ($this->limitEnd === 0 && $this->offset > 0) {
            return "LIMIT $this->limitStart OFFSET $this->offset";
        }

        return "LIMIT $this->limitStart, $this->limitEnd";
    }

    private function getGroupBy(): string
    {
        if ([] === $this->groupBy) {
            return '';
        }

        $groupBy = implode(', ', $this->groupBy);

        return "GROUP BY {$groupBy}";
    }

    private function getOrderBy(): string
    {
        if ([] === $this->sortOrder) {
            return '';
        }

        $orders = [];
        foreach ($this->sortOrder as $column => $order) {
            $orders[] = "{$column} {$order}";
        }

        $orders = implode(', ', $orders);

        return "ORDER BY {$orders}";
    }

    /**
     * @param array<string, string> $sortOrdder
     */
    public function sortOrderRef(int $limitStart, int $limitEnd, int $offset, array $sortOrdder): void
    {
        $this->limitStart = $limitStart;
        $this->limitEnd   = $limitEnd;
        $this->offset     = $offset;
        $this->sortOrder  = $sortOrdder;
    }
}
