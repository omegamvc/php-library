<?php

declare(strict_types=1);

namespace Omega\Database\MyModel;

use Omega\Collection\Collection;
use Omega\Database\MyQuery\Delete;
use Omega\Database\MyQuery\Update;

/**
 * @extends Collection<array-key, Model>
 */
class ModelCollection extends Collection
{
    /** @var Model */
    private $model;

    /**
     * @param iterable<array-key, Model> $models
     * @param Model                      $of
     */
    public function __construct($models, $of)
    {
        parent::__construct($models);
        $this->model = $of;
    }

    /**
     * Get value of primary key from first collumn/record.
     *
     * @return mixed[]
     *
     * @throws \Exception No records founds
     */
    public function getPrimaryKey()
    {
        $primaryKeys = [];
        foreach ($this->collection as $model) {
            $primaryKeys[] = $model->getPrimaryKey();
        }

        return $primaryKeys;
    }

    /**
     *  Check every Model has clean column.
     */
    public function isClean(?string $column = null): bool
    {
        return $this->every(fn ($model) => $model->isClean($column));
    }

    /**
     * Check every Model has dirty column.
     */
    public function isDirty(?string $column = null): bool
    {
        return !$this->isClean($column);
    }

    /**
     * Update using query using model primary key.
     *
     * @param array<array-key, mixed> $values
     */
    public function update(array $values): bool
    {
        $table_name  = (fn () => $this->{'table_name'})->call($this->model);
        $pdo         = (fn () => $this->{'pdo'})->call($this->model);
        $primary_key = (fn () => $this->{'primary_key'})->call($this->model);
        $update      = new Update($table_name, $pdo);

        $update->values($values)->in($primary_key, $this->getPrimaryKey());

        return $update->execute();
    }

    /**
     * Delete using query using model primary key.
     */
    public function delete(): bool
    {
        $table_name  = (fn () => $this->{'table_name'})->call($this->model);
        $pdo         = (fn () => $this->{'pdo'})->call($this->model);
        $primary_key = (fn () => $this->{'primary_key'})->call($this->model);
        $delete      = new Delete($table_name, $pdo);

        $delete->in($primary_key, $this->getPrimaryKey());

        return $delete->execute();
    }

    /**
     * Convert array of model to pure array;.
     *
     * @return array<array-key, mixed>
     */
    public function toArrayArray(): array
    {
        /** @var array<array-key, mixed> */
        $arr = [];
        foreach ($this->collection as $model) {
            $arr = array_merge($arr, $model->toArray());
        }

        return $arr;
    }
}
