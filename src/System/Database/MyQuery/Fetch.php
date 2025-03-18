<?php

declare(strict_types=1);

namespace System\Database\MyQuery;

use System\Collection\Collection;

abstract class Fetch extends Query
{
    /**
     * @return Collection<string|int, mixed>
     */
    public function get(): ?Collection
    {
        if (false === ($items = $this->all())) {
            $items = [];
        }

        return new Collection($items);
    }

    /**
     * @return string[]|mixed
     */
    public function single()
    {
        $this->builder();

        $this->pdo->query($this->query);
        foreach ($this->binds as $bind) {
            if (!$bind->hasBind()) {
                $this->pdo->bind($bind->getBind(), $bind->getValue());
            }
        }
        $result = $this->pdo->single();

        return $result === false ? [] : $this->pdo->single();
    }

    /** @return array<string|int, mixed>|false */
    public function all()
    {
        $this->builder();

        $this->pdo->query($this->query);
        foreach ($this->binds as $bind) {
            if (!$bind->hasBind()) {
                $this->pdo->bind($bind->getBind(), $bind->getValue());
            }
        }

        return $this->pdo->resultset();
    }
}
