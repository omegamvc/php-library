<?php

declare(strict_types=1);

namespace Omega\Database\Schema;

abstract class AbstractQuery
{
    /** @var SchemaConnection PDO property */
    protected SchemaConnection $pdo;

    public function __toString()
    {
        return $this->builder();
    }

    protected function builder(): string
    {
        return '';
    }

    public function execute(): bool
    {
        return $this->pdo->query($this->builder())->execute();
    }

    /**
     * Helper: join condition into string.
     *
     * @param string[] $array
     */
    protected function join(array $array, string $separator = ' '): string
    {
        return implode(
            $separator,
            array_filter($array, fn ($item) => $item !== '')
        );
    }
}
