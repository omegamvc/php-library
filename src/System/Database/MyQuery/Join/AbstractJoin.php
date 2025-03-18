<?php

declare(strict_types=1);

namespace System\Database\MyQuery\Join;

use System\Database\MyQuery\InnerQuery;

abstract class AbstractJoin
{
    /**
     * @var string
     */
    protected string $mainTable = '';

    /**
     * @var string
     */
    protected string $tableName = '';

    /**
     * @var string
     */
    protected string $columnName = '';

    /**
     * @var string[]
     */
    protected array $compareColumn = [];

    /**
     * @var string
     */
    protected string $stringJoin = '';

    protected ?InnerQuery $subQuery = null;

    final public function __construct()
    {
    }

    /**
     * @return self
     */
    public function __invoke(string $main_table)
    {
        $this->mainTable = $main_table;

        return $this;
    }

    public function __toString()
    {
        return $this->stringJoin();
    }

    /**
     * Instance of class.
     *
     * @param string|InnerQuery $ref_table Name of the table want to join or sub query
     * @param string            $id        Main id of the table
     * @param string|null       $ref_id    Id of the table want to join, null means same as main id
     */
    public static function ref($ref_table, string $id, ?string $ref_id = null): AbstractJoin
    {
        $instance = new static();

        if ($ref_table instanceof InnerQuery) {
            return $instance
                ->clausa($ref_table)
                ->compare($id, $ref_id);
        }

        return $instance
            ->tableRef($ref_table)
            ->compare($id, $ref_id);
    }

    // setter

    /**
     * set main table / master table.
     *
     * @param string $main_table Name of the master table
     *
     * @return self
     */
    public function table(string $main_table)
    {
        $this->mainTable = $main_table;

        return $this;
    }

    public function clausa(InnerQuery $select): self
    {
        $this->subQuery  = $select;
        $this->tableName = $select->getAlias();

        return $this;
    }

    /**
     * Set table reference.
     *
     * @param string $ref_table Name of the ref table
     *
     * @return self
     */
    public function tableRef(string $ref_table)
    {
        $this->tableName = $ref_table;

        return $this;
    }

    /**
     * set main table and ref table.
     *
     * @param string $main_table Name of the master table
     * @param string $ref_table  Name of the ref table
     *
     * @return self
     */
    public function tableRelation(string $main_table, string $ref_table)
    {
        $this->mainTable = $main_table;
        $this->tableName = $ref_table;

        return $this;
    }

    /**
     * Compare identical two table.
     *
     * @param string $main_column    Identical of the main table column
     * @param string $compire_column Identical of the ref table column
     *
     * @return self
     */
    public function compare(string $main_column, ?string $compire_column = null)
    {
        $compire_column ??= $main_column;

        $this->compareColumn[] = [
            $main_column, $compire_column,
        ];

        return $this;
    }

    // getter
    /**
     * Get string of raw join builder.
     *
     * @return string String of raw join builder
     */
    public function stringJoin(): string
    {
        return $this->joinBuilder();
    }

    // main

    /**
     * Setup bulider.
     *
     * @return string Raw of builder join
     */
    protected function joinBuilder(): string
    {
        return $this->stringJoin;
    }

    /**
     * Get string of compare join
     * (ex: a.b = c.d).
     */
    protected function splitJoin(): string
    {
        $on = [];
        foreach ($this->compareColumn as $column) {
            $masterColumn  = $column[0];
            $compireColumn = $column[1];

            $on[] = "$this->mainTable.$masterColumn = $this->tableName.$compireColumn";
        }

        return implode(' AND ', $on);
    }

    protected function getAlias(): string
    {
        return null === $this->subQuery ? $this->tableName : (string) $this->subQuery;
    }
}
