<?php

declare(strict_types=1);

namespace System\Database\MyQuery;

abstract class Execute extends Query
{
    public function execute(): bool
    {
        $this->builder();

        if ($this->query != null) {
            $this->pdo->query($this->query);
            foreach ($this->binds as $bind) {
                if (!$bind->hasBind()) {
                    $this->pdo->bind($bind->getBind(), $bind->getValue());
                }
            }

            $this->pdo->execute();

            return $this->pdo->rowCount() > 0 ? true : false;
        }

        return false;
    }
}
