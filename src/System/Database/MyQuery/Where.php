<?php

declare(strict_types=1);

namespace System\Database\MyQuery;

use System\Database\MyQuery\Traits\ConditionTrait;
use System\Database\MyQuery\Traits\SubQueryTrait;

class Where
{
    use ConditionTrait;
    use SubQueryTrait;

    /** @var string Table Name */
    private string $table;

    /** This property use for helper phpstan (auto skip) */
    private ?InnerQuery $subQuery = null;

    /**
     * Binder array(['key', 'val']).
     *
     * @var Bind[] Binder for PDO bind */
    private array $binds = [];

    /**
     * Final where statement.
     *
     * @var string[]
     */
    private array $where = [];

    /**
     * Single filter and single strict mode.
     *
     * @var array<string, string>
     */
    private array $filters = [];

    /**
     * Strict mode.
     *
     * @var bool True if use AND instance of OR
     */
    private bool $strictMode = true;

    public function __construct(string $table_name)
    {
        $this->table = $table_name;
    }

    /**
     * Get raw property.
     *  - binds
     *  - where
     *  - filter
     *  - isStrict.
     *
     * @return array<string, Bind[]|string[]|array<string, string>|bool>
     */
    public function get(): array
    {
        return [
            'binds'     => $this->binds,
            'where'     => $this->where,
            'filters'   => $this->filters,
            'isStrict'  => $this->strictMode,
        ];
    }

    /**
     * Reset all condition.
     */
    public function flush(): void
    {
        $this->binds       = [];
        $this->where       = [];
        $this->filters     = [];
        $this->strictMode = true;
    }

    public function isEmpty(): bool
    {
        return [] === $this->binds && [] === $this->where && [] === $this->filters && true === $this->strictMode;
    }
}
