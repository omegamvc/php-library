<?php

declare(strict_types=1);

namespace System\Support\Facades;

/**
 * @method static \System\Database\Schema\Create         create()
 * @method static \System\Database\Schema\Drop           drop()
 * @method static \System\Database\Schema\Table\Truncate refresh(string $table_name)
 * @method static \System\Database\Schema\Table\Create   table(string $table_name, callable $blueprint)
 * @method static \System\Database\Schema\Table\Alter    alter(string $table_name, callable $blueprint)
 * @method static \System\Database\Schema\Table\Raw      raw(string $raw)
 */
final class Schema extends Facade
{
    protected static function getAccessor()
    {
        return 'Schema';
    }
}
