<?php

declare(strict_types=1);

namespace Omega\Database\Query;

abstract class AbstractExecute extends AbstractQuery
{
    public function execute(): bool
    {
        $this->builder();

        if ($this->_query != null) {
            $this->PDO->query($this->_query);
            foreach ($this->_binds as $bind) {
                if (!$bind->hasBind()) {
                    $this->PDO->bind($bind->getBind(), $bind->getValue());
                }
            }

            $this->PDO->execute();

            return $this->PDO->rowCount() > 0 ? true : false;
        }

        return false;
    }
}
