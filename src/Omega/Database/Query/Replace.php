<?php

declare(strict_types=1);

namespace Omega\Database\Query;

class Replace extends Insert
{
    protected function builder(): string
    {
        [$binds, ,$columns] = $this->bindsDestructur();

        $stringsBinds = [];
        /** @var array<int, array<int, string>> */
        $chunk         = array_chunk($binds, count($columns), true);
        foreach ($chunk as $group) {
            $stringsBinds[] = '(' . implode(', ', $group) . ')';
        }

        $stringBinds  = implode(', ', $stringsBinds);
        $stringColumn = implode(', ', $columns);

        return $this->query = "REPLACE INTO {$this->table} ({$stringColumn}) VALUES {$stringBinds}";
    }
}
