<?php

declare(strict_types=1);

namespace System\Support\Facades;

/**
 * @method static \System\Database\Query\Table table(string $from)
 */
final class Query extends Facade
{
    protected static function getAccessor()
    {
        return 'Query';
    }
}
