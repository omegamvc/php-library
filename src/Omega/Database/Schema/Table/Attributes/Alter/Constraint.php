<?php

declare(strict_types=1);

namespace Omega\Database\Schema\Table\Attributes\Alter;

use Omega\Database\Schema\Table\Attributes\Constraint as AttributesConstraint;

class Constraint extends AttributesConstraint
{
    public function after(string $column): self
    {
        $this->order = "AFTER {$column}";

        return $this;
    }

    public function first(): self
    {
        $this->order = 'FIRST';

        return $this;
    }

    public function raw(string $raw): self
    {
        $this->raw = $raw;

        return $this;
    }
}
