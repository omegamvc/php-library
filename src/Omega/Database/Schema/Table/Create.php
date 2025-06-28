<?php

declare(strict_types=1);

namespace Omega\Database\Schema\Table;

use Omega\Database\Schema\SchemaConnection;
use Omega\Database\Schema\AbstractQuery;
use Omega\Database\Schema\Table\Attributes\DataType;

class Create extends AbstractQuery
{
    public const string INNODB    = 'INNODB';
    public const string MYISAM    = 'MYISAM';
    public const string MEMORY    = 'MEMORY';
    public const string MERGE     = 'MERGE';
    public const string EXAMPLE   = 'EXAMPLE';
    public const string ARCHIVE   = 'ARCHIVE';
    public const string CSV       = 'CSV';
    public const string BLACKHOLE = 'BLACKHOLE';
    public const string FEDERATED = 'FEDERATED';

    /** @var Column[]|DataType[] */
    private array $columns;

    /** @var string[] */
    private array $primaryKeys;

    /** @var string[] */
    private array $uniques;

    /** @var string */
    private string $storeEngine;

    private string $characterSet;

    /** @var string */
    private string $tableName;

    public function __construct(string $databaseName, string $tableName, SchemaConnection $pdo)
    {
        $this->tableName    = $databaseName . '.' . $tableName;
        $this->pdo          = $pdo;
        $this->columns      = [];
        $this->primaryKeys  = [];
        $this->uniques      = [];
        $this->storeEngine  = '';
        $this->characterSet = '';
    }

    public function __invoke(string $columnName): DataType
    {
        return $this->columns[] = (new Column())->column($columnName);
    }

    public function addColumn(): Column
    {
        return $this->columns[] = new Column();
    }

    /** @param Column[] $columns */
    public function columns(array $columns): self
    {
        $this->columns = [];
        foreach ($columns as $column) {
            $this->columns[] = $column;
        }

        return $this;
    }

    public function primaryKey(string $columnName): self
    {
        $this->primaryKeys[] = $columnName;

        return $this;
    }

    public function unique(string $unique): self
    {
        $this->uniques[] = $unique;

        return $this;
    }

    public function engine(string $engine): self
    {
        $this->storeEngine = $engine;

        return $this;
    }

    public function character(string $characterSet): self
    {
        $this->characterSet = $characterSet;

        return $this;
    }

    protected function builder(): string
    {
        /** @var string[] $columns */
        $columns = array_merge($this->getColumns(), $this->getPrimaryKey(), $this->getUnique());
        $columns = $this->join($columns, ', ');
        $query   = $this->join([$this->tableName, '(', $columns, ')' . $this->getStoreEngine() . $this->getCharacterSet()]);

        return 'CREATE TABLE ' . $query;
    }

    /** @return string[] */
    private function getColumns(): array
    {
        $res = [];

        foreach ($this->columns as $attribute) {
            $res[] = $attribute->__toString();
        }

        return $res;
    }

    /** @return string[] */
    private function getPrimaryKey(): array
    {
        if (count($this->primaryKeys) === 0) {
            return [''];
        }

        $primaryKeys = array_map(fn ($primaryKey) => $primaryKey, $this->primaryKeys);
        $primaryKeys = implode(', ', $primaryKeys);

        return ["PRIMARY KEY ({$primaryKeys})"];
    }

    /** @return string[] */
    private function getUnique(): array
    {
        if (count($this->uniques) === 0) {
            return [''];
        }

        $uniques = implode(', ', $this->uniques);

        return ["UNIQUE ({$uniques})"];
    }

    private function getStoreEngine(): string
    {
        return $this->storeEngine === '' ? '' : ' ENGINE=' . $this->storeEngine;
    }

    private function getCharacterSet(): string
    {
        return $this->characterSet === '' ? '' : " CHARACTER SET {$this->characterSet}";
    }
}
