<?php

declare(strict_types=1);

namespace System\Console\Style;

interface RuleInterface
{
    /**
     * @return array<int, int>
     */
    public function get(): array;

    public function raw(): string;
}
