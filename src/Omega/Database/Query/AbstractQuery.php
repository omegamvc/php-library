<?php

declare(strict_types=1);

namespace Omega\Database\Query;

use Omega\Database\Connection;

abstract class AbstractQuery
{
    /** @var Connection PDO property */
    protected $pdo;

    /** @var string Main query */
    protected $query;

    /** @var string Table Name */
    protected $table = '';

    protected ?InnerQuery $subQuery = null;

    /** @var string[] Columns name */
    protected array $column = ['*'];

    /**
     * Binder array(['key', 'val']).
     *
     * @var Bind[] Binder for PDO bind
     */
    protected array $binds = [];

    /** @var int Limit start from */
    protected int $limitStart = 0;

    /** @var int Limit end to */
    protected int $limitEnd = 0;

    /** @var int offest */
    protected int $offset = 0;

    /** @var array<string, string> Sort result ASC|DESC */
    protected array $sortOrder  = [];

    public const int ORDER_ASC  = 0;
    public const int ORDER_DESC = 1;

    /**
     * Final where statmnet.
     *
     * @var string[]
     */
    protected array $where = [];

    /**
     * Grouping.
     *
     * @var string[]
     */
    protected array $groupBy = [];

    /**
     * Multy filter with strict mode.
     *
     * @var array<int, array<string, array<string, array<string, string>>>>
     */
    protected array $groupFilters = [];

    /**
     * Single filter and single strict mode.
     *
     * @var array<string, string>
     */
    protected array $filters = [];

    /**
     * Strict mode.
     *
     * @var bool True if use AND instance of OR
     */
    protected bool $strictMode = true;

    /**
     * @var string[]
     */
    protected array $join = [];

    /**
     * reset all property.
     *
     * @return self
     */
    public function reset(): self
    {
        $this->table        = '';
        $this->subQuery     = null;
        $this->column       = ['*'];
        $this->binds        = [];
        $this->limitStart   = 0;
        $this->limitEnd     = 0;
        $this->where        = [];
        $this->groupFilters = [];
        $this->filters      = [];
        $this->strictMode   = true;

        return $this;
    }

    // Query builder

    /**
     * Get where statment baseon binding set before.
     *
     * @return string Where statment from binder
     */
    protected function getWhere(): string
    {
        $merging      = $this->mergeFilters();
        $where        = $this->splitGrupsFilters($merging);
        $glue         = $this->strictMode ? ' AND ' : ' OR ';
        $whereCostume = implode($glue, $this->where);

        if ($where !== '' && $whereCostume !== '') {
            // menggabungkan basic where dengan costume where
            $whereString = $this->strictMode ? "AND $whereCostume" : "OR $whereCostume";

            return "WHERE $where $whereString";
        } elseif ($where === '' && $whereCostume !== '') {
            // hanya menggunkan costume where
            $whereString = $this->strictMode ? "$whereCostume" : "$whereCostume";

            return "WHERE $whereString";
        } elseif ($where !== '') {
            // hanya mengunakan basic where
            return "WHERE $where";
        }

        // return condition where statment
        return $where;
    }

    /**
     * @return array<int, array<string, array<string, array<string, string>>>>
     */
    protected function mergeFilters(): array
    {
        $new_group_filters = $this->groupFilters;
        if (!empty($this->filters)) {
            // merge group filter and main filter (condition)
            $new_group_filters[] = [
                'filters' => $this->filters,
                'strict'  => $this->strictMode,
            ];
        }

        // hasil penggabungan
        return $new_group_filters;
    }

    /**
     * @param array<int, array<string, array<string, array<string, string>>>> $group_filters Groups of filters
     */
    protected function splitGrupsFilters(array $group_filters): string
    {
        // mengabungkan query-queery kecil menjadi satu
        $whereStatment = [];
        foreach ($group_filters as $filters) {
            $single          = $this->splitFilters($filters);
            $whereStatment[] = "( $single )";
        }

        return implode(' AND ', $whereStatment);
    }

    /**
     * @param array<string, array<string, array<string, string>>> $filters Filters
     */
    protected function splitFilters(array $filters): string
    {
        $query      = [];
        $table_name = null === $this->subQuery ? $this->table : $this->subQuery->getAlias();
        foreach ($filters['filters'] as $fieldName => $fieldValue) {
            $value        = $fieldValue['value'];
            $comparation  = $fieldValue['comparation'];
            $column       = str_contains($fieldName, '.') ? $fieldName : "{$table_name}.{$fieldName}";
            $bind         = $fieldValue['bind'];

            if ($value !== '') {
                $query[] = "({$column} {$comparation} :{$bind})";
            }
        }

        $clear_query = array_filter($query);

        return $filters['strict'] ? implode(' AND ', $clear_query) : implode(' OR ', $clear_query);
    }

    /**
     * Bind query with binding.
     */
    public function queryBind(): string
    {
        [$binds, $values] = $this->bindsDestructur();

        $quote_values = array_map(function ($value) {
            if (is_string($value)) {
                return "'" . $value . "'";
            }

            if (is_bool($value)) {
                if ($value === true) {
                    return 'true';
                }

                return 'false';
            }

            /* @phpstan-ignore-next-line */
            return $value;
        }, $values);

        return str_replace($binds, $quote_values, $this->builder());
    }

    protected function builder(): string
    {
        return '';
    }

    /**
     * @return array<int, string[]|bool[]>>
     */
    public function bindsDestructur(): array
    {
        $bind_name = [];
        $value     = [];
        $columns   = [];

        foreach ($this->binds as $bind) {
            // if (!$bind->hasColumName()) {
            //     continue;
            // }
            $bind_name[] = $bind->getBind();
            $value[]     = $bind->getValue();
            if (!in_array($bind->getColumnName(), $columns)) {
                $columns[] = $bind->getColumnName();
            }
        }

        return [$bind_name, $value, $columns];
    }

    /** @return Bind[]  */
    public function getBinds()
    {
        return $this->binds;
    }

    /**
     * Add where condition from where referans.
     */
    public function whereRef(?Where $ref): static
    {
        if ($ref->isEmpty()) {
            return $this;
        }
        $conditon = $ref->get();
        foreach ($conditon['binds'] as $bind) {
            $this->binds[] = $bind;
        }
        foreach ($conditon['where'] as $where) {
            $this->where[] = $where;
        }
        foreach ($conditon['filters'] as $name => $filter) {
            $this->filters[$name] = $filter;
        }
        $this->strictMode = $conditon['isStrict'];

        return $this;
    }
}
