<?php

declare(strict_types=1);

namespace System\Support\Facades;

use System\Database\Connection;

/**
 * @method static Connection instance()
 */
final class Database extends Facade
{
    protected static function getAccessor(): string
    {
        return Connection::class;
    }
}
